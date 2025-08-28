<?php

namespace Test\Shooting;

use cs\Core\Box;
use cs\Core\GameProperty;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Util;
use cs\Enum\BuyMenuItem;
use cs\Enum\Color;
use cs\Enum\GameOverReason;
use cs\Enum\SoundType;
use cs\Event\SoundEvent;
use cs\Weapon\Knife;
use cs\Weapon\PistolGlock;
use cs\Weapon\PistolP250;
use cs\Weapon\RifleAk;
use Test\BaseTestCase;

class ShootTest extends BaseTestCase
{

    public function testOneTapAmmoMagazine(): void
    {
        $playerCommands = [
            fn(Player $p) => $p->buyItem(BuyMenuItem::RIFLE_AK),
            $this->waitNTicks(RifleAk::equipReadyTimeMs),
            fn(Player $p) => $p->getSight()->lookVertical(-91),
            fn(Player $p) => $this->assertNull($p->attackSecondary()),
            fn(Player $p) => $this->assertSame(0, $p->getEquippedItem()->getScopeLevel()),
            fn(Player $p) => $this->assertPlayerNotHit($p->attack()),
        ];

        $game = $this->simulateGame($playerCommands, [GameProperty::START_MONEY => 16000]);
        $ak = $game->getPlayer(1)->getEquippedItem();
        $this->assertInstanceOf(RifleAk::class, $ak);
        $this->assertSame(29, $ak->getAmmo());
    }

    public function testEmptyClipSoundEvent(): void
    {
        $noAmmoTickId = null;
        $game = $this->createTestGame();
        $game->getPlayer(1)->equipSecondaryWeapon();

        $game->onTick(function (GameState $state) use ($game, &$noAmmoTickId): void {
            if ($noAmmoTickId === -1) {
                $game->quit(GameOverReason::TIE);
                return;
            }
            $p = $state->getPlayer(1);
            if (!$p->isAlive()) {
                return;
            }

            $p->attack();
            $glock = $p->getEquippedItem();
            assert($glock instanceof PistolGlock, get_class($glock));
            if ($glock->getAmmo() === 0) {
                $noAmmoTickId = $state->getTickId();
            }
        });
        $autoReloadFired = false;
        $game->onEvents(function (array $events) use (&$noAmmoTickId, &$autoReloadFired): void {
            foreach ($events as $event) {
                if (!($event instanceof SoundEvent)) {
                    continue;
                }

                if ($event->type === SoundType::ITEM_RELOAD) {
                    $this->assertFalse($autoReloadFired);
                    $autoReloadFired = true;
                }
                if ($event->type === SoundType::ATTACK_NO_AMMO) {
                    $this->assertGreaterThan(0, $noAmmoTickId);
                    $noAmmoTickId = -1;
                }
            }
        });

        $game->start();
        $this->assertSame(-1, $noAmmoTickId);
        $this->assertTrue($autoReloadFired);
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
            fn(Player $p) => $this->assertNull($p->attack()),
            function (Player $p): void {
                $ak = $p->getEquippedItem();
                $this->assertInstanceOf(RifleAk::class, $ak);
                $this->assertSame(RifleAk::magazineCapacity, $ak->getAmmo());
                $this->assertSame(RifleAk::reserveAmmo, $ak->getAmmoReserve());
            },
            $this->waitNTicks(RifleAk::equipReadyTimeMs) - 1,
            fn(Player $p) => $this->assertNotNull($p->attack()),
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
            },
            $this->waitNTicks(RifleAk::fireRateMs),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            function (Player $p): void {
                $ak = $p->getEquippedItem();
                $this->assertInstanceOf(RifleAk::class, $ak);
                $this->assertSame(RifleAk::magazineCapacity - 1, $ak->getAmmo());
            },
            fn(Player $p) => $p->reload(),
            function (Player $p): void {
                $ak = $p->getEquippedItem();
                $this->assertInstanceOf(RifleAk::class, $ak);
                $this->assertSame(RifleAk::magazineCapacity - 1, $ak->getAmmo());
            },
            $this->waitNTicks(RifleAk::reloadTimeMs),
            function (Player $p): void {
                $ak = $p->getEquippedItem();
                $this->assertInstanceOf(RifleAk::class, $ak);
                $this->assertSame(RifleAk::magazineCapacity, $ak->getAmmo());
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

    public function testTeamDamageIsLowerThanOpponent(): void
    {
        $game = $this->createTestGame();
        $game->addPlayer(new Player(2, Color::ORANGE, true));
        $game->addPlayer(new Player(3, Color::BLUE, false));
        $game->getWorld()->addBox(new Box(new Point(), 1000, 1000, 1000));

        $this->playPlayer($game, [
            fn(Player $p) => $p->setPosition(new Point(500, 0, 500)),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs),
            fn(Player $p) => $game->getPlayer(2)->setPosition(new Point(500, 0, 300)),
            fn(Player $p) => $game->getPlayer(3)->setPosition(new Point(500, 0, 700)),
            fn(Player $p) => $p->getSight()->look(0, -10),
            fn(Player $p) => $this->assertPlayerHit($p->attack()),
            $this->waitNTicks(PistolGlock::fireRateMs),
            fn(Player $p) => $p->getSight()->look(180, -10),
            fn(Player $p) => $this->assertPlayerHit($p->attack()),
            function () use ($game) {
                $this->assertLessThan(100, $game->getPlayer(3)->getHealth());
                $this->assertLessThan($game->getPlayer(2)->getHealth(), $game->getPlayer(3)->getHealth());
            },
            fn(Player $p) => $p->getSight()->look(180, 0),
            $this->waitNTicks(PistolGlock::fireRateMs),
            fn(Player $p) => $this->assertPlayerHit($p->attack()),
            $this->waitNTicks(PistolGlock::fireRateMs),
            fn(Player $p) => $this->assertPlayerHit($p->attack()),
            $this->endGame(),
        ]);

        $this->assertCount(2, $game->getAlivePlayers());
        $this->assertSame(-1, $game->getScore()->getPlayerStat(1)->getKills());
        $this->assertSame(500, $game->getPlayer(1)->getMoney());
    }

    public function testDamageLowOnRangeMaxDamage(): void
    {
        $game = $this->createTestGame();
        $game->addPlayer(new Player(2, Color::ORANGE, true));

        $bulletHitHeadShotsCount = 0;
        $game->onEvents(function (array $events) use (&$bulletHitHeadShotsCount): void {
            foreach ($events as $event) {
                if ($event instanceof SoundEvent && $event->type === SoundType::BULLET_HIT_HEADSHOT) {
                    $bulletHitHeadShotsCount++;
                }
            }
        });

        $this->playPlayer($game, [
            fn(Player $p) => $p->setPosition(new Point(500)),
            fn(Player $p) => $game->getPlayer(2)->setPosition(new Point(500, 0, PistolGlock::rangeMaxDamage + $p->getBoundingRadius())),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs),
            fn(Player $p) => $p->getSight()->look(0, 0),
            fn(Player $p) => $this->assertPlayerHit($p->attack()),
            $this->endGame(),
        ]);

        $this->assertSame(99, $game->getPlayer(2)->getHealth());
        $this->assertSame(1, $bulletHitHeadShotsCount);
        $this->assertSame(0, $game->getScore()->getPlayerStat(1)->getHeadshotKills());
    }

}
