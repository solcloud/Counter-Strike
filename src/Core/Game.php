<?php

namespace cs\Core;

use cs\Enum\GameOverReason;
use cs\Enum\PauseReason;
use cs\Event\Event;
use cs\Event\GameOverEvent;
use cs\Event\KillEvent;
use cs\Event\PauseEndEvent;
use cs\Event\PauseStartEvent;
use cs\Event\RoundEndCoolDownEvent;
use cs\Event\RoundEndEvent;
use cs\Event\RoundStartEvent;
use cs\Map\Map;

class Game
{

    private World $world;
    private Score $score;
    private GameState $state;
    private GameProperty $properties;
    private ?GameOverEvent $gameOver = null;
    private PauseStartEvent $startRoundFreezeTime;

    /** @var Player[] */
    private array $players = [];
    /** @var Event[] */
    private array $events = [];
    /** @var Event[] */
    private array $tickEvents = [];

    private int $tick = 0;
    private int $eventId = 0;
    private int $roundNumber = 1;
    private int $roundStartTickId = 0;
    private int $roundTickCount = 0;
    private int $playersCountAttackers = 0;
    private int $playersCountDefenders = 0;
    private bool $paused = true;
    private bool $roundEndCoolDown = false;

    public function __construct(GameProperty $properties = new GameProperty())
    {
        $this->state = new GameState($this);
        $this->world = new World($this);
        $this->score = new Score();
        $this->properties = $properties;
    }

    private function initialize(): void
    {
        $this->roundTickCount = Util::millisecondsToFrames($this->properties->round_time_ms);
        $this->startRoundFreezeTime = new PauseStartEvent(PauseReason::FREEZE_TIME, function (): void {
            $this->paused = false;
            $this->addEvent(new PauseEndEvent());
            $this->addEvent(new RoundStartEvent($this->playersCountAttackers, $this->playersCountDefenders, function (): void {
                $this->roundEndCoolDown = false;
                $this->roundStartTickId = $this->getTickId();
            }));
        }, $this->properties->freeze_time_sec * 1000);
        $this->addEvent($this->startRoundFreezeTime);
    }

    public function tick(int $tickId): ?GameOverEvent
    {
        $this->tick = $tickId;

        if ($this->gameOver) {
            $this->tickEvents = [$this->gameOver];
            return $this->gameOver;
        }
        if ($tickId === 0) {
            $this->initialize();
        }

        $alivePlayers = [0, 0];
        foreach ($this->players as $player) {
            if (!$player->isAlive()) {
                continue;
            }

            $player->onTick($tickId);
            if ($player->isAlive()) {
                $alivePlayers[(int)$player->isPlayingOnAttackerSide()]++;
            }
        }
        $this->checkRoundEnd($alivePlayers[0], $alivePlayers[1]);
        $this->processEvents($tickId);
        return null;
    }

    private function checkRoundEnd(int $defendersAlive, int $attackersAlive): void
    {
        if ($this->roundEndCoolDown) {
            return;
        }
        // TODO bomb check, planted, timer

        if ($this->playersCountAttackers > 0 && $attackersAlive === 0) {
            $this->roundEndCoolDown = true;
            $this->addEvent(new RoundEndEvent($this, false));
            return;
        }
        if ($this->playersCountDefenders > 0 && $defendersAlive === 0) {
            $this->roundEndCoolDown = true;
            $this->addEvent(new RoundEndEvent($this, true));
            return;
        }

        if ($this->roundStartTickId + $this->roundTickCount === $this->tick) {
            $this->roundEndCoolDown = true;
            $this->addEvent(new RoundEndEvent($this, false));
            return;
        }
    }

    private function addEvent(Event $event): void
    {
        $eventId = $this->eventId++;
        $this->events[$eventId] = $event;
        $event->customId = $eventId;
        $event->onComplete[] = fn(Event $e) => $this->removeEvent($e->customId);

        $this->tickEvents[] = $event;
    }

    private function removeEvent(int $eventId): void
    {
        unset($this->events[$eventId]);
    }

    private function processEvents(int $tickId): void
    {
        if ($this->events === []) {
            $this->eventId = 0;
            return;
        }

        foreach ($this->events as $event) {
            $event->process($tickId);
        }
    }

    public function getPlayer(int $id): Player
    {
        return $this->players[$id];
    }

    public function addPlayer(Player $player): void
    {
        if (isset($this->players[$player->getId()])) {
            throw new GameException("Player with ID '{$player->getId()}' is already in game!");
        }

        $player->setWorld($this->world);
        $player->getInventory()->earnMoney($this->properties->start_money);
        $spawnPosition = $this->getWorld()->getPlayerSpawnPosition($player->isPlayingOnAttackerSide(), $this->properties->randomize_spawn_position);
        $player->setPosition($spawnPosition);

        $this->players[$player->getId()] = $player;
        $this->world->addPlayerCollider(new PlayerCollider($player));
        if ($player->isPlayingOnAttackerSide()) {
            $this->playersCountAttackers++;
        } else {
            $this->playersCountDefenders++;
        }
    }

    public function quit(GameOverReason $reason): void
    {
        $this->gameOver = new GameOverEvent($reason);
        $this->tickEvents = [$this->gameOver];
    }

    public function isPaused(): bool
    {
        return $this->paused;
    }

    public function getTickId(): int
    {
        return $this->tick;
    }

    public function loadMap(Map $map): void
    {
        $this->world->loadMap($map);
    }

    public function getWorld(): World
    {
        return $this->world;
    }

    /**
     * @return Event[]
     */
    public function consumeTickEvents(): array
    {
        $events = $this->tickEvents;
        $this->tickEvents = [];
        return $events;
    }

    public function getRoundNumber(): int
    {
        return $this->roundNumber;
    }

    public function playerAttackKilledEvent(Player $playerDead, Bullet $bullet, bool $headShot): void
    {
        $this->addEvent(new KillEvent($playerDead, $this->players[$bullet->getOriginPlayerId()], $bullet->getShootItem()->getId(), $headShot));
    }

    public function playerFallDamageKilledEvent(Player $playerDead): void
    {
        $this->addEvent(new KillEvent($playerDead, $playerDead, Floor::class, false));
    }

    public function endRound(bool $attackersWins): void
    {
        $this->roundNumber++;

        if ($attackersWins) {
            $this->score->attackersWinsRound();
        } else {
            $this->score->defendersWinsRound();
        }

        if ($this->roundNumber > $this->properties->max_rounds) {
            if ($this->score->isTie()) {
                $this->gameOver = new GameOverEvent(GameOverReason::TIE);
            } else {
                $this->gameOver = new GameOverEvent($this->score->attackersIsWinning() ? GameOverReason::ATTACKERS_WINS : GameOverReason::DEFENDERS_WINS);
            }
            return;
        }

        $this->world->roundReset();
        foreach ($this->players as $player) {
            $player->roundReset();
            $player->getInventory()->earnMoney(1000); // TODO
            $spawnPosition = $this->getWorld()->getPlayerSpawnPosition($player->isPlayingOnAttackerSide(), $this->properties->randomize_spawn_position);
            $player->setPosition($spawnPosition);
        }

        $this->paused = true;
        $startRoundFreezeTime = $this->startRoundFreezeTime;
        $startRoundFreezeTime->reset();

        $isHalftime = $this->properties->max_rounds > 2 && (((int)floor($this->properties->max_rounds / 2)) === $this->roundNumber);
        if ($isHalftime) {
            $callback = function () use ($startRoundFreezeTime): void {
                $this->addEvent($startRoundFreezeTime);
            };
            $event = new PauseStartEvent(PauseReason::HALF_TIME, $callback, $this->properties->half_time_freeze_sec * 1000);
        } else {
            $event = new RoundEndCoolDownEvent(function () use ($startRoundFreezeTime): void {
                $this->addEvent($startRoundFreezeTime);
            }, $this->properties->round_end_cool_down_sec * 1000);
        }

        $this->addEvent($event);
    }

    public function getState(): GameState
    {
        return $this->state;
    }

    public function getPlayersCount(): int
    {
        return count($this->players);
    }

    public function getScore(): Score
    {
        return $this->score;
    }

    public function getProperties(): GameProperty
    {
        return $this->properties;
    }

    /**
     * @return Player[]
     */
    public function getPlayers(): array
    {
        return $this->players;
    }

}
