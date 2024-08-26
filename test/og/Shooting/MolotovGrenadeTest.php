<?php

namespace Test\Shooting;

use cs\Core\Box;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Ramp;
use cs\Core\Setting;
use cs\Enum\BuyMenuItem;
use cs\Enum\Color;
use cs\Enum\InventorySlot;
use cs\Enum\RampDirection;
use cs\Equipment\Molotov;
use cs\Equipment\Smoke;
use Test\BaseTestCase;

class MolotovGrenadeTest extends BaseTestCase
{

    public function testOwnDamage(): void
    {
        $game = $this->createNoPauseGame(2);
        $game->getPlayer(1)->setPosition(new Point(500, 0, 500));
        $game->getWorld()->addBox(new Box(new Point(), 1000, 3000, 1000));
        $health = $game->getPlayer(1)->getHealth();

        $this->playPlayer($game, [
            fn(Player $p) => $p->getInventory()->earnMoney(1000),
            fn(Player $p) => $p->buyItem(BuyMenuItem::KEVLAR_BODY),
            fn(Player $p) => $p->getSight()->look(0, -90),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_MOLOTOV)),
            fn(Player $p) => $this->assertInstanceOf(Molotov::class, $p->getEquippedItem()),
            $this->waitNTicks(Molotov::equipReadyTimeMs),
            fn(Player $p) => $this->assertSame($health, $p->getHealth()),
            fn(Player $p) => $this->assertNotNull($p->attackSecondary()),
            fn(Player $p) => $this->assertSame(1, $game->getRoundNumber()),
            $this->waitNTicks(Molotov::MAX_TIME_MS),
            fn(Player $p) => $this->assertSame(2, $game->getRoundNumber(), 'Player should not survive molly'),
            fn(Player $p) => $this->assertSame(1, $game->getScore()->getPlayerStat($p->getId())->getDeaths()),
            $this->endGame(),
        ]);
    }

    public function testWallBlockFire(): void
    {
        $game = $this->createNoPauseGame();
        $box = new Box(new Point(0, 0, 300), 1000, 300, 10);
        $game->getWorld()->addBox($box);
        $game->getWorld()->addBox(new Box(new Point(), 1000, 3000, 1000));
        $health = $game->getPlayer(1)->getHealth();
        $game->getPlayer(1)->setPosition(new Point(100, 0, 50));
        $enemy = new Player(2, Color::BLUE, false);
        $game->addPlayer($enemy);
        $enemyHealth = $enemy->getHealth();
        $enemy->setPosition($box->getBase()->setX(100)->addZ($enemy->getBoundingRadius() + 20));

        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(0, -30),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_MOLOTOV)),
            $this->waitNTicks(Molotov::equipReadyTimeMs),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            $this->waitNTicks(2000),
            fn(Player $p) => $p->setPosition(new Point(999, 0, 999)),
            $this->waitNTicks(Molotov::MAX_TIME_MS),
            fn(Player $p) => $this->assertLessThan($health, $p->getHealth()),
            $this->endGame(),
        ]);

        $this->assertSame($enemyHealth, $enemy->getHealth());
    }

    public function testMolotovSpreadMaze(): void
    {
        $game = $this->createNoPauseGame(10);
        $game->getWorld()->addBox(new Box(new Point(0, 0, 500), 1000, 300, 10));
        $box = new Box(new Point(0, 0, 300), 250, 300, 10);
        $game->getWorld()->addBox($box);
        $game->getWorld()->addBox(new Box(new Point(0, 0, 100), 1000, 300, 10));
        $game->getWorld()->addBox(new Box(new Point(350, 0, 0), 100, 300, 700));
        $game->getWorld()->addBox(new Box(new Point(150, 0, 0), 10, Setting::playerObstacleOvercomeHeight() + 10, 300));
        $game->getPlayer(1)->setPosition(new Point(50, 0, 190));
        $enemy = new Player(2, Color::BLUE, false);
        $game->addPlayer($enemy);
        $enemy->setPosition($box->getBase()->setX(100)->addZ($enemy->getBoundingRadius() + 20));
        $game->getTestMap()->startPointForNavigationMesh->setFrom($enemy->getPositionClone());

        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(100, -20),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_MOLOTOV)),
            $this->waitNTicks(Molotov::equipReadyTimeMs),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            $this->waitNTicks(Molotov::MAX_TIME_MS),
            $this->endGame(),
        ]);

        $this->assertSame(2, $game->getRoundNumber());
        $this->assertTrue($game->getScore()->attackersIsWinning());
        $this->assertSame(1, $game->getScore()->getScoreAttackers());
        $this->assertSame(0, $game->getScore()->getScoreDefenders());

        $this->assertSame(1, $game->getScore()->getPlayerStat(1)->getKills());
        $this->assertSame(100, $game->getScore()->getPlayerStat(1)->getDamage());
        $this->assertSame(0, $game->getScore()->getPlayerStat($enemy->getId())->getKills());
        $this->assertSame(0, $game->getScore()->getPlayerStat($enemy->getId())->getDamage());

    }

    public function testStandOnTallWallAvoidFire(): void
    {
        $game = $this->createNoPauseGame(10);
        $game->getWorld()->addBox(new Box(new Point(500, 0, 500), 1, $game->getWorld()::GRENADE_NAVIGATION_MESH_OBJECT_HEIGHT + 1, 1));
        $game->getWorld()->addBox(new Box(new Point(), 1000, 3000, 1000));
        $health = $game->getPlayer(1)->getHealth();

        $this->playPlayer($game, [
            fn(Player $p) => $p->setPosition(new Point(500, 0, 460)),
            fn(Player $p) => $p->getSight()->look(0, -90),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_MOLOTOV)),
            fn(Player $p) => $this->assertInstanceOf(Molotov::class, $p->getEquippedItem()),
            $this->waitNTicks(Molotov::equipReadyTimeMs),
            fn(Player $p) => $this->assertSame($health, $p->getHealth()),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            $this->waitNTicks(300),
            function (Player $p) use (&$health, $game) {
                $this->assertLessThan($health, $p->getHealth());
                $p->setPosition(new Point(500, $game->getWorld()::GRENADE_NAVIGATION_MESH_OBJECT_HEIGHT + 10, 500));
                $health = $p->getHealth();
            },
            $this->waitNTicks(Molotov::MAX_TIME_MS),
            function (Player $p) use (&$health) {
                $this->assertSame($health, $p->getHealth());
            },
            $this->endGame(),
        ]);
    }

    public function testTunnelExpand(): void
    {
        $game = $this->createNoPauseGame();
        $game->getWorld()->addBox(new Box(new Point(), 1000, 3000, 1000));
        $grenadeTileSize = $game->getWorld()::GRENADE_NAVIGATION_MESH_TILE_SIZE;
        $game->getWorld()->addBox(new Box(new Point(0, 0, 500), $grenadeTileSize, 500, 2));
        $game->getWorld()->addBox(new Box(new Point(0, 0, 500 - ($grenadeTileSize * 4)), $grenadeTileSize, 500, 2));
        $game->getWorld()->addBox(new Box(new Point($grenadeTileSize, 0, 500 - ($grenadeTileSize * 1)), 1000 - (3 * $grenadeTileSize), 500, 2));
        $game->getWorld()->addBox(new Box(new Point($grenadeTileSize, 0, 500 - ($grenadeTileSize * 3)), 1000 - (3 * $grenadeTileSize), 500, 2));
        $game->getWorld()->addBox(new Box(new Point($grenadeTileSize, 0, 500 - $grenadeTileSize), 2, 500, $grenadeTileSize));
        $game->getWorld()->addBox(new Box(new Point($grenadeTileSize, 0, 500 - ($grenadeTileSize * 4)), 2, 500, $grenadeTileSize));
        $game->addPlayer(new Player(2, Color::ORANGE, false));
        $game->addPlayer(new Player(3, Color::BLUE, false));
        $game->addPlayer(new Player(4, Color::BLUE, false));
        $br = $game->getPlayer(1)->getBoundingRadius() + 1;
        $game->getPlayer(2)->setPosition(new Point(700, 0, 500 - ($grenadeTileSize * 2)));
        $game->getPlayer(3)->setPosition((new Point(100, 0, 500 - $grenadeTileSize))->addZ($br));
        $game->getPlayer(4)->setPosition((new Point(100, 0, 500 - ($grenadeTileSize * 3)))->addZ(-$br));

        $this->playPlayer($game, [
            fn(Player $p) => $p->setPosition(new Point((int)ceil($grenadeTileSize / 2) + 1, 0, 500 - ($grenadeTileSize * 2))),
            fn(Player $p) => $p->crouch(),
            fn(Player $p) => $p->getSight()->look(0, -90),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_MOLOTOV)),
            $this->waitNTicks(Molotov::equipReadyTimeMs),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            fn(Player $p) => $p->setPosition($p->getPositionClone()->addX(1000 + $p->getBoundingRadius())),
            fn(Player $p) => $this->assertSame(100, $p->getHealth()),
            $this->waitNTicks(Molotov::MAX_TIME_MS),
            $this->endGame(),
        ]);

        $this->assertLessThan(100, $game->getPlayer(2)->getHealth());
        $this->assertSame(100 - $game->getPlayer(2)->getHealth(), $game->getScore()->getPlayerStat(1)->getDamage());
        $this->assertSame(1, $game->getScore()->getPlayerStat(2)->getDeaths());
        $this->assertSame(0, $game->getScore()->getPlayerStat(3)->getDeaths());
        $this->assertSame(0, $game->getScore()->getPlayerStat(4)->getDeaths());
        $this->assertSame(0, $game->getScore()->getPlayerStat(1)->getDeaths());
    }

    public function testFlameClimbStairs(): void
    {
        $game = $this->createNoPauseGame();
        $game->getWorld()->addBox(new Box(new Point(), 1000, 3000, 1000));
        $grenadeTileSize = $game->getWorld()::GRENADE_NAVIGATION_MESH_TILE_SIZE;
        $game->getWorld()->addBox(new Box(new Point($grenadeTileSize * 3, 0, 300), 1000, 3000, 1000));
        $game->getWorld()->addRamp(new Ramp(new Point(-1, 0, 300), RampDirection::GROW_TO_POSITIVE_Z, 100, $grenadeTileSize * 10));

        $this->playPlayer($game, [
            fn(Player $p) => $p->setPosition(new Point($grenadeTileSize * 2, 0, 200)),
            fn(Player $p) => $p->getSight()->look(0, -90),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_MOLOTOV)),
            $this->waitNTicks(Molotov::equipReadyTimeMs),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            fn(Player $p) => $p->setPosition($p->getPositionClone()->addPart(0, 400, 300)),
            fn(Player $p) => $this->assertSame(100, $p->getHealth()),
            fn(Player $p) => $this->assertSame(100, $p->getHealth()),
            $this->waitNTicks(Molotov::MAX_TIME_MS),
        ]);

        $this->assertSame(2, $game->getRoundNumber());
    }

    public function testFlameDoNotLikeSmoke(): void
    {
        $game = $this->createNoPauseGame();
        $game->getWorld()->addBox(new Box(new Point(), 1000, 2000, 1000));
        $health = 100;

        $this->playPlayer($game, [
            fn(Player $p) => $p->setPosition(new Point(500, 0, 500)),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_MOLOTOV)),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_SMOKE)),
            $this->waitNTicks(Smoke::equipReadyTimeMs),
            fn(Player $p) => $p->getSight()->look(0, 90),
            fn(Player $p) => $this->assertNotNull($p->attackSecondary()),
            fn(Player $p) => $this->assertTrue($p->equip(InventorySlot::SLOT_GRENADE_MOLOTOV)),
            $this->waitNTicks(Molotov::equipReadyTimeMs),
            fn(Player $p) => $p->getSight()->look(0, -90),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            $this->waitNTicks(500),
            function (Player $p) use (&$health) {
                $this->assertLessThan($health, $p->getHealth());
                $health = $p->getHealth();
            },
            $this->waitNTicks(Molotov::MAX_TIME_MS),
            $this->endGame(),
        ]);

        $this->assertSame(1, $game->getRoundNumber());
        $this->assertSame($health, $game->getPlayer(1)->getHealth());
        $this->assertGreaterThan(72, $game->getPlayer(1)->getHealth());
    }

    public function testSmokeExtinguishFlames(): void
    {
        $game = $this->createNoPauseGame();
        $game->getWorld()->addBox(new Box(new Point(), 1000, 2000, 1000));
        $health = 100;

        $this->playPlayer($game, [
            fn(Player $p) => $p->setPosition(new Point(500, 0, 500)),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_MOLOTOV)),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_SMOKE)),
            $this->waitNTicks(Smoke::equipReadyTimeMs),
            fn(Player $p) => $p->getSight()->look(0, -90),
            fn(Player $p) => $this->assertNotNull($p->attackSecondary()),
            fn(Player $p) => $this->assertTrue($p->equip(InventorySlot::SLOT_GRENADE_MOLOTOV)),
            $this->waitNTicks(Molotov::equipReadyTimeMs),
            $this->waitNTicks((int)ceil(Smoke::MAX_TIME_MS / 3)),
            fn(Player $p) => $p->getSight()->look(0, -90),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            fn(Player $p) => $this->assertSame($health, $p->getHealth()),
            $this->waitNTicks(500),
            fn(Player $p) => $this->assertSame($health, $p->getHealth()),
            $this->waitNTicks(Molotov::MAX_TIME_MS),
            $this->endGame(),
        ]);

        $this->assertSame(1, $game->getRoundNumber());
        $this->assertSame($health, $game->getPlayer(1)->getHealth());
    }

}
