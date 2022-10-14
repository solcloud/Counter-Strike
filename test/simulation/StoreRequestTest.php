<?php

namespace Test\Simulation;

use cs\Core\GameProperty;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Setting;
use cs\Core\Util;
use cs\Map\Map;
use cs\Net\PlayerControl;
use cs\Net\Protocol\TextProtocol;
use Test\BaseTest;
use Test\TestGame;

class StoreRequestTest extends BaseTest
{

    public function testAllStoreRequests(): void
    {
        $dataDirectory = __DIR__ . '/data/requests';

        foreach (glob($dataDirectory . '/*.bin') as $testData) { // @phpstan-ignore-line
            $data = trim(file_get_contents($testData)); // @phpstan-ignore-line
            $meta = json_decode(file_get_contents(substr($testData, 0, -4) . ".json"), true); // @phpstan-ignore-line

            $testerClass = require substr($testData, 0, -4) . ".php";
            $this->_testRequest(new $testerClass(), $meta, $data); // @phpstan-ignore-line
        }
    }

    /**
     * @param array{actionData: array<string,int>, tickMs: int,
     *     protocol: string, players: array<mixed>, properties: array<string,string|int|bool>, map: array<mixed>
     * } $meta
     */
    private function _testRequest(SimulationTester $tester, array $meta, string &$data): void
    {
        Util::$TICK_RATE = $meta['tickMs'];
        Setting::loadConstants($meta['actionData']);

        $this->assertSame(TextProtocol::class, $meta['protocol']);
        $protocol = new TextProtocol();

        $playerRequests = [];
        foreach (explode("\n", $data) as $line) {
            [$tickId, $playerId, $request] = explode('~', $line);
            $playerRequests[(int)$tickId][(int)$playerId] = $protocol->parsePlayerControlCommands($request);
        }
        $this->assertGreaterThan(0, $tickId);

        $game = $this->createGame((int)$tickId, GameProperty::fromArray($meta['properties']));
        $game->loadMap(Map::fromArray($meta['map']));
        foreach ($meta['players'] as $playerData) {
            $player = Player::fromArray($playerData); // @phpstan-ignore-line
            $game->addPlayer($player);
        }

        $playerControls = [];
        $game->onEvents(function (array $events) use ($tester): void {
            $tester->onEvents($events);
        });
        $game->onTick(function (GameState $state) use ($tester, &$playerRequests, &$playerControls): void {
            $tester->onTickStart($state, $state->getTickId());
            if (!isset($playerRequests[$state->getTickId()])) {
                return;
            }

            foreach ($playerRequests[$state->getTickId()] as $playerId => $commands) {
                if (!isset($playerControls[$playerId])) {
                    $playerControls[$playerId] = new PlayerControl($state->getPlayer($playerId), $state);
                }

                foreach ($commands as $command) {
                    $method = array_shift($command);
                    $playerControls[$playerId]->{$method}(...$command);
                }
            }
        });
        $game->onAfterTick(function (GameState $state) use ($tester): void {
            $tester->onTickEnd($state, $state->getTickId());
        });

        $tester->onGameStart($game);
        $game->start();
        $tester->onGameEnd($game);
    }

    protected function createGame(int $tickMax, GameProperty $properties): TestGame
    {
        $game = new TestGame($properties);
        $game->setTickMax($tickMax);
        return $game;
    }

}
