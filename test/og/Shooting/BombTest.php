<?php

namespace Test\Shooting;

use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Util;
use cs\Enum\BuyMenuItem;
use cs\Enum\Color;
use cs\Enum\InventorySlot;
use cs\Enum\RoundEndReason;
use cs\Event\PlantEvent;
use cs\Event\RoundEndEvent;
use Test\BaseTestCase;

class BombTest extends BaseTestCase
{

    public function testBombPlant(): void
    {
        $roundEndEvent = null;
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
        $game->onEvents(function (array $events) use (&$roundEndEvent, &$plantEvent, &$plantCount) {
            foreach ($events as $event) {
                if ($event instanceof RoundEndEvent) {
                    $roundEndEvent = $event;
                }
                if ($event instanceof PlantEvent) {
                    $plantEvent = $event;
                    $plantCount++;
                }
            }
        });

        $game->start();
        $this->assertFalse($game->getPlayer(1)->isAlive());
        $this->assertLessThan(Util::millisecondsToFrames($properties->round_time_ms), $game->getTickId());
        $this->assertInstanceOf(RoundEndEvent::class, $roundEndEvent);
        $this->assertInstanceOf(PlantEvent::class, $plantEvent);
        $this->assertSame(1, $plantCount);
        $this->assertSame(RoundEndReason::BOMB_EXPLODED, $roundEndEvent->reason);
        $this->assertSame(
            Util::millisecondsToFrames($properties->bomb_plant_time_ms) + Util::millisecondsToFrames($properties->bomb_explode_time_ms),
            $game->getTickId() - 3
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
        $defender->getSight()->lookAt(180, -90);

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


}
