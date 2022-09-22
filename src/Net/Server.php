<?php

namespace cs\Net;

use cs\Core\Game;
use cs\Core\GameException;
use cs\Core\Player;
use cs\Core\Util;
use cs\Enum\Color;
use cs\Enum\GameOverReason;
use cs\Event\GameOverEvent;
use cs\Net\Protocol\TextProtocol;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

class Server
{

    /** @var Client[] [playerId => Client] */
    private array $clients = [];
    /** @var array<int,callable> [playerId => fn(PlayerControl $control):void {} */
    private array $tickCommands = [];
    private LoggerInterface $logger;

    private int $playerId = 0;
    private int $countAttackers = 0;
    private int $countDefenders = 0;
    private int $blockListMax = 500;
    private int $serverLag = 0;
    private int $tickMicrosecond;

    /** @var array<string,int> [ipAddress-port => playerId] */
    private array $loggedPlayers = [];
    /** @var array<string,int> [ipAddress => 1] */
    private array $blockList = [];
    private ?string $saveRequestsPath = null;

    public function __construct(
        private Game          $game,
        private ServerSetting $setting,
        private NetConnector  $net,
        private Protocol      $protocol = new TextProtocol(),
    )
    {
        $this->logger = new NullLogger();

        $tickMs = $this->setting->tickMs;
        if ($tickMs < 0) {
            throw new GameException("Negative tickMs given");
        } elseif ($tickMs === 0) { // tests only
            $this->tickMicrosecond = 0;
        } else {
            Util::$TICK_RATE = $tickMs;
            $this->tickMicrosecond = $tickMs * 1000;
        }
    }

    public function start(): void
    {
        if (!$this->startWarmup()) {
            $playerCount = count($this->clients);
            $this->log("Not all players connected during warmup! Players: {$playerCount}/{$this->setting->playersMax}.");
            $this->game->quit(GameOverReason::REASON_NOT_ALL_PLAYERS_CONNECTED);
            $this->sendGameStateToClients();
            return;
        }

        $this->log("All players connected, starting game.");
        if ($this->saveRequestsPath) {
            $this->saveRequestMetaData();
        }
        $tickCount = $this->startGame();
        $this->log("Game ended! Ticks: {$tickCount}, Lag: {$this->serverLag}.");
    }

    protected function startWarmup(): bool
    {
        $gameReady = false;
        while ($this->setting->warmupWaitSecRemains >= 0) {
            if (!$gameReady) {
                $this->clientsLogin();
                if (count($this->clients) === $this->setting->playersMax) {
                    if ($this->setting->warmupInstantStart) {
                        return true;
                    }
                    $gameReady = true;
                }
            }

            if (--$this->setting->warmupWaitSecRemains > 0) {
                sleep(1);
            }
        }

        return $gameReady;
    }

    protected function startGame(): int
    {
        $this->sendGameStateToClients();

        $tickId = 0;
        $usLast = (int)(microtime(true) * 1000000);

        while (true) {
            $this->receiveClientsCommands();
            $gameOverEvent = $this->gameTick($tickId);
            $this->sendGameStateToClients();
            if ($gameOverEvent) {
                break;
            }

            $usCurrent = (int)(microtime(true) * 1000000);
            $delta = $usCurrent - $usLast;
            $sleepTimeUs = 0;
            if ($delta + 100 < $this->tickMicrosecond) {
                $sleepTimeUs = $this->tickMicrosecond - $delta - 100;
                usleep($sleepTimeUs);
            } else {
                $this->serverLag++;
            }
            $usLast = $usCurrent + $sleepTimeUs;
            $tickId++;
        }

        return $tickId;
    }

    private function sendGameStateToClients(): void
    {
        $msg = $this->protocol->serializeGameState($this->game);
        foreach ($this->clients as $client) {
            $this->sendToPlayer($client, $msg);
        }
    }

    private function clientsLogin(): void
    {
        if ($this->pollClient($clientIp, $clientPort, $clientRequest)) {
            $this->loginPlayer($clientIp, $clientPort, $clientRequest);
        }
    }

    private function receiveClientsCommands(): void
    {
        $playersRequest = [];
        for ($i = 1; $i <= $this->setting->playersMax * 2; $i++) {
            if (!$this->pollClient($address, $port, $msg)) {
                continue;
            }

            if (isset($this->loggedPlayers["{$address}-{$port}"])) {
                $playerId = $this->loggedPlayers["{$address}-{$port}"];
                if (!isset($playersRequest[$playerId])) {
                    $playersRequest[$playerId] = 1;
                    if ($this->game->getPlayer($playerId)->isAlive()) {
                        $this->parseClientRequest($playerId, $msg);
                    }
                }
                if (count($playersRequest) === $this->setting->playersMax) {
                    break;
                }
            } else {
                $this->playerBlock($address);
            }
        }
    }

    private function parseClientRequest(int $playerId, string $msg): void
    {
        $commands = $this->protocol->parsePlayerControlCommands($msg);
        if ([] === $commands) {
            $this->log("Player '{$playerId}' send invalid request", LogLevel::WARNING);
            return;
        }
        if ($this->saveRequestsPath) {
            $data = "{$this->game->getTickId()}~{$playerId}~{$msg}\n";
            file_put_contents($this->saveRequestsPath, $data, FILE_APPEND);
        }

        $this->tickCommands[$playerId] = function (PlayerControl $control) use ($commands): void {
            foreach ($commands as $command) {
                $method = array_shift($command);
                $control->{$method}(...$command);
            }
        };
    }

    private function pollClient(mixed &$clientIp, mixed &$clientPort, mixed &$clientRequest, int $readTimeoutMicroSeconds = 100): bool
    {
        $peer = '';
        $clientRequest = $this->net->receive($peer, $readTimeoutMicroSeconds);
        if ($clientRequest === null) {
            return false;
        }

        [$clientIp, $clientPort] = explode(':', $peer);
        if (isset($this->blockList[$clientIp])) {
            return false;
        }
        return true;
    }

    private function sendToPlayer(Client $client, string $msg): void
    {
        $this->net->sendTo($client, $msg);
    }

    private function playerBlock(string $playerAddress): void
    {
        if (count($this->blockList) > $this->blockListMax) {
            array_shift($this->blockList);
        }
        $this->blockList[$playerAddress] = 1;
    }

    private function error(string $msg = ''): never
    {
        throw new GameException($msg);
    }

    private function loginPlayer(string $playerAddress, int $playerPort, string $msg): void
    {
        $playersCount = count($this->clients);
        if ($playersCount >= $this->setting->playersMax) {
            $this->error("Too many players");
        }

        if ($msg === "login {$this->setting->attackerCode}") { // TODO use protocol interface for this, also each player should have unique code from MM
            $attackerSide = true;
        } elseif ($msg === "login {$this->setting->defenderCode}") {
            $attackerSide = false;
        } else {
            $this->playerBlock($playerAddress);
            return;
        }

        $playerId = ++$this->playerId;
        $playerControl = $this->playerCreate($playerId, $attackerSide);
        $client = new Client($playerControl, $playerAddress, $playerPort);
        $this->clients[$playerId] = $client;
        $this->loggedPlayers["{$playerAddress}-{$playerPort}"] = $playerId;
        $this->sendToPlayer($client, $this->protocol->serializeGameSetting($this->game->getPlayer($playerId), $this->setting, $this->game));
    }

    private function playerCreate(int $playerId, bool $attackerSide): PlayerControl
    {
        if ($attackerSide) {
            $color = Color::from($this->countAttackers % 5 + 1);
            $this->countAttackers++;
        } else {
            $color = Color::from($this->countDefenders % 5 + 1);
            $this->countDefenders++;
        }

        $player = new Player($playerId, $color, $attackerSide);
        $this->game->addPlayer($player);
        return new PlayerControl($player, $this->game->getState());
    }

    private function gameTick(int $tickId): ?GameOverEvent
    {
        foreach ($this->tickCommands as $playerId => $callback) {
            call_user_func($callback, $this->clients[$playerId]->getPlayerControl());
        }
        $this->tickCommands = [];
        return $this->game->tick($tickId);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    private function log(string $msg, string $level = LogLevel::INFO): void
    {
        $this->logger->log($level, $msg);
    }

    private function saveRequestMetaData(): void
    {
        $players = [];
        foreach ($this->game->getPlayers() as $player) {
            $players[$player->getId()] = $player->toArray();
        }
        file_put_contents($this->saveRequestsPath . '.json', json_encode([
            'protocol'   => get_class($this->protocol),
            'properties' => $this->game->getProperties()->toArray(),
            'players'    => $players,
            'walls'      => $this->game->getWorld()->getWalls(),
            'floors'     => $this->game->getWorld()->getFloors(),
        ], JSON_THROW_ON_ERROR));
    }

    public function storeRequests(string $path = '/tmp/cs.server.req'): void
    {
        $this->saveRequestsPath = $path;
        file_put_contents($this->saveRequestsPath, '');
    }

}