<?php

namespace Test\Shooting;

use cs\Core\GameProperty;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Setting;
use cs\Core\Util;
use cs\Enum\BuyMenuItem;
use cs\Enum\Color;
use cs\Enum\InventorySlot;
use cs\Enum\RoundEndReason;
use cs\Equipment\Bomb;
use cs\Event\KillEvent;
use cs\Event\PlantEvent;
use cs\Event\RoundEndEvent;
use cs\Weapon\Knife;
use Test\BaseTestCase;

class BombTest extends BaseTestCase
{

    public function testInvalidBombPlantCases(): void
    {
        $gameProperty = new GameProperty();
        $gameProperty->bomb_plant_time_ms = 1;
        $gameProperty->bomb_explode_time_ms = 1;
        $gameProperty->freeze_time_sec = 1;

        $game = $this->createTestGame(null, $gameProperty);
        $game->getPlayer(1)->setPosition(new Point(500, 0, 500));
        $game->addPlayer(new Player(2, Color::BLUE, true));
        $this->playPlayer($game, [
            fn() => $this->assertTrue($game->isPaused()),
            fn(Player $p) => $p->equip(InventorySlot::SLOT_BOMB),
            fn(Player $p) => $p->attack(),
            $this->waitNTicks(Bomb::equipReadyTimeMs),
            fn(Player $p) => $p->attack(),
            $this->waitNTicks(1000),
            fn(Player $p) => $p->jump(),
            fn(Player $p) => $this->assertTrue($p->isFlying()),
            fn(Player $p) => $p->attack(),
            $this->waitXTicks(Setting::tickCountJump()),
            fn(Player $p) => $p->suicide(),
            fn(Player $p) => $p->attack(),
            $this->waitNTicks(1000),
            fn(Player $p) => $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_BOMB->value)),
            fn(Player $p) => $this->assertInstanceOf(Bomb::class, $p->getEquippedItem()),
            fn(Player $p) => $this->assertNotNull($p->dropEquippedItem()),
            fn(Player $p) => $this->assertInstanceOf(Knife::class, $p->getEquippedItem()),
            fn(Player $p) => $this->assertFalse($game->getWorld()->canAttack($p)),
            fn(Player $p) => $this->assertSame(1, $game->getRoundNumber()),
            $this->endGame(),
        ]);
    }

    public function testBombPlant(): void
    {
        $roundEndEvent = null;
        $killEvent = null;
        $plantEvent = null;
        $plantCount = 0;


        $properties = $this->createNoPauseGameProperty();
        $properties->bomb_plant_time_ms = 800;
        $properties->bomb_explode_time_ms = 1000;
        $game = $this->createTestGame(null, $properties);
        $game->onTick(function (GameState $state) {
            if ($state->getTickId() === 1) {
                $state->getPlayer(1)->equip(InventorySlot::SLOT_BOMB);
                return;
            }
            $state->getPlayer(1)->attack();
        });
        $game->onEvents(function (array $events) use (&$roundEndEvent, &$plantEvent, &$killEvent, &$plantCount) {
            foreach ($events as $event) {
                if ($event instanceof RoundEndEvent) {
                    $this->assertNull($roundEndEvent);
                    $roundEndEvent = $event;
                }
                if ($event instanceof PlantEvent) {
                    $plantEvent = $event;
                    $plantCount++;
                }
                if ($event instanceof KillEvent) {
                    $this->assertNull($killEvent);
                    $killEvent = $event;
                }
            }
        });

        $game->start();
        $this->assertFalse($game->getPlayer(1)->isAlive());
        $this->assertLessThan(Util::millisecondsToFrames($properties->round_time_ms), $game->getTickId());
        $this->assertInstanceOf(RoundEndEvent::class, $roundEndEvent);
        $this->assertSame([
            'roundNumber' => $roundEndEvent->roundNumberEnded,
            'newRoundNumber' => $roundEndEvent->roundNumberEnded + 1,
            'attackersWins' => $roundEndEvent->attackersWins,
            'score' => $game->getScore()->toArray(),
        ], $roundEndEvent->serialize());
        $this->assertInstanceOf(KillEvent::class, $killEvent);
        $this->assertSame($game->getPlayer(1), $killEvent->getPlayerDead());
        $this->assertSame($game->getPlayer(1), $killEvent->getPlayerCulprit());
        $this->assertInstanceOf(PlantEvent::class, $plantEvent);
        $this->assertSame([
            'timeMs' => 1000,
            'position' => (new Point())->toArray(),
        ], $plantEvent->serialize());
        $this->assertSame(1, $plantCount);
        $this->assertSame(RoundEndReason::BOMB_EXPLODED, $roundEndEvent->reason);
        $this->assertSame(
            Util::millisecondsToFrames(Bomb::equipReadyTimeMs) + Util::millisecondsToFrames($properties->bomb_plant_time_ms) + Util::millisecondsToFrames($properties->bomb_explode_time_ms),
            $game->getTickId() - 4
        );
    }

    protected function _testBombPlantRound(bool $shouldDefuse): void
    {
        $properties = $this->createNoPauseGameProperty();
        $properties->bomb_plant_time_ms = 1;
        $properties->bomb_defuse_time_ms = 1600;
        $properties->bomb_explode_time_ms = 1000;

        $game = $this->createTestGame(null, $properties);
        $defender = new Player(2, Color::BLUE, false);
        $game->addPlayer($defender);
        $defender->getSight()->look(180, -90);

        $roundEndEvent = null;
        $game->onTick(function (GameState $state) {
            if ($state->getTickId() === 1) {
                $state->getPlayer(1)->equip(InventorySlot::SLOT_BOMB);
                return;
            }
            $state->getPlayer(1)->attack();
            $state->getPlayer(2)->use();
        });
        $game->onEvents(function (array $events) use (&$roundEndEvent) {
            foreach ($events as $event) {
                if (!$roundEndEvent && $event instanceof RoundEndEvent) {
                    $roundEndEvent = $event;
                }
            }
        });

        if ($shouldDefuse) {
            $defender->buyItem(BuyMenuItem::DEFUSE_KIT);
        }
        $game->start();

        $this->assertInstanceOf(RoundEndEvent::class, $roundEndEvent);
        $this->assertSame($shouldDefuse ? RoundEndReason::BOMB_DEFUSED : RoundEndReason::BOMB_EXPLODED, $roundEndEvent->reason);
        if ($shouldDefuse) {
            $this->assertTrue($game->getScore()->defendersIsWinning());
            $this->assertFalse($game->getScore()->attackersIsWinning());
            $this->assertSame(700, $defender->getMoney());
            $this->assertFalse($game->isBombActive());
        } else {
            $this->assertTrue($game->getScore()->attackersIsWinning());
            $this->assertFalse($game->getScore()->defendersIsWinning());
        }
        $this->assertFalse($game->getScore()->isTie());
    }

    public function testBombPlantDefuse(): void
    {
        $this->_testBombPlantRound(false);
        $this->_testBombPlantRound(true);
    }

    public function testBombPlantReset(): void
    {
        $gameProperty = $this->createNoPauseGameProperty();
        $gameProperty->bomb_plant_time_ms = self::TEST_TICK_RATE * 2;
        $gameProperty->bomb_explode_time_ms = 100;
        $game = $this->createTestGame(null, $gameProperty);
        $start = new Point(321, 0, 300);

        $this->playPlayer($game, [
            fn(Player $p) => $p->setPosition($start),
            fn(Player $p) => $p->equip(InventorySlot::SLOT_BOMB),
            $this->waitNTicks(Bomb::equipReadyTimeMs),
            function (Player $p) {
                $p->moveForward();
                $p->attack();
                $this->assertFalse($p->isMoving());
            },
            fn(Player $p) => $this->assertPositionSame($start, $p->getPositionClone()),
            fn(Player $p) => $this->assertFalse($game->isBombActive()),
            fn(Player $p) => $this->assertSame(1, $game->getRoundNumber()),
            $this->waitNTicks(500),
            fn(Player $p) => $this->assertFalse($game->isBombActive()),
            fn(Player $p) => $this->assertSame(1, $game->getRoundNumber()),
            function (Player $p) use ($game) {
                $this->assertFalse($game->isBombActive());
                $p->attack();
                $this->assertFalse($game->isBombActive());
            },
            function (Player $p) use ($game) {
                $this->assertFalse($game->isBombActive());
                $p->attack();
                $this->assertFalse($game->isBombActive());
            },
            fn(Player $p) => $p->attack(),
            fn(Player $p) => $this->assertTrue($game->isBombActive()),
            fn(Player $p) => $this->assertSame(1, $game->getRoundNumber()),
            $this->waitNTicks(300),
        ]);

        $this->assertSame(2, $game->getRoundNumber());
        $this->assertSame(1100, $game->getPlayer(1)->getMoney());
        $score = $game->getScore()->toArray();
        $history = $score['history'] ?? false;
        $this->assertIsArray($history);
        $this->assertSame(RoundEndReason::BOMB_EXPLODED->value, $history[1]['reason'] ?? false);
        $this->assertSame([0, 1], $score['score'] ?? false);
        $this->assertSame([0, 1], $score['firstHalfScore'] ?? false);
        $this->assertSame([], $score['secondHalfScore'] ?? false);
        $this->assertSame(1100, $game->getPlayer(1)->getMoney());
    }


}
