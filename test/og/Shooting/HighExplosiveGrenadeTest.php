<?php

namespace Test\Shooting;

use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Setting;
use cs\Core\Wall;
use cs\Enum\BuyMenuItem;
use cs\Enum\Color;
use cs\Equipment\HighExplosive;
use cs\Weapon\PistolGlock;
use Test\BaseTestCase;

class HighExplosiveGrenadeTest extends BaseTestCase
{

    public function testOwnDamage(): void
    {
        $game = $this->createNoPauseGame();
        $game->getPlayer(1)->setPosition(new Point(500,0, 500));
        $health = $game->getPlayer(1)->getHealth();

        $this->playPlayer($game, [
            fn(Player $p) => $p->getInventory()->earnMoney(1000),
            fn(Player $p) => $p->buyItem(BuyMenuItem::KEVLAR_BODY),
            fn(Player $p) => $p->getSight()->look(0, -90),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_HE)),
            fn(Player $p) => $this->assertInstanceOf(HighExplosive::class, $p->getEquippedItem()),
            $this->waitNTicks(HighExplosive::equipReadyTimeMs),
            fn(Player $p) => $this->assertSame($health, $p->getHealth()),
            fn(Player $p) => $this->assertNotNull($p->attackSecondary()),
            $this->waitXTicks(100),
            fn(Player $p) => $this->assertLessThan($health, $p->getHealth()),
            $this->endGame(),
        ]);
    }

    public function testDamageEnemy(): void
    {
        $game = $this->createNoPauseGame();
        $enemy = new Player(2, Color::ORANGE, false);
        $game->addPlayer($enemy);
        $enemy->setPosition(new Point(430, 0, 500));
        $enemyHealth = $enemy->getHealth();

        $game->getWorld()->addWall(new Wall(new Point(0, 0, 600), true, 800));
        $game->getWorld()->addWall(new Wall(new Point(560, 0, 0), false, 800));

        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(40, -10),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_HE)),
            fn(Player $p) => $this->assertSame(500, $p->getMoney()),
            fn(Player $p) => $this->assertInstanceOf(HighExplosive::class, $p->getEquippedItem()),
            $this->waitNTicks(HighExplosive::equipReadyTimeMs),
            fn(Player $p) => $this->assertSame($enemyHealth, $enemy->getHealth()),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            $this->waitXTicks(100),
            fn(Player $p) => $this->assertLessThan($enemyHealth, $enemy->getHealth()),
            fn(Player $p) => $this->assertSame(500, $p->getMoney()),
            $this->endGame(),
        ]);
    }

    public function testKillEnemy(): void
    {
        $game = $this->createNoPauseGame();
        $enemy = new Player(2, Color::ORANGE, false);
        $game->addPlayer($enemy);
        $enemy->setPosition(new Point(430, 0, 500));

        $game->getWorld()->addWall(new Wall(new Point(0, 0, 600), true, 800));
        $game->getWorld()->addWall(new Wall(new Point(560, 0, 0), false, 800));

        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(40, -2),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs),
            fn(Player $p) => $this->assertPlayerHit($p->attack()),
            fn(Player $p) => $p->getSight()->look(40, -10),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_HE)),
            fn(Player $p) => $this->assertSame(500, $p->getMoney()),
            fn(Player $p) => $this->assertInstanceOf(HighExplosive::class, $p->getEquippedItem()),
            $this->waitNTicks(HighExplosive::equipReadyTimeMs),
            fn(Player $p) => $this->assertSame(71, $enemy->getHealth()),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            $this->waitXTicks(100),
        ]);

        $this->assertSame(2, $game->getRoundNumber());
        $this->assertSame(500 + 300, $game->getPlayer(1)->getMoney());
    }

    public function testWallBlockDamage(): void
    {
        $game = $this->createNoPauseGame();
        $game->getWorld()->addWall(new Wall(new Point(0, 0, 400), true, 800, 1000));
        $game->getPlayer(1)->setPosition(new Point(100, 0, 500));

        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(0, -90),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_HE)),
            $this->waitNTicks(HighExplosive::equipReadyTimeMs),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            fn(Player $p) => $p->setPosition(new Point(100, 0, 300)),
            $this->waitNTicks(800),
            fn(Player $p) => $this->assertSame(100, $p->getHealth()),
            $this->endGame(),
        ]);
    }

    public function testSmallWallBlockDamagePartially(): void
    {
        $noWallHealth = null;

        $game = $this->createNoPauseGame();
        $game->getPlayer(1)->setPosition(new Point(100, 0, 500));

        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(0, -90),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_HE)),
            $this->waitNTicks(HighExplosive::equipReadyTimeMs),
            fn(Player $p) => $this->assertNotNull($p->attackSecondary()),
            fn(Player $p) => $p->setPosition(new Point(100, 0, 300)),
            $this->waitNTicks(2000),
            function (Player $p) use (&$noWallHealth) {
                $this->assertLessThan(100, $p->getHealth());
                $noWallHealth = $p->getHealth();
            },
            $this->endGame(),
        ]);

        ////

        $game = $this->createNoPauseGame();
        $game->getWorld()->addWall(new Wall(new Point(0, 0, 400), true, 800, Setting::playerHeadHeightCrouch() / 2));
        $game->getPlayer(1)->setPosition(new Point(100, 0, 500));

        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(0, -90),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_HE)),
            $this->waitNTicks(HighExplosive::equipReadyTimeMs),
            fn(Player $p) => $this->assertNotNull($p->attackSecondary()),
            fn(Player $p) => $p->setPosition(new Point(100, 0, 300)),
            $this->waitNTicks(2000),
            function (Player $p) use (&$noWallHealth) {
                $this->assertGreaterThan($noWallHealth, $p->getHealth());
                $this->assertLessThan(100, $p->getHealth());
            },
            $this->endGame(),
        ]);
    }

    public function testDistanceLowerDamage(): void
    {
        $game = $this->createNoPauseGame();
        $game->addPlayer(new Player(2, Color::ORANGE, false));
        $game->getPlayer(2)->setPosition(new Point(600, 0, 600));
        $game->addPlayer(new Player(3, Color::ORANGE, false));
        $game->getPlayer(3)->setPosition(new Point(700, 0, 700));
        $game->getPlayer(1)->setPosition(new Point(500, 0, 500));

        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(45, -89),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_HE)),
            fn(Player $p) => $this->assertInstanceOf(HighExplosive::class, $p->getEquippedItem()),
            $this->waitNTicks(HighExplosive::equipReadyTimeMs),
            fn(Player $p) => $this->assertNotNull($p->attackSecondary()),
            $this->waitXTicks(100),
            $this->endGame(),
        ]);

        $health = $game->getPlayer(1)->getHealth();
        $this->assertSame(1, $game->getRoundNumber());
        $this->assertCount(3, $game->getAlivePlayers());
        $this->assertLessThan(100, $health);
        $this->assertLessThan(100,  $game->getPlayer(2)->getHealth());
        $this->assertLessThan(100,  $game->getPlayer(3)->getHealth());
        $this->assertGreaterThan($health,  $game->getPlayer(2)->getHealth());
        $health = $game->getPlayer(2)->getHealth();
        $this->assertGreaterThan($health,  $game->getPlayer(3)->getHealth());
    }

}
