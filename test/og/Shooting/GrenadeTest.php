<?php

namespace Test\Shooting;

use cs\Core\Floor;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Setting;
use cs\Core\Wall;
use cs\Enum\BuyMenuItem;
use cs\Enum\InventorySlot;
use cs\Enum\SoundType;
use cs\Equipment\Decoy;
use cs\Equipment\Flashbang;
use cs\Event\SoundEvent;
use Test\BaseTestCase;

class GrenadeTest extends BaseTestCase
{

    public function testThrow(): void
    {
        $floorY = 100;
        $landEvent = null;
        $bounceEvent = null;
        $game = $this->createTestGame();
        $game->getWorld()->addFloor(new Floor(new Point(-50, $floorY, 300), 1500, 3500));
        $game->onEvents(function (array $events) use (&$landEvent, &$bounceEvent): void {
            foreach ($events as $event) {
                if (!($event instanceof SoundEvent)) {
                    continue;
                }
                if ($event->type === SoundType::GRENADE_LAND) {
                    $this->assertTrue(is_null($landEvent), 'Only one landEvent please');
                    $landEvent = $event;
                }
                if (!$bounceEvent && $event->type === SoundType::GRENADE_BOUNCE) {
                    $bounceEvent = $event;
                }
            }
        });
        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(12, 15),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_DECOY)),
            fn(Player $p) => $this->assertTrue($p->equip(InventorySlot::SLOT_GRENADE_DECOY)),
            $this->waitNTicks(Decoy::equipReadyTimeMs),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            $this->waitNTicks(1800),
            $this->endGame(),
        ]);

        $floorY += Decoy::boundingRadius;
        $this->assertFalse($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_GRENADE_DECOY->value));
        $this->assertInstanceOf(SoundEvent::class, $bounceEvent);
        $this->assertInstanceOf(SoundEvent::class, $landEvent);
        $this->assertPositionNotSame(new Point(), $landEvent->position);
        $this->assertGreaterThan(1, $bounceEvent->position->z);
        $this->assertPositionNotSame($bounceEvent->position, $landEvent->position);
        $this->assertGreaterThan($bounceEvent->position->z, $landEvent->position->z);
        $this->assertSame($floorY, $bounceEvent->position->y);
        $this->assertSame($floorY, $landEvent->position->y);
        $this->assertPositionSame(new Point(152, $floorY, 720), $landEvent->position);
    }

    public function testThrowRun(): void
    {
        $floorY = 100;
        $landEvent = null;
        $bounceEvent = null;
        $game = $this->createTestGame();
        $game->getWorld()->addFloor(new Floor(new Point(-50, $floorY, 300), 1500, 3500));
        $game->onEvents(function (array $events) use (&$landEvent, &$bounceEvent): void {
            foreach ($events as $event) {
                if (!($event instanceof SoundEvent)) {
                    continue;
                }
                if ($event->type === SoundType::GRENADE_LAND) {
                    $this->assertTrue(is_null($landEvent), 'Only one landEvent please');
                    $landEvent = $event;
                }
                if (!$bounceEvent && $event->type === SoundType::GRENADE_BOUNCE) {
                    $bounceEvent = $event;
                }
            }
        });
        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(12, 15),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_DECOY)),
            fn(Player $p) => $this->assertTrue($p->equip(InventorySlot::SLOT_GRENADE_DECOY)),
            $this->waitNTicks(Decoy::equipReadyTimeMs),
            function (Player $p) {
                $p->moveForward();
                $this->assertNotNull($p->attack());
            },
            $this->waitNTicks(1800),
            $this->endGame(),
        ]);

        $floorY += Decoy::boundingRadius;
        $this->assertFalse($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_GRENADE_DECOY->value));
        $this->assertInstanceOf(SoundEvent::class, $bounceEvent);
        $this->assertInstanceOf(SoundEvent::class, $landEvent);
        $this->assertPositionNotSame(new Point(), $landEvent->position);
        $this->assertGreaterThan(1, $bounceEvent->position->z);
        $this->assertPositionNotSame($bounceEvent->position, $landEvent->position);
        $this->assertGreaterThan($bounceEvent->position->z, $landEvent->position->z);
        $this->assertSame($floorY, $bounceEvent->position->y);
        $this->assertSame($floorY, $landEvent->position->y);
        $this->assertPositionSame(new Point(220, $floorY, 1022), $landEvent->position);
    }

    public function testThrow2(): void
    {
        $landEvent = null;
        $bounceEvent = null;
        $game = $this->createTestGame();
        $game->getWorld()->addWall(new Wall(new Point(0, 0, 500), true, 9999));
        $game->onEvents(function (array $events) use (&$landEvent, &$bounceEvent): void {
            foreach ($events as $event) {
                if (!($event instanceof SoundEvent)) {
                    continue;
                }
                if ($event->type === SoundType::GRENADE_LAND) {
                    $this->assertTrue(is_null($landEvent), 'Only one landEvent please');
                    $landEvent = $event;
                }
                if ($event->type === SoundType::GRENADE_BOUNCE) {
                    $bounceEvent = $event;
                }
            }
        });
        $this->playPlayer($game, [
            fn(Player $p) => $p->crouch(),
            fn(Player $p) => $p->getSight()->look(45, 45),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_DECOY)),
            fn(Player $p) => $this->assertTrue($p->equip(InventorySlot::SLOT_GRENADE_DECOY)),
            $this->waitNTicks(Decoy::equipReadyTimeMs),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            $this->waitNTicks(1700),
            $this->endGame(),
        ]);

        $y = Decoy::boundingRadius;
        $this->assertFalse($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_GRENADE_DECOY->value));
        $this->assertInstanceOf(SoundEvent::class, $bounceEvent);
        $this->assertInstanceOf(SoundEvent::class, $landEvent);
        $this->assertPositionNotSame(new Point(), $landEvent->position);
        $this->assertGreaterThan(1, $bounceEvent->position->z);
        $this->assertPositionNotSame($bounceEvent->position, $landEvent->position);
        $this->assertSame($y, $bounceEvent->position->y);
        $this->assertSame($y, $landEvent->position->y);
        $this->assertPositionSame(new Point(1374, $y, 416), $landEvent->position);
    }

    public function testThrow3(): void
    {
        $landEvent = null;
        $bounceEvent = null;
        $game = $this->createTestGame();
        $game->onEvents(function (array $events) use (&$landEvent, &$bounceEvent): void {
            foreach ($events as $event) {
                if (!($event instanceof SoundEvent)) {
                    continue;
                }
                if ($event->type === SoundType::GRENADE_LAND) {
                    $this->assertTrue(is_null($landEvent), 'Only one landEvent please');
                    $landEvent = $event;
                }
                if (!$bounceEvent && $event->type === SoundType::GRENADE_BOUNCE) {
                    $bounceEvent = $event;
                }
            }
        });
        $this->playPlayer($game, [
            fn(Player $p) => $p->crouch(),
            fn(Player $p) => $p->getSight()->look(45, -8),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_DECOY)),
            fn(Player $p) => $this->assertTrue($p->equip(InventorySlot::SLOT_GRENADE_DECOY)),
            $this->waitNTicks(Decoy::equipReadyTimeMs),
            fn(Player $p) => $p->jump(),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            $this->waitNTicks(1200),
            $this->endGame(),
        ]);

        $y = Decoy::boundingRadius;
        $this->assertFalse($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_GRENADE_DECOY->value));
        $this->assertInstanceOf(SoundEvent::class, $bounceEvent);
        $this->assertInstanceOf(SoundEvent::class, $landEvent);
        $this->assertPositionNotSame(new Point(), $landEvent->position);
        $this->assertGreaterThan(1, $bounceEvent->position->z);
        $this->assertPositionNotSame($bounceEvent->position, $landEvent->position);
        $this->assertGreaterThan($bounceEvent->position->z, $landEvent->position->z);
        $this->assertSame($y, $bounceEvent->position->y);
        $this->assertSame($y, $landEvent->position->y);
        $this->assertPositionSame(new Point(470, $y, 470), $landEvent->position);
    }

    public function testThrowFlashBang(): void
    {
        $landEvent = null;
        $bounceEvent = null;
        $game = $this->createTestGame();
        $game->onEvents(function (array $events) use (&$landEvent, &$bounceEvent): void {
            foreach ($events as $event) {
                if (!($event instanceof SoundEvent)) {
                    continue;
                }
                if ($event->type === SoundType::GRENADE_LAND) {
                    $this->assertTrue(is_null($landEvent), 'Only one landEvent please');
                    $landEvent = $event;
                }
                if (!$bounceEvent && $event->type === SoundType::GRENADE_BOUNCE) {
                    $bounceEvent = $event;
                }
            }
        });
        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(45, 89),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_FLASH)),
            fn(Player $p) => $this->assertTrue($p->equip(InventorySlot::SLOT_GRENADE_FLASH)),
            $this->waitNTicks(Flashbang::equipReadyTimeMs),
            fn(Player $p) => $p->jump(),
            function (Player $p) {
                $p->moveForward();
                $this->assertNotNull($p->attack());
            },
            $this->waitNTicks(2200),
            $this->endGame(),
        ]);

        $y = Flashbang::boundingRadius;
        $this->assertFalse($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_GRENADE_DECOY->value));
        $this->assertNull($bounceEvent);
        $this->assertInstanceOf(SoundEvent::class, $landEvent);
        $this->assertGreaterThan($y, $landEvent->position->y);
        $this->assertPositionSame(new Point(158, 470, 158), $landEvent->position);
    }

    public function testFullVerticalThrow(): void
    {
        $this->_testFullVerticalThrow(19);
        $this->_testFullVerticalThrow(27);
        $this->_testFullVerticalThrow(51);
        $this->_testFullVerticalThrow(rand(10, Setting::playerHeadHeightStand() / 2));
    }

    private function _testFullVerticalThrow(int $floorY): void
    {
        $landEvent = null;
        $bounceEvents = [];
        $game = $this->createTestGame();
        $game->getWorld()->addFloor(new Floor(new Point(-50, $floorY, -50), 500, 2000));
        $game->onEvents(function (array $events) use (&$landEvent, &$bounceEvents): void {
            foreach ($events as $event) {
                if (!($event instanceof SoundEvent)) {
                    continue;
                }
                if ($event->type === SoundType::GRENADE_LAND) {
                    $this->assertTrue(is_null($landEvent), 'Only one landEvent please');
                    $landEvent = $event;
                }
                if ($event->type === SoundType::GRENADE_BOUNCE) {
                    $bounceEvents[] = $event;
                }
            }
        });
        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(2, 90),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_DECOY)),
            fn(Player $p) => $this->assertTrue($p->equip(InventorySlot::SLOT_GRENADE_DECOY)),
            $this->waitNTicks(Decoy::equipReadyTimeMs),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            $this->waitNTicks(2500),
            $this->endGame(),
        ]);

        $this->assertFalse($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_GRENADE_DECOY->value));
        $this->assertCount(4, $bounceEvents);
        $bounceEvent = array_pop($bounceEvents);
        $this->assertInstanceOf(SoundEvent::class, $bounceEvent);
        $this->assertInstanceOf(SoundEvent::class, $landEvent);
        $this->assertPositionSame(new Point(0, $floorY + Decoy::boundingRadius, 0), $landEvent->position, "FloorY: {$floorY}");
        $this->assertPositionSame($bounceEvent->position, $landEvent->position);
    }

    public function testThrowAgainstWall(): void
    {
        $landEvent = null;
        $bounceEvent = null;
        $game = $this->createTestGame();
        $game->getPlayer(1)->setPosition(new Point(300, 0, 200));
        $game->onEvents(function (array $events) use (&$landEvent, &$bounceEvent): void {
            foreach ($events as $event) {
                if (!($event instanceof SoundEvent)) {
                    continue;
                }
                if ($event->type === SoundType::GRENADE_LAND) {
                    $this->assertTrue(is_null($landEvent), 'Only one landEvent please');
                    $landEvent = $event;
                }
                if (!$bounceEvent && $event->type === SoundType::GRENADE_BOUNCE) {
                    $bounceEvent = $event;
                }
            }
        });
        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(-80, 20),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_DECOY)),
            fn(Player $p) => $this->assertTrue($p->equip(InventorySlot::SLOT_GRENADE_DECOY)),
            $this->waitNTicks(Decoy::equipReadyTimeMs),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            $this->waitNTicks(1100),
            $this->endGame(),
        ]);

        $this->assertInstanceOf(SoundEvent::class, $bounceEvent);
        $this->assertSame(-1 + Decoy::boundingRadius, $bounceEvent->position->x);
        $this->assertGreaterThan(Decoy::boundingRadius + 10, $bounceEvent->position->y);
        $this->assertInstanceOf(SoundEvent::class, $landEvent);
        $this->assertSame(Decoy::boundingRadius, $landEvent->position->y);
        $pp = $game->getPlayer(1)->getPositionClone();
        $this->assertGreaterThan($pp->x, $landEvent->position->x);
        $this->assertGreaterThan($pp->z, $landEvent->position->z);
        $this->assertPositionSame(new Point(703, Decoy::boundingRadius, 374), $landEvent->position);
    }

    public function testMultiThrow(): void
    {
        $game = $this->createNoPauseGame();
        $this->playPlayer($game, [
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_DECOY)),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_FLASH)),
            fn(Player $p) => $this->assertTrue($p->equip(InventorySlot::SLOT_GRENADE_DECOY)),
            fn(Player $p) => $this->assertFalse($game->getWorld()->canAttack($p)),
            $this->waitNTicks(Decoy::equipReadyTimeMs),
            fn(Player $p) => $this->assertTrue($game->getWorld()->canAttack($p)),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            fn(Player $p) => $this->assertFalse($game->getWorld()->canAttack($p)),
            fn(Player $p) => $this->assertInstanceOf(Flashbang::class, $p->getEquippedItem()),
            fn(Player $p) => $this->assertFalse($p->getEquippedItem()->isEquipped()),
            fn(Player $p) => $this->assertFalse($game->getWorld()->canAttack($p)),
            $this->waitNTicks(Flashbang::equipReadyTimeMs),
            fn(Player $p) => $this->assertTrue($game->getWorld()->canAttack($p)),
            fn(Player $p) => $this->assertTrue($p->getEquippedItem()->isEquipped()),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            fn(Player $p) => $this->assertFalse($game->getWorld()->canAttack($p)),
            $this->endGame(),
        ]);

        $this->assertCount(3, $game->getPlayer(1)->getInventory()->getItems());
        $this->assertTrue($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_KNIFE->value));
        $this->assertTrue($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_SECONDARY->value));
        $this->assertTrue($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_BOMB->value));
    }

    public function testExtremePosition(): void
    {
        $game = $this->createNoPauseGame();
        $floor = new Floor(new Point(500, 600, 500), 10, 10);
        $game->getWorld()->addFloor($floor);

        $test = new \stdClass();
        $test->goingUp = true;
        $test->mid = false;
        $test->lastPosition = null;
        $test->bounceCount = 0;
        $test->bounceX = 0;
        $test->airCount = 0;
        $test->land = null;
        $game->onEvents(function (array $events) use (&$test): void {
            foreach ($events as $event) {
                if (false === ($event instanceof SoundEvent)) {
                    continue;
                }

                if ($event->type === SoundType::GRENADE_LAND) {
                    $this->assertNull($test->land);
                    $test->land = $event->position;
                    return;
                }
                if ($event->position->y < 2 + Decoy::boundingRadius) {
                    return;
                }
                if ($event->type === SoundType::GRENADE_BOUNCE) {
                    $test->bounceCount++;
                    $test->bounceX = $event->position->x;
                    continue;
                }
                if ($event->type === SoundType::GRENADE_AIR) {
                    $test->airCount++;
                    continue;
                }

                if ($test->lastPosition === null) {
                    $test->lastPosition = $event->position->clone();
                    continue;
                }

                if (!$test->mid && $test->lastPosition->y === $event->position->y) {
                    $this->assertFalse($test->mid);
                    $test->mid = true;
                    $this->assertTrue($test->goingUp);
                    $test->goingUp = false;
                    $test->lastPosition->setFrom($event->position);
                    continue;
                }

                if ($test->mid && $test->lastPosition->y === $event->position->y) {
                    continue;
                }

                $msg = "last: {$test->lastPosition->y}, actual: {$event->position->y}";
                if ($test->goingUp) {
                    $this->assertTrue($test->lastPosition->y < $event->position->y, $msg);
                } else {
                    $this->assertTrue($test->lastPosition->y > $event->position->y, $msg);
                }
                $test->lastPosition->setFrom($event->position);
            }
        });

        $this->playPlayer($game, [
            fn(Player $p) => $p->setPosition($floor->getStart()),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_DECOY)),
            fn(Player $p) => $p->getSight()->look(-22, 30),
            fn(Player $p) => $this->assertGreaterThan(10, $p->getSight()->getRotationVertical()),
            $this->waitNTicks(Decoy::equipReadyTimeMs),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            $this->waitNTicks(2500),
            $this->endGame(),
        ]);

        $this->assertNotNull($test->land);
        $this->assertSame(Decoy::boundingRadius, $test->land->y);
        $this->assertSame(1, $test->bounceCount);
        $this->assertSame(-1 + Decoy::boundingRadius, $test->bounceX);
        $this->assertSame(114, $test->airCount);
        $this->assertFalse($test->goingUp);
    }


}
