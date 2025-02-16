<?php

namespace Test\Game;

use cs\Core\GameProperty;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Util;
use cs\Core\Wall;
use cs\Enum\BuyMenuItem;
use cs\Enum\Color;
use cs\Enum\GameOverReason;
use cs\Enum\InventorySlot;
use cs\Enum\SoundType;
use cs\Equipment\Bomb;
use cs\Event\GameOverEvent;
use cs\Event\KillEvent;
use cs\Event\PauseEndEvent;
use cs\Event\PauseStartEvent;
use cs\Event\PlantEvent;
use cs\Event\RoundEndCoolDownEvent;
use cs\Event\RoundEndEvent;
use cs\Event\RoundStartEvent;
use cs\Event\SoundEvent;
use cs\Map\DefaultMap;
use cs\Weapon\PistolGlock;
use cs\Weapon\RifleAk;
use Test\BaseTestCase;

class RoundTest extends BaseTestCase
{

    public function testMsToTickConstantTenOnTest(): void
    {
        $this->assertSame(0, Util::millisecondsToFrames(0));
        $this->assertSame(1, Util::millisecondsToFrames(1));
        $this->assertSame(1, Util::millisecondsToFrames(10));
        $this->assertSame(123, Util::millisecondsToFrames(1230));
        $this->assertSame(240, Util::millisecondsToFrames(RifleAk::reloadTimeMs));
    }

    public function testRoundEndWhenNoPlayersAreAlive(): void
    {
        $playerCommands = [
            fn(Player $p) => $p->jump(),
            fn(Player $p) => $p->suicide(),
            $this->endGame(),
        ];

        $break = false;
        $killEvents = [];
        $dropEvents = [];
        $game = $this->createNoPauseGame();
        $game->getPlayer(1)->setPosition(new Point(300, 0, 300));
        $game->onEvents(function (array $events) use (&$killEvents, &$dropEvents, &$break): void {
            if ($break) {
                return;
            }
            if ($events[0] instanceof GameOverEvent) {
                $break = true;
            }

            foreach ($events as $event) {
                if ($event instanceof KillEvent) {
                    $killEvents[] = $event;
                }
                if ($event instanceof SoundEvent && $event->type === SoundType::ITEM_DROP_LAND) {
                    $dropEvents[] = $event;
                }
            }
        });

        $this->assertSame(1, $game->getRoundNumber());
        $this->playPlayer($game, $playerCommands);
        $this->assertSame(2, $game->getRoundNumber());

        $this->assertTrue($break);
        $this->assertFalse($game->getPlayer(1)->isAlive());

        $this->assertCount(1, $killEvents);
        $killEvent = $killEvents[0];
        $this->assertInstanceOf(KillEvent::class, $killEvent); // @phpstan-ignore-line
        $this->assertSame([
            'playerDead' => $killEvent->getPlayerDead()->getId(),
            'playerCulprit' => $killEvent->getPlayerCulprit()->getId(),
            'itemId' => $killEvent->getAttackItemId(),
            'headshot' => $killEvent->wasHeadShot(),
        ], $killEvent->serialize());
        $this->assertFalse($killEvent->wasHeadShot());
        $this->assertSame(1, $killEvent->getPlayerDead()->getId());
        $this->assertSame(1, $killEvent->getPlayerCulprit()->getId());

        $this->assertCount(2, $dropEvents);
        $drop1 = $dropEvents[0];
        $drop2 = $dropEvents[1];
        $this->assertInstanceOf(SoundEvent::class, $drop1); // @phpstan-ignore-line
        $this->assertInstanceOf(SoundEvent::class, $drop2); // @phpstan-ignore-line
        $this->assertInstanceOf(PistolGlock::class, $drop1->getItem());
        $this->assertInstanceOf(Bomb::class, $drop2->getItem());
        $pp = $game->getPlayer(1)->getPositionClone();
        $pr = $game->getPlayer(1)->getBoundingRadius();
        $this->assertGreaterThan(0, $pp->y);
        $this->assertSame(0, $drop1->position->y);
        $this->assertLessThanOrEqual($pr, abs($drop1->position->x - $pp->x));
        $this->assertLessThanOrEqual($pr, abs($drop1->position->z - $pp->z));
        $this->assertSame(0, $drop2->position->y);
        $this->assertLessThanOrEqual($pr, abs($drop2->position->x - $pp->x));
        $this->assertLessThanOrEqual($pr, abs($drop2->position->z - $pp->z));
    }

    public function testSkippingTicksPlayerSimulation(): void
    {
        $called = false;
        $playerCommands = [
            $this->waitXTicks(10),
            fn(Player $p) => $p->buyItem(BuyMenuItem::RIFLE_AK),
            function (Player $p) use (&$called): void {
                $this->assertInstanceOf(RifleAk::class, $p->getEquippedItem());
                $called = true;
            },
        ];

        $game = $this->simulateGame($playerCommands, [GameProperty::START_MONEY => 16000]);
        $this->assertSame(13, $game->getTickId());
        $this->assertTrue($called);
    }

    public function testNoSpawnPosition(): void
    {
        $game = $this->createTestGame();
        $game->addPlayer(new Player(2, Color::YELLOW, true));
        $player = new Player(3, Color::GREEN, true);
        $this->expectExceptionMessage("Cannot find free spawn position for 'attacker' player");
        $game->addPlayer($player);
    }

    public function testFreezeTime(): void
    {
        $game = $this->createGame([
            GameProperty::FREEZE_TIME_SEC => 0,
        ]);
        $this->assertTrue($game->isPaused());
        $game->tick();
        $this->assertFalse($game->isPaused());
        $events = $game->consumeTickEvents();
        $this->assertCount(3, $events);
        $this->assertInstanceOf(PauseStartEvent::class, $events[0]);
        $this->assertInstanceOf(PauseEndEvent::class, $events[1]);
        $this->assertInstanceOf(RoundStartEvent::class, $events[2]);
    }

    public function testFreezeTime1(): void
    {
        $game = $this->createGame([
            GameProperty::FREEZE_TIME_SEC => 1,
        ]);

        foreach (range(0, (int)(1000 / Util::$TICK_RATE)) as $tickId) {
            $this->assertTrue($game->isPaused(), "Tick: {$tickId}");
            $game->tick();
        }
        $game->tick();
        $this->assertFalse($game->isPaused());
    }

    protected function _testRoundRandomizeSpawnPosition(bool $randomize): void
    {
        $spawns = [];
        $sights = [];

        $game = $this->createGame([
            GameProperty::MAX_ROUNDS => 33,
            GameProperty::RANDOMIZE_SPAWN_POSITION => $randomize,
        ]);
        $p = new Player(2, Color::BLUE, false);
        $game->addPlayer($p);

        $game->onEvents(function (array $events) use ($p, $game, &$spawns, &$sights): void {
            foreach ($events as $event) {
                if ($p->isPlayingOnAttackerSide()) {
                    $game->quit(GameOverReason::ATTACKERS_SURRENDER);
                    return;
                }

                if ($event instanceof RoundStartEvent) {
                    $spawns[] = $p->getPositionClone()->hash();
                    $sights[] = $p->getSight()->getRotationHorizontal();
                    $p->getSight()->lookHorizontal(123);
                }
            }
        });
        $game->start();

        $this->assertSame(17, $game->getRoundNumber());
        $this->assertNotEmpty($spawns);
        $this->assertNotEmpty($sights);

        if ($randomize) {
            $this->assertGreaterThan(2, count(array_unique($spawns)));
            $this->assertGreaterThan(2, count(array_unique($sights)));
        } else {
            $this->assertCount(1, array_unique($spawns));
            $this->assertCount(1, array_unique($sights));
        }
    }

    public function testRoundRandomizeSpawnPositionTrue(): void
    {
        $this->_testRoundRandomizeSpawnPosition(true);
    }

    public function testRoundRandomizeSpawnPositionFalse(): void
    {
        $this->_testRoundRandomizeSpawnPosition(false);
    }

    public function testRoundEndEventFiredOncePerRoundEndActually(): void
    {
        $maxRounds = 5;
        $game = $this->createGame([
            GameProperty::MAX_ROUNDS => $maxRounds,
            GameProperty::ROUND_TIME_MS => 1,
        ]);

        $roundEndEventsCount = 0;
        $roundCoolDownEventsCount = 0;
        $game->setTickMax($maxRounds * 2);
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->moveForward();
        });
        $game->onEvents(function (array $events) use (&$roundEndEventsCount, &$roundCoolDownEventsCount): void {
            foreach ($events as $event) {
                if ($event instanceof RoundEndEvent) {
                    $roundEndEventsCount++;
                }
                if ($event instanceof RoundEndCoolDownEvent) {
                    $roundCoolDownEventsCount++;
                }
            }
        });
        $game->start();
        $this->assertSame($maxRounds, $roundEndEventsCount);
        $this->assertSame($maxRounds - 2, $roundCoolDownEventsCount); // (firstRound + halfTime)
        $this->assertSame($maxRounds + 1, $game->getRoundNumber());
    }

    public function testHalfTimeSwitch(): void
    {
        $maxRounds = 5;
        $game = $this->createGame([
            GameProperty::MAX_ROUNDS => $maxRounds,
            GameProperty::ROUND_TIME_MS => 1,
            GameProperty::HALF_TIME_FREEZE_SEC => 0,
            GameProperty::START_MONEY => 3000,
        ]);
        $playerAttackerSpawnPosition = $game->getPlayer(1)->getPositionClone();
        $game->setTickMax($maxRounds * 2);

        $this->assertTrue($game->getPlayer(1)->isPlayingOnAttackerSide());
        $this->assertTrue($game->getPlayer(1)->buyItem(BuyMenuItem::RIFLE_AK));
        $this->assertTrue($game->getScore()->isTie());

        $game->start();
        $this->assertSame($maxRounds + 1, $game->getRoundNumber());
        $this->assertFalse($game->getPlayer(1)->isPlayingOnAttackerSide());
        $this->assertNotInstanceOf(RifleAk::class, $game->getPlayer(1)->getEquippedItem());
        $this->assertFalse($game->getScore()->isTie());
        $this->assertTrue($game->getScore()->defendersIsWinning());
        $this->assertFalse($game->getScore()->attackersIsWinning());
        $this->assertSame(3, $game->getScore()->getScoreDefenders());
        $this->assertSame(2, $game->getScore()->getScoreAttackers());
        $this->assertSame(9500, $game->getPlayer(1)->getMoney());
        $this->assertPositionNotSame($playerAttackerSpawnPosition, $game->getPlayer(1)->getPositionClone());
        $this->assertFalse($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_BOMB->value));
        $this->assertSame(9500, $game->getPlayer(1)->getMoney());
        $this->assertSame([2, 0], $game->getScore()->toArray()['firstHalfScore']);
        $this->assertSame([3, 0], $game->getScore()->toArray()['secondHalfScore']);
        $this->assertSame(2, $game->getScore()->toArray()['halfTimeRoundNumber']);
    }

    public function testOnlyOneAttackerHasBombInInventory(): void
    {
        $gameProperty = $this->createNoPauseGameProperty();
        $gameProperty->max_rounds = 6;

        $game = $this->createTestGame(null ,$gameProperty);
        $game->loadMap(new DefaultMap());
        $game->addPlayer(new Player(2, Color::BLUE, true));
        $game->addPlayer(new Player(3, Color::BLUE, true));

        $bombCount = 0;
        foreach ($game->getPlayers() as $player) {
            $bombCount += (int)$player->getInventory()->has(InventorySlot::SLOT_BOMB->value);
        }
        $this->assertSame(1, $bombCount);

        $game->onEvents(function (array $events) use ($game): void {
            if (!$game->getPlayer(1)->isPlayingOnAttackerSide()) {
                $game->quit(GameOverReason::ATTACKERS_SURRENDER);
                return;
            }

            foreach ($events as $event) {
                if ($event instanceof PauseEndEvent) {
                    $bombCount = 0;
                    foreach ($game->getPlayers() as $player) {
                        $bombCount += (int)$player->getInventory()->has(InventorySlot::SLOT_BOMB->value);
                    }
                    $this->assertSame(1, $bombCount);
                }
            }
        });
        $game->start();
        $this->assertSame(4, $game->getRoundNumber());
    }

    public function testRoundEndCoolDown(): void
    {
        $gameProperty = $this->createNoPauseGameProperty();
        $gameProperty->round_end_cool_down_sec = 1;
        $gameProperty->max_rounds = 6;
        $game = $this->createTestGame(null, $gameProperty);
        $pos = new Point(501, 0, 502);
        $enemy = new Player(2, Color::BLUE, false);
        $game->addPlayer($enemy);
        $enemy->setPosition($pos->clone()->addX(-300));

        $this->playPlayer($game, [
            fn() => $enemy->getSight()->look(-90, 0),
            fn() => $enemy->equipSecondaryWeapon(),
            fn() => $this->assertSame(1, $game->getRoundNumber()),
            fn(Player $p) => $p->setPosition($pos),
            fn(Player $p) => $p->suicide(),
            fn() => $this->assertSame(2, $game->getRoundNumber()),
            fn(Player $p) => $this->assertFalse($p->isAlive()),
            $this->waitNTicks(500),
            fn(Player $p) => $this->assertFalse($p->isAlive()),
            fn(Player $p) => $this->assertPositionSame($pos, $p->getPositionClone()),
            function () use ($enemy) {
                $result = $this->assertPlayerNotHit($enemy->attack());
                $hits = $result->getHits();
                $this->assertCount(1, $hits);
                $wall = $hits[0];
                $this->assertInstanceOf(Wall::class, $wall);
                $this->assertPositionSame($enemy->getSightPositionClone()->setX(-1), $result->getBullet()->getPosition());
            },
            $this->waitNTicks(500),
            $this->endGame(),
        ]);

        $this->assertSame(2, $game->getRoundNumber());
        $this->assertTrue($game->getPlayer(1)->isAlive());
        $this->assertTrue($game->getPlayer(1)->isPlayingOnAttackerSide());
    }

    public function testKillInRoundEndCoolDown(): void
    {
        $gameProperty = $this->createNoPauseGameProperty();
        $gameProperty->start_money = 0;
        $gameProperty->freeze_time_sec = 1;
        $gameProperty->round_end_cool_down_sec = 1;
        $gameProperty->bomb_plant_time_ms = 0;
        $gameProperty->bomb_defuse_time_ms = 0;
        $gameProperty->max_rounds = 6;
        $game = $this->createTestGame(null, $gameProperty);
        $game->getPlayer(1)->setPosition(new Point(500, 0, 500));
        $enemy = new Player(2, Color::BLUE, false);
        $game->addPlayer($enemy);
        $enemy->setPosition($game->getPlayer(1)->getPositionClone());
        $enemy->getSight()->look(0, -90);

        $this->playPlayer($game, [
            fn(Player $p) => $p->equip(InventorySlot::SLOT_BOMB),
            $this->waitNTicks(max(1000, Bomb::equipReadyTimeMs)),
            fn(Player $p) => $p->attack(),
            fn() => $this->assertSame(1, $game->getRoundNumber()),
            fn() => $this->assertSame(0, $enemy->getMoney()),
            fn() => $enemy->use(),
            fn() => $this->assertSame(300, $enemy->getMoney()),
            fn() => $this->assertSame(2, $game->getRoundNumber()),
            function () use ($enemy) {
                $enemy->setPosition($enemy->getPositionClone()->addX(500));
                $enemy->getSight()->look(-90, 0);
                $result = $this->assertPlayerHit($enemy->attack());
                $this->assertCount(2, $result->getHits());
                $this->assertSame(300, $result->getMoneyAward());
                $this->assertSame(600, $enemy->getMoney());
            },
            $this->waitNTicks(1000),
            $this->endGame(),
        ]);

        $this->assertSame(2, $game->getRoundNumber());
        $this->assertSame(300 + 800 + 1400, $game->getPlayer(1)->getMoney());
        $this->assertSame(300 + 300 + 3500, $game->getPlayer(2)->getMoney());
    }

    public function testMultipleRoundsScoreAndEvents(): void
    {
        $maxRounds = 4;
        $gameProperty = $this->createNoPauseGameProperty($maxRounds);
        $gameProperty->bomb_plant_time_ms = 0;
        $gameProperty->bomb_defuse_time_ms = 0;
        $gameProperty->bomb_explode_time_ms = 200;
        $gameProperty->round_time_ms = 500;
        $game = $this->createTestGame(null, $gameProperty);
        $p1 = $game->getPlayer(1);
        $p2 = new Player(2, Color::BLUE, false);
        $game->addPlayer($p2);
        $start = new Point(500, 0, 500);
        $p2->setPosition($start);
        $p1->setPosition($p2->getPositionClone());
        $p1->getSight()->look(0, -90);
        $p2->getSight()->look(0, -90);

        $eventCounts = [];
        $eventObjects = [];
        $game->onEvents(function (array $events) use (&$eventCounts, &$eventObjects): void {
            foreach ($events as $event) {
                if (!isset($eventCounts[$event::class])) {
                    $eventCounts[$event::class] = 0;
                }
                $eventCounts[$event::class]++;
                $eventObjects[$event::class] = $event;
            }
        });

        $this->playPlayer($game, [
            fn() => $this->assertTrue($p1->equip(InventorySlot::SLOT_BOMB)),
            $this->waitNTicks(Bomb::equipReadyTimeMs),
            fn() => $p1->attack(),
            fn() => $this->assertPositionSame($start, $p2->getPositionClone()),
            function () use ($p2) {
                $p2->moveForward();
                $p2->use();
                $this->assertFalse($p2->isMoving());
            },
            fn() => $this->assertPositionSame($start, $p2->getPositionClone()),
            fn() => $this->assertSame(2, $game->getRoundNumber()),
            fn() => $this->assertTrue($p1->equip(InventorySlot::SLOT_BOMB)),
            $this->waitNTicks(Bomb::equipReadyTimeMs),
            fn() => $p1->attack(),
            $this->waitNTicks(220),
            fn() => $this->assertSame(3, $game->getRoundNumber()),
            fn() => $p1->setPosition(new Point(500, 0, 500)),
            fn() => $p2->setPosition(new Point(500, 0, 500)),
            fn() => $this->assertTrue($p1->buyItem(BuyMenuItem::GRENADE_FLASH)),
            fn() => $p1->buyItem(BuyMenuItem::DEFUSE_KIT),
            fn() => $this->assertTrue($p1->getInventory()->has(InventorySlot::SLOT_KIT->value)),
            fn() => $p2->buyItem(BuyMenuItem::DEFUSE_KIT),
            fn() => $this->assertFalse($p2->getInventory()->has(InventorySlot::SLOT_KIT->value)),
            fn() => $this->assertTrue($p2->buyItem(BuyMenuItem::GRENADE_FLASH)),
            fn() => $this->assertTrue($p2->buyItem(BuyMenuItem::GRENADE_FLASH)),
            fn() => $this->assertTrue($p2->buyItem(BuyMenuItem::GRENADE_DECOY)),
            fn() => $p1->suicide(),
            fn() => $this->assertSame(4, $game->getRoundNumber()),
            function () use ($p1, $p2) {
                $this->assertFalse($p1->hasDefuseKit());
                $this->assertFalse($p2->hasDefuseKit());
                $this->assertFalse($p1->getInventory()->has(InventorySlot::SLOT_BOMB->value));
                $this->assertTrue($p2->getInventory()->has(InventorySlot::SLOT_BOMB->value));
                $this->assertTrue($p2->getInventory()->has(InventorySlot::SLOT_GRENADE_FLASH->value));
                $this->assertTrue($p2->getInventory()->has(InventorySlot::SLOT_GRENADE_DECOY->value));
                $this->assertFalse($p1->getInventory()->has(InventorySlot::SLOT_GRENADE_FLASH->value));
            },
            $this->waitNTicks(800),
        ]);

        $this->assertNotEmpty($eventCounts);
        $this->assertSame($maxRounds + 1, $game->getRoundNumber());
        $this->assertSame($maxRounds + 1, $eventCounts[PauseStartEvent::class]);
        $this->assertSame($maxRounds, $eventCounts[PauseEndEvent::class]);
        $this->assertSame($maxRounds, $eventCounts[RoundStartEvent::class]);
        $this->assertSame($maxRounds, $eventCounts[RoundEndEvent::class]);
        $this->assertSame(2, $eventCounts[PlantEvent::class]);
        $this->assertSame($maxRounds - 2, $eventCounts[RoundEndCoolDownEvent::class]);
        $this->assertTrue($game->getScore()->isTie());

        $expectedScoreBoard = [
            'score' => [2, 2],
            'lossBonus' => [1400, 1400],
            'history' => [
                1 => [
                    'attackersWins' => false,
                    'reason' => 2,
                    'scoreAttackers' => 0,
                    'scoreDefenders' => 1,
                ],
                2 => [
                    'attackersWins' => true,
                    'reason' => 3,
                    'scoreAttackers' => 1,
                    'scoreDefenders' => 1,
                ],
                3 => [
                    'attackersWins' => true,
                    'reason' => 0,
                    'scoreAttackers' => 2,
                    'scoreDefenders' => 1,
                ],
                4 => [
                    'attackersWins' => false,
                    'reason' => 1,
                    'scoreAttackers' => 2,
                    'scoreDefenders' => 2,
                ],
            ],
            'firstHalfScore' => [1, 1],
            'secondHalfScore' => [1, 1],
            'halfTimeRoundNumber' => 2,
            'scoreboard' => [
                [
                    [
                        'id' => 1,
                        'kills' => -1,
                        'deaths' => 1,
                        'damage' => 0,
                    ],
                ],
                [
                    [
                        'id' => 2,
                        'kills' => 0,
                        'deaths' => 0,
                        'damage' => 0,
                    ],
                ],
            ],
        ];
        $this->assertSame($expectedScoreBoard, $game->getScore()->toArray());
    }

    public function testBombExplodeMoney(): void
    {
        $maxRounds = 4;
        $gameProperty = $this->createNoPauseGameProperty($maxRounds);
        $gameProperty->bomb_plant_time_ms = 0;
        $gameProperty->bomb_explode_time_ms = 0;
        $gameProperty->round_time_ms = Bomb::equipReadyTimeMs * 2;
        $this->assertGreaterThan(Util::$TICK_RATE, $gameProperty->round_time_ms);
        $game = $this->createTestGame(null, $gameProperty);

        $this->playPlayer($game, [
            fn(Player $p) => $p->equip(InventorySlot::SLOT_BOMB),
            $this->waitNTicks(Bomb::equipReadyTimeMs),
            fn(Player $p) => $p->setPosition(new Point(500, 0, 500)),
            fn(Player $p) => $p->attack(),
            fn() => $this->assertSame(1, $game->getRoundNumber()),
            fn() => $this->assertSame(2, $game->getRoundNumber()),
            fn(Player $p) => $this->assertSame(800 + 300 + 800 + 3500, $p->getMoney()),
            $this->waitNTicks(3000),
        ]);

        $this->assertSame($maxRounds + 1, $game->getRoundNumber());
        $this->assertSame(4050, $game->getPlayer(1)->getMoney());

        $gameOver = $game->tick();
        $this->assertInstanceOf(GameOverEvent::class, $gameOver);
        $this->assertSame(GameOverReason::DEFENDERS_WINS, $gameOver->reason);
    }

    public function testNoMoneyForAttackerIfSurvivedRoundWithoutBombPlant(): void
    {
        $maxRounds = 4;
        $game = $this->createNoPauseGame($maxRounds);
        $game->addPlayer(new Player(2, Color::GREEN, false));

        $game->start();

        $this->assertSame($maxRounds + 1, $game->getRoundNumber());
        $this->assertSame(4050, $game->getPlayer(1)->getMoney());
        $this->assertSame(800, $game->getPlayer(2)->getMoney());
    }

}
