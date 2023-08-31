<?php

namespace Test\World;

use cs\Core\Box;
use cs\Core\Floor;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Point2D;
use cs\Core\Ramp;
use cs\Core\Setting;
use cs\Core\Wall;
use cs\Core\World;
use cs\Enum\Color;
use cs\Map\TestMap;
use Test\BaseTestCase;

class WorldTest extends BaseTestCase
{

    public function testFindFloor(): void
    {
        $game = $this->createGame();
        $world = new World($game);
        $world->addFloor(new Floor(new Point(10, 1, 0), 1, 1));

        $this->assertNull($world->findFloor(new Point(0, 2, 0), 999));
        $this->assertNull($world->findFloor(new Point(0, 1, 0), 1));
        $this->assertNull($world->findFloor(new Point(9, 1, 0), 0));
        $this->assertNull($world->findFloor(new Point(7, 1, 0), 2));
        $this->assertNull($world->findFloor(new Point(12, 1, 0), 0));
        $this->assertNull($world->findFloor(new Point(14, 1, 0), 2));
        $this->assertNotNull($world->findFloor(new Point(0, 1, 0), 10));
        $this->assertNotNull($world->findFloor(new Point(0, 1, 0), 12));
        $this->assertNotNull($world->findFloor(new Point(8, 1, 0), 2));
        $this->assertNotNull($world->findFloor(new Point(11, 1, 0), 2));
        $this->assertNotNull($world->findFloor(new Point(12, 1, 0), 2));
        $this->assertNull($world->findFloor(new Point(15, 1, 0), 3));
    }

    public function testCanBeSeen(): void
    {
        $game = $this->createGame();
        $player = $game->getPlayer(1);
        $this->assertFalse($game->getWorld()->canBeSeen($player, new Point(999, 999, 999), 10, 200));
        $this->assertFalse($game->getWorld()->canBeSeen(
            $player, $player->getPositionClone()->addY($player->getSightHeight())->addZ(-20), 10, 999)
        );
        $this->assertTrue($game->getWorld()->canBeSeen(
            $player, $player->getPositionClone()->addY($player->getSightHeight())->addZ(20), 10, 999)
        );
        $this->assertTrue($game->getWorld()->canBeSeen(
            $player, $player->getPositionClone()->addY($player->getSightHeight())->addZ(100), 10, 100)
        );
        $this->assertFalse($game->getWorld()->canBeSeen(
            $player, $player->getSightPositionClone()->addZ(101), 10, 100)
        );

        $game->getWorld()->addWall(new Wall((new Point())->addPart(-20, -20, 20), true, 200));
        $this->assertFalse($game->getWorld()->canBeSeen(
            $player, $player->getPositionClone()->addY($player->getSightHeight())->addZ(100), 10, 100)
        );

        $game->loadMap(new TestMap());
        $this->assertTrue($game->getWorld()->canBeSeen(
            $player, $player->getPositionClone()->addY($player->getSightHeight())->addZ(100), 10, 100)
        );
        $game->addPlayer(new Player(2, Color::YELLOW, false));
        $game->getPlayer(2)->setPosition($player->getPositionClone()->addZ(10));
        $this->assertTrue($game->getWorld()->canBeSeen(
            $player, $player->getPositionClone()->addY($player->getSightHeight())->addZ(100), 10, 100, false)
        );
        $this->assertFalse($game->getWorld()->canBeSeen(
            $player, $player->getPositionClone()->addY($player->getSightHeight())->addZ(100), 10, 100, true)
        );
    }

    public function testStairCaseUp(): void
    {
        $steps = 20;
        $ramp = new Ramp(new Point(Setting::playerBoundingRadius() + 10, 0, 0), new Point2D(1, 0), $steps + 1, 250, true, Setting::moveDistancePerTick());

        $game = $this->createTestGame($steps);
        $game->getWorld()->addRamp($ramp);
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveRight());

        $game->start();
        $this->assertSame($steps * $ramp->stepHeight, $game->getPlayer(1)->getPositionClone()->y);
    }

    public function testStairCaseDown(): void
    {
        $steps = 20;
        $startY = $steps * Setting::playerObstacleOvercomeHeight();
        $ramp = new Ramp(
            new Point(Setting::moveDistancePerTick() / -2, $startY, -2 * Setting::playerBoundingRadius()),
            new Point2D(1, 0),
            $steps,
            250,
            false,
            Setting::moveDistancePerTick(),
            Setting::playerObstacleOvercomeHeight()
        );

        $game = $this->createTestGame($steps);
        $game->getWorld()->addRamp($ramp);
        $player = $game->getPlayer(1);
        $player->setPosition($player->getPositionClone()->addY($startY));
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveRight());

        $game->start();
        $this->assertNotSame(0, $player->getPositionClone()->y);
        $this->assertPositionSame(new Point($steps * Setting::moveDistancePerTick(), Setting::playerObstacleOvercomeHeight(), 0), $player->getPositionClone());
    }

    public function testWallPenetration(): void
    {
        $game = $this->createTestGame(Setting::tickCountJump());
        $p = $game->getPlayer(1);
        $box = new Box($p->getPositionClone()->clone()->addZ(Setting::moveDistancePerTick()), $p->getBoundingRadius(), 50, $p->getBoundingRadius());
        $game->getWorld()->addBox($box);
        $wall = new Wall(new Point(-200, -10, $box->getBase()->z + $box->depthZ), true, 400, 3 * Setting::playerJumpHeight());
        $game->getWorld()->addWall($wall);
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->jump();
            $state->getPlayer(1)->moveForward();
        });

        $game->start();
        $this->assertGreaterThanOrEqual($box->heightY, $game->getPlayer(1)->getPositionClone()->y);
        $this->assertSame($wall->getBase() - $p->getBoundingRadius() - 1, $game->getPlayer(1)->getPositionClone()->z);
    }

    public function testBoxPenetration(): void
    {
        $game = $this->createTestGame(50);
        $p = $game->getPlayer(1);
        $box = new Box($p->getPositionClone()->clone()->addZ(2 * Setting::moveDistancePerTick()), 700, 50, 3 * $p->getBoundingRadius());
        $game->getWorld()->addBox($box);
        $depth = $box->getBase()->z + $box->depthZ;
        $wall = new Box(new Point(-100, 0, -$depth), 800, 400, 2 * $depth);
        $game->getWorld()->addBox($wall);
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->getSight()->lookHorizontal(15);
            $state->getPlayer(1)->jump();
            $state->getPlayer(1)->moveForward();
            $state->getPlayer(1)->moveRight();
        });

        $game->start();
        $this->assertGreaterThanOrEqual($box->heightY, $game->getPlayer(1)->getPositionClone()->y);
        $this->assertSame($box->getBase()->z + $box->depthZ - $p->getBoundingRadius() - 1, $game->getPlayer(1)->getPositionClone()->z);
    }

    public function testDoubleBoxPenetration(): void
    {
        $y = 0;
        $boxHeight = Setting::playerHeadHeightCrouch() + 2;
        $scale = (int)ceil(Setting::playerBoundingRadius() * 1.8);
        $game = $this->createTestGame(50);
        $world = $game->getWorld();
        $p1 = $game->getPlayer(1);
        $p1->setPosition(new Point(6 * $scale, $y, 4 * $scale));
        $p2 = new Player(2, Color::BLUE, false);
        $game->addPlayer($p2);
        $p2->setPosition($p1->getPositionClone()->addY($p1->getHeadHeight() + 20));
        $p1->crouch();

        $world->addBox(new Box(new Point(5 * $scale, $y, 5 * $scale), 3 * $scale, $boxHeight, $scale));
        $world->addBox(new Box(new Point(6 * $scale, $y + $boxHeight, 5 * $scale), 1 * $scale, $boxHeight, $scale));

        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->moveForward();
            if ($state->getTickId() < Setting::tickCountCrouch()) {
                return;
            }
            $state->getPlayer(2)->moveForward();
        });
        $game->start();
        $boxStart = 5 * $scale - 1 - $p1->getBoundingRadius();
        $this->assertSame($boxStart, $p1->getPositionClone()->z);
        $this->assertSame($boxStart, $p2->getPositionClone()->z);
    }

}
