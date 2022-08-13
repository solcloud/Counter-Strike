<?php

namespace Test\Shooting;

use cs\Core\GameProperty;
use cs\Core\Player;
use cs\Core\Util;
use cs\Enum\BuyMenuItem;
use cs\Weapon\Knife;
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
            fn(Player $p) => $p->getSight()->lookVertical(-90),
            fn(Player $p) => $p->attack(),
        ];

        $game = $this->simulateGame($playerCommands, [GameProperty::START_MONEY => 16000]);
        $ak = $game->getPlayer(1)->getEquippedItem();
        $this->assertInstanceOf(RifleAk::class, $ak);
        $this->assertSame(29, $ak->getAmmo());
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
