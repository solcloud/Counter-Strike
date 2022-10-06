<?php

namespace cs\Core;

use cs\Enum\GameOverReason;
use cs\Enum\PauseReason;
use cs\Enum\RoundEndReason;
use cs\Event\Event;
use cs\Event\GameOverEvent;
use cs\Event\KillEvent;
use cs\Event\PauseEndEvent;
use cs\Event\PauseStartEvent;
use cs\Event\RoundEndCoolDownEvent;
use cs\Event\RoundEndEvent;
use cs\Event\RoundStartEvent;
use cs\Event\SoundEvent;
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
    private bool $bombPlanted = false; // TODO

    public function __construct(GameProperty $properties = new GameProperty())
    {
        $this->state = new GameState($this);
        $this->world = new World($this);
        $this->score = new Score($properties->loss_bonuses);
        $this->properties = $properties;

        $this->initialize();
    }

    private function initialize(): void
    {
        $this->roundTickCount = Util::millisecondsToFrames($this->properties->round_time_ms);
        $this->startRoundFreezeTime = new PauseStartEvent($this, PauseReason::FREEZE_TIME, function (): void {
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
            $this->addEvent(new RoundEndEvent($this, false, RoundEndReason::ALL_ENEMIES_ELIMINATED));
            return;
        }
        if ($this->playersCountDefenders > 0 && $defendersAlive === 0) {
            $this->roundEndCoolDown = true;
            $this->addEvent(new RoundEndEvent($this, true, RoundEndReason::ALL_ENEMIES_ELIMINATED));
            return;
        }

        if ($this->roundStartTickId + $this->roundTickCount === $this->tick) {
            $this->roundEndCoolDown = true;
            $this->addEvent(new RoundEndEvent($this, false, RoundEndReason::TIME_RUNS_OUT));
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
        $this->score->addPlayer($player);
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

    public function addSoundEvent(SoundEvent $event): void
    {
        $this->addEvent($event);
    }

    public function playerAttackKilledEvent(Player $playerDead, Bullet $bullet, bool $headShot): void
    {
        $playerCulprit = $this->players[$bullet->getOriginPlayerId()];
        if ($playerDead->isPlayingOnAttackerSide() === $playerCulprit->isPlayingOnAttackerSide()) { // team kill
            $this->score->getPlayerStat($playerCulprit->getId())->removeKill();
        } else {
            $this->score->getPlayerStat($playerCulprit->getId())->addKill($headShot);
        }
        $this->score->getPlayerStat($playerDead->getId())->addDeath();

        $this->addEvent(new KillEvent($playerDead, $playerCulprit, $bullet->getShootItem()->getId(), $headShot));
    }

    public function playerFallDamageKilledEvent(Player $playerDead): void
    {
        $this->score->getPlayerStat($playerDead->getId())->removeKill();
        $this->score->getPlayerStat($playerDead->getId())->addDeath();

        $this->addEvent(new KillEvent($playerDead, $playerDead, Floor::class, false));
    }

    public function endRound(RoundEndEvent $roundEndEvent): void
    {
        $this->roundNumber++;
        $this->score->roundEnd($roundEndEvent);

        if ($this->roundNumber > $this->properties->max_rounds) {
            if ($this->score->isTie()) {
                $this->gameOver = new GameOverEvent(GameOverReason::TIE);
            } else {
                $this->gameOver = new GameOverEvent($this->score->attackersIsWinning() ? GameOverReason::ATTACKERS_WINS : GameOverReason::DEFENDERS_WINS);
            }
            return;
        }

        $startRoundFreezeTime = $this->startRoundFreezeTime;
        $startRoundFreezeTime->reset();

        $isHalftime = $this->properties->max_rounds > 1 && (((int)floor($this->properties->max_rounds / 2)) + 1 === $this->roundNumber);
        if ($isHalftime) {
            $this->halfTimeSwapTeams();
            $callback = function () use ($startRoundFreezeTime, $roundEndEvent): void {
                $this->paused = true;
                $this->roundReset(true, $roundEndEvent);
                $this->addEvent($startRoundFreezeTime);
            };
            $event = new PauseStartEvent($this, PauseReason::HALF_TIME, $callback, $this->properties->half_time_freeze_sec * 1000);
        } else {
            $event = new RoundEndCoolDownEvent(function () use ($startRoundFreezeTime, $roundEndEvent): void {
                $this->paused = true;
                $this->roundReset(false, $roundEndEvent);
                $this->addEvent($startRoundFreezeTime);
            }, $this->properties->round_end_cool_down_sec * 1000);
        }

        $this->addEvent($event);
    }

    private function halfTimeSwapTeams(): void
    {
        $this->score->swapTeams();
        foreach ($this->players as $player) {
            $player->getInventory()->earnMoney(-$player->getInventory()->getDollars());
            $player->getInventory()->earnMoney($this->properties->start_money);
            $player->swapTeam();
        }
    }

    private function roundReset(bool $firstRound, RoundEndEvent $roundEndEvent): void
    {
        $this->world->roundReset();
        foreach ($this->players as $player) {
            $player->roundReset();
            if (!$firstRound) {
                $player->getInventory()->earnMoney($this->calculateRoundMoneyAward($roundEndEvent, $player));
            }
            $spawnPosition = $this->getWorld()->getPlayerSpawnPosition($player->isPlayingOnAttackerSide(), $this->properties->randomize_spawn_position);
            $player->setPosition($spawnPosition);
        }
        $this->bombPlanted = false;
    }

    private function calculateRoundMoneyAward(RoundEndEvent $roundEndEvent, Player $player): int
    {
        $amount = 0;
        $attackersWins = $roundEndEvent->attackersWins;

        // Attacker side checks
        if ($player->isPlayingOnAttackerSide()) {
            $amount += $this->bombPlanted ? 800 : 0;
            if ($attackersWins) {
                $amount += match ($roundEndEvent->reason) {
                    RoundEndReason::ALL_ENEMIES_ELIMINATED => 3250,
                    RoundEndReason::BOMB_EXPLODED => 3500,
                    default => throw new GameException("New win reason? " . $roundEndEvent->reason->value),
                };
            } elseif (!$player->isAlive()) {
                $amount += $this->score->getMoneyLossBonus(true);
            }

            return $amount;
        }

        // Defender side checks
        if (!$attackersWins) {
            $amount += match ($roundEndEvent->reason) {
                RoundEndReason::ALL_ENEMIES_ELIMINATED, RoundEndReason::TIME_RUNS_OUT => 3250,
                RoundEndReason::BOMB_DEFUSED => 3500,
                default => throw new GameException("New win reason? " . $roundEndEvent->reason->value),
            };
        } else {
            $amount += $this->score->getMoneyLossBonus(false);
        }

        return $amount;
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
