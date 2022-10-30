<?php

namespace Test\Shooting;

use cs\Core\GameState;
use cs\Core\Util;
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
            $game->getTickId() - 4
        );
    }

}
