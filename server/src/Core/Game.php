<?php

namespace cs\Core;

use cs\Enum\GameOverReason;
use cs\Enum\ItemId;
use cs\Enum\PauseReason;
use cs\Enum\RoundEndReason;
use cs\Enum\SoundType;
use cs\Equipment\Bomb;
use cs\Event\DropEvent;
use cs\Event\Event;
use cs\Event\GameOverEvent;
use cs\Event\KillEvent;
use cs\Event\PauseEndEvent;
use cs\Event\PauseStartEvent;
use cs\Event\PlantEvent;
use cs\Event\RoundEndCoolDownEvent;
use cs\Event\RoundEndEvent;
use cs\Event\RoundStartEvent;
use cs\Event\SoundEvent;
use cs\Event\ThrowEvent;
use cs\Interface\ForOneRoundMax;
use cs\Map\Map;

class Game
{

    private Bomb $bomb;
    private World $world;
    private Score $score;
    private GameState $state;
    private Backtrack $backtrack;
    private GameProperty $properties;
    private ?GameOverEvent $gameOver = null;
    private PauseStartEvent $startRoundFreezeTime;

    /** @var Player[] */
    private array $players = [];
    /** @var Event[] */
    private array $events = [];
    /** @var Event[] */
    private array $tickEvents = [];

    protected int $tick = 0;
    private int $eventId = 0;
    private int $roundNumber = 1;
    private int $roundStartTickId = 0;
    private int $roundTickCount = 0;
    private int $buyTimeTickCount = 0;
    private int $playersCountAttackers = 0;
    private int $playersCountDefenders = 0;
    private bool $paused = true;
    private bool $roundEndCoolDown = false;
    private bool $bombPlanted = false;
    private ?int $bombEventId = null;

    public function __construct(GameProperty $properties = new GameProperty())
    {
        $this->bomb = new Bomb($properties->bomb_plant_time_ms, $properties->bomb_defuse_time_ms);
        $this->state = new GameState($this);
        $this->score = new Score($properties->loss_bonuses);
        $this->backtrack = new Backtrack($this, $properties->backtrack_history_tick_count);
        $this->properties = $properties;
        $this->world = new World($this);

        $this->initialize();
    }

    private function initialize(): void
    {
        $this->roundTickCount = Util::millisecondsToFrames($this->properties->round_time_ms);
        $this->buyTimeTickCount = Util::millisecondsToFrames($this->properties->buy_time_sec * 1000);
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
        $this->backtrack->startState();
        foreach ($this->players as $player) {
            if (!$player->isAlive()) {
                continue;
            }

            $player->onTick($tickId);
            if ($player->isAlive()) {
                $alivePlayers[(int)$player->isPlayingOnAttackerSide()]++;
                $this->backtrack->addStateData($player);
            }
        }
        $this->backtrack->finishState();
        $this->checkRoundEnd($alivePlayers[0], $alivePlayers[1]);
        $this->processEvents($tickId);
        return null;
    }

    private function checkRoundEnd(int $defendersAlive, int $attackersAlive): void
    {
        if ($this->roundEndCoolDown) {
            return;
        }

        if ($this->playersCountAttackers > 0 && $attackersAlive === 0) {
            $this->roundEnd(false, RoundEndReason::ALL_ENEMIES_ELIMINATED);
            return;
        }
        if ($this->playersCountDefenders > 0 && $defendersAlive === 0) {
            $this->roundEnd(true, RoundEndReason::ALL_ENEMIES_ELIMINATED);
            return;
        }

        if (!$this->bombPlanted && $this->roundStartTickId + $this->roundTickCount === $this->tick) {
            $this->roundEnd(false, RoundEndReason::TIME_RUNS_OUT);
            return;
        }
    }

    private function addEvent(Event $event): int
    {
        $eventId = $this->eventId++;
        $this->events[$eventId] = $event;
        $event->customId = $eventId;
        $event->onComplete[] = fn(Event $e) => $this->removeEvent($e->customId);

        $this->tickEvents[] = $event;
        return $eventId;
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
        $player->getSight()->lookHorizontal($this->getWorld()->getPlayerSpawnRotationHorizontal($player->isPlayingOnAttackerSide(), $this->properties->randomize_spawn_position ? 80 : 0));

        $this->players[$player->getId()] = $player;
        $this->world->addPlayer($player);
        if ($player->isPlayingOnAttackerSide()) {
            $this->playersCountAttackers++;
            if ($this->playersCountAttackers === 1) {
                $this->spawnBomb();
            }
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

    public function isBombActive(): bool
    {
        return $this->bombPlanted && $this->bombEventId !== null;
    }

    public function getTickId(): int
    {
        return $this->tick;
    }

    public function playersCanBuy(): bool
    {
        return ($this->isPaused() || $this->tick <= $this->roundStartTickId + $this->buyTimeTickCount);
    }

    public function loadMap(Map $map): void
    {
        $this->bomb->setMaxBlastDistance($map->getBombMaxBlastDistance());
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

    public function addThrowEvent(ThrowEvent $event): void
    {
        $this->addEvent($event);
    }

    public function addDropEvent(DropEvent $event): void
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
        $sound = new SoundEvent($playerDead->getPositionClone(), SoundType::PLAYER_DEAD);
        $this->addSoundEvent($sound->setPlayer($playerDead));
    }

    public function playerFallDamageKilledEvent(Player $playerDead): void
    {
        $this->score->getPlayerStat($playerDead->getId())->removeKill();
        $this->score->getPlayerStat($playerDead->getId())->addDeath();

        $this->addEvent(new KillEvent($playerDead, $playerDead, ItemId::SOLID_SURFACE, false));
        $sound = new SoundEvent($playerDead->getPositionClone(), SoundType::PLAYER_DEAD);
        $this->addSoundEvent($sound->setPlayer($playerDead));
    }

    public function playerBombKilledEvent(Player $playerDead): void
    {
        $this->addEvent(new KillEvent($playerDead, $playerDead, ItemId::BOMB, false));
        $sound = new SoundEvent($playerDead->getPositionClone(), SoundType::PLAYER_DEAD);
        $this->addSoundEvent($sound->setPlayer($playerDead)->setItem($this->bomb));
    }

    private function spawnBomb(): void
    {
        $this->bombReset();
        $this->bombPlanted = false;
        if ($this->playersCountAttackers === 0) {
            return;
        }

        /** @var Player[] $attackers */
        $attackers = array_values(array_filter($this->players, fn(Player $player) => $player->isPlayingOnAttackerSide()));
        $bombCarrier = $attackers[rand(0, count($attackers) - 1)];
        $bombCarrier->getInventory()->pickup($this->bomb);
    }

    private function bombReset(): void
    {
        $this->bomb->reset();
        if (null !== $this->bombEventId) {
            unset($this->events[$this->bombEventId]);
            $this->bombEventId = null;
        }
    }

    public function bombDefused(Player $defuser): void
    {
        $defuser->getInventory()->earnMoney(300);
        $sound = new SoundEvent($this->bomb->getPosition(), SoundType::BOMB_DEFUSED);
        $this->addSoundEvent($sound->setItem($this->bomb));
        $this->roundEnd(false, RoundEndReason::BOMB_DEFUSED);
        $this->bombReset();
    }

    public function bombPlanted(Player $planter): void
    {
        $this->bombPlanted = true;
        $planter->getInventory()->earnMoney(300);
        $sound = new SoundEvent($this->bomb->getPosition(), SoundType::BOMB_PLANTED);
        $this->addSoundEvent($sound->setItem($this->bomb));

        $event = new PlantEvent(function (): void {
            $sound = new SoundEvent($this->bomb->getPosition(), SoundType::BOMB_EXPLODED);
            $this->addSoundEvent($sound->setItem($this->bomb));
            $this->roundEnd(true, RoundEndReason::BOMB_EXPLODED);

            foreach ($this->getAlivePlayers() as $player) {
                $this->bomb->explodeDamageToPlayer($player);
                if (!$player->isAlive()) {
                    $this->playerBombKilledEvent($player);
                }
            }
            $this->bombEventId = null;
        }, $this->properties->bomb_explode_time_ms, $this->bomb->getPosition());
        $this->bombEventId = $this->addEvent($event);
    }

    public function roundEnd(bool $attackersWins, RoundEndReason $reason): void
    {
        if ($this->roundEndCoolDown) {
            return;
        }

        $this->roundEndCoolDown = true;
        $roundEndEvent = new RoundEndEvent($this, $attackersWins, $reason);
        $roundEndEvent->onComplete[] = fn() => $this->endRound($roundEndEvent);
        $this->addEvent($roundEndEvent);
    }

    private function endRound(RoundEndEvent $roundEndEvent): void
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
            $callback = function () use ($startRoundFreezeTime, $roundEndEvent): void {
                $this->halfTimeSwapTeams();
                $this->roundReset(true, $roundEndEvent);
                $this->addEvent($startRoundFreezeTime);
            };
            $event = new PauseStartEvent($this, PauseReason::HALF_TIME, $callback, $this->properties->half_time_freeze_sec * 1000);
            $this->paused = true;
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
        $attackersCount = $this->playersCountAttackers;
        $this->playersCountAttackers = $this->playersCountDefenders;
        $this->playersCountDefenders = $attackersCount;
        $this->score->swapTeams();

        foreach ($this->players as $player) {
            $player->swapTeam();
            $player->getInventory()->earnMoney(-$player->getInventory()->getDollars());
            $player->getInventory()->earnMoney($this->properties->start_money);
            $player->getInventory()->reset($player->isPlayingOnAttackerSide(), true);
        }
    }

    private function roundReset(bool $firstRound, RoundEndEvent $roundEndEvent): void
    {
        $this->world->roundReset();
        foreach ($this->events as $event) {
            if ($event instanceof ForOneRoundMax) {
                $this->removeEvent($event->customId);
            }
        }
        $randomizeSpawn = $this->properties->randomize_spawn_position;
        foreach ($this->players as $player) {
            if (!$firstRound) {
                $player->getInventory()->earnMoney($this->calculateRoundMoneyAward($roundEndEvent, $player));
            }
            $player->roundReset();
            $spawnPosition = $this->getWorld()->getPlayerSpawnPosition($player->isPlayingOnAttackerSide(), $randomizeSpawn);
            $player->getSight()->lookHorizontal($this->getWorld()->getPlayerSpawnRotationHorizontal($player->isPlayingOnAttackerSide(), $randomizeSpawn ? 80 : 0));
            $player->setPosition($spawnPosition);
        }
        $this->spawnBomb();
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

    public function getScore(): Score
    {
        return $this->score;
    }

    public function getProperties(): GameProperty
    {
        return $this->properties;
    }

    public function getBacktrack(): Backtrack
    {
        return $this->backtrack;
    }

    /**
     * @return Player[]
     */
    public function getAlivePlayers(): array
    {
        return array_filter($this->players, fn(Player $player) => $player->isAlive());
    }

    /**
     * @return Player[]
     */
    public function getPlayers(): array
    {
        return $this->players;
    }

}
