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
        $this->assertPositionSame(new Point(221, $floorY, 1022), $landEvent->position);
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
            $this->waitNTicks(1500),
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
        $this->assertPositionSame(new Point(1168, $y, 210), $landEvent->position);
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
        $this->assertPositionSame(new Point(559, Decoy::boundingRadius, 347), $landEvent->position);
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


}
