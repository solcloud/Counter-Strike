<?php

namespace Test\Shooting;

use cs\Core\GameProperty;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Util;
use cs\Enum\BuyMenuItem;
use cs\Enum\SoundType;
use cs\Event\SoundEvent;
use cs\Weapon\Knife;
use cs\Weapon\PistolGlock;
use cs\Weapon\PistolP250;
use cs\Weapon\RifleAk;
use Test\BaseTestCase;

class SimpleShootTest extends BaseTestCase
{

    public function testOneTapAmmoMagazine(): void
    {
        $playerCommands = [
            fn(Player $p) => $p->buyItem(BuyMenuItem::RIFLE_AK),
            $this->waitNTicks(RifleAk::equipReadyTimeMs),
            fn(Player $p) => $p->getSight()->lookVertical(-91),
            fn(Player $p) => $p->attack(),
        ];

        $game = $this->simulateGame($playerCommands, [GameProperty::START_MONEY => 16000]);
        $ak = $game->getPlayer(1)->getEquippedItem();
        $this->assertInstanceOf(RifleAk::class, $ak);
        $this->assertSame(29, $ak->getAmmo());
    }

    public function testVerticalShooting(): void
    {
        $playerCommands = [
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs),
            fn(Player $p) => $p->getSight()->look(45, -89),
            function (Player $p) {
                $result = $p->attack();
                $this->assertNotNull($result);
            },
            $this->endGame(),
        ];

        $game = $this->createNoPauseGame();
        $hitEvent = null;
        $game->onEvents(function (array $events) use (&$hitEvent) {
            foreach ($events as $event) {
                if ($event instanceof SoundEvent && $event->type === SoundType::BULLET_HIT) {
                    if ($hitEvent) {
                        $this->fail('Sound more than once?');
                    }
                    $hitEvent = $event;
                }
            }
        });

        $this->playPlayer($game, $playerCommands);
        $this->assertPositionSame(new Point(), $game->getPlayer(1)->getPositionClone());
        $this->assertInstanceOf(SoundEvent::class, $hitEvent);
        $this->assertSame(SoundType::BULLET_HIT, $hitEvent->type);
        $this->assertSame(0, $hitEvent->position->y);
        $this->assertGreaterThanOrEqual(1, $hitEvent->position->x);
        $this->assertGreaterThanOrEqual(1, $hitEvent->position->z);
        $this->assertLessThanOrEqual(2, $hitEvent->position->x);
        $this->assertLessThanOrEqual(2, $hitEvent->position->z);
    }

    public function testReloadOneBulletFromAk(): void
    {
        $playerCommands = [
            fn(Player $p) => $p->getSight()->lookVertical(-90),
            function (Player $p): void {
                $this->assertInstanceOf(Knife::class, $p->getEquippedItem());
            },
            fn(Player $p) => $p->buyItem(BuyMenuItem::RIFLE_AK),
            function (Player $p): void {
                $ak = $p->getEquippedItem();
                $this->assertInstanceOf(RifleAk::class, $ak);
                $this->assertSame(RifleAk::magazineCapacity, $ak->getAmmo());
                $this->assertSame(RifleAk::reserveAmmo, $ak->getAmmoReserve());
            },
            fn(Player $p) => $p->attack(),
            function (Player $p): void {
                $ak = $p->getEquippedItem();
                $this->assertInstanceOf(RifleAk::class, $ak);
                $this->assertSame(RifleAk::magazineCapacity, $ak->getAmmo());
                $this->assertSame(RifleAk::reserveAmmo, $ak->getAmmoReserve());
            },
            $this->waitNTicks(RifleAk::equipReadyTimeMs) - 1,
            fn(Player $p) => $p->attack(),
            function (Player $p): void {
                $ak = $p->getEquippedItem();
                $this->assertInstanceOf(RifleAk::class, $ak);
                $this->assertSame(RifleAk::magazineCapacity - 1, $ak->getAmmo());
                $this->assertSame(RifleAk::reserveAmmo, $ak->getAmmoReserve());
            },
            fn(Player $p) => $p->reload(),
            $this->waitNTicks(RifleAk::reloadTimeMs) - 1,
            function (Player $p): void {
                $this->assertNull($p->attack());
                $ak = $p->getEquippedItem();
                $this->assertInstanceOf(RifleAk::class, $ak);
                $this->assertSame(RifleAk::magazineCapacity - 1, $ak->getAmmo());
                $this->assertSame(RifleAk::reserveAmmo, $ak->getAmmoReserve());
            },
            function (Player $p): void {
                $ak = $p->getEquippedItem();
                $this->assertInstanceOf(RifleAk::class, $ak);
                $this->assertSame(RifleAk::magazineCapacity, $ak->getAmmo());
                $this->assertSame(RifleAk::reserveAmmo - 1, $ak->getAmmoReserve());
                $this->assertNotNull($p->attack());
            },
        ];

        $this->simulateGame($playerCommands, [GameProperty::START_MONEY => 16000]);
    }

    public function testCanAttackAfterDrop(): void
    {
        $game = $this->createNoPauseGame();
        $game->getPlayer(1)->getInventory()->earnMoney(16000);

        $playerCommands = [
            fn(Player $p) => $this->assertFalse($game->getWorld()->canAttack($p)),
            fn(Player $p) => $this->assertInstanceOf(Knife::class, $p->getEquippedItem()),
            $this->waitNTicks(Knife::equipReadyTimeMs) - 1,
            fn(Player $p) => $this->assertTrue($game->getWorld()->canAttack($p)),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::RIFLE_AK)),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            fn(Player $p) => $p->equipPrimaryWeapon(),
            fn(Player $p) => $this->assertInstanceOf(RifleAk::class, $p->getEquippedItem()),
            fn(Player $p) => $this->assertFalse($game->getWorld()->canAttack($p)),
            fn(Player $p) => $this->assertNull($p->attack()),
            $this->waitNTicks(RifleAk::equipReadyTimeMs) - 1,
            fn(Player $p) => $this->assertTrue($game->getWorld()->canAttack($p)),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            fn(Player $p) => $this->assertFalse($game->getWorld()->canAttack($p)),
            fn(Player $p) => $this->assertInstanceOf(RifleAk::class, $p->dropEquippedItem()),
            fn(Player $p) => $this->assertInstanceOf(PistolGlock::class, $p->getEquippedItem()),
            fn(Player $p) => $this->assertFalse($game->getWorld()->canAttack($p)),
            fn(Player $p) => $this->assertNull($p->attack()),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs) - 1,
            fn(Player $p) => $this->assertTrue($game->getWorld()->canAttack($p)),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            fn(Player $p) => $this->assertInstanceOf(PistolGlock::class, $p->dropEquippedItem()),
            fn(Player $p) => $this->assertInstanceOf(Knife::class, $p->getEquippedItem()),
            fn(Player $p) => $this->assertFalse($game->getWorld()->canAttack($p)),
            fn(Player $p) => $this->assertNull($p->attack()),
            $this->waitNTicks(Knife::equipReadyTimeMs) - 1,
            fn(Player $p) => $this->assertTrue($game->getWorld()->canAttack($p)),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            $this->endGame(),
        ];

        $this->playPlayer($game, $playerCommands);
    }

    public function testFireRate(): void
    {
        $playerCommands = [
            fn(Player $p) => $p->buyItem(BuyMenuItem::PISTOL_P250),
            $this->waitNTicks(PistolP250::equipReadyTimeMs),
            fn(Player $p) => $p->getSight()->lookVertical(-90),
            fn(Player $p) => $p->attack(),
            function (Player $p) {
                $this->assertGreaterThan(Util::$TICK_RATE, PistolP250::fireRateMs);
                $gun = $p->getEquippedItem();
                $this->assertInstanceOf(PistolP250::class, $gun);
                $this->assertNull($p->attack());
                $this->assertNull($p->attack());
            },
            $this->waitNTicks(PistolP250::fireRateMs),
            fn(Player $p) => $p->attack(),
        ];

        $game = $this->simulateGame($playerCommands, [GameProperty::START_MONEY => 16000]);
        $gun = $game->getPlayer(1)->getEquippedItem();
        $this->assertInstanceOf(PistolP250::class, $gun);
        $this->assertSame($gun::magazineCapacity - 2, $gun->getAmmo());
    }

}
