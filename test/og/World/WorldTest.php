<?php

namespace Test\World;

use cs\Core\Action;
use cs\Core\Box;
use cs\Core\Floor;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Point2D;
use cs\Core\Ramp;
use cs\Core\Wall;
use cs\Core\World;
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

    public function testStairCase(): void
    {
        $steps = 20;
        $ramp = new Ramp(new Point(Action::playerBoundingRadius(), 0, 0), new Point2D(1, 0), $steps + 1, 250, true, Action::moveDistancePerTick());

        $game = $this->createTestGame($steps);
        $game->getWorld()->addRamp($ramp);
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveRight());

        $game->start();
        $this->assertSame($steps * $ramp->stepHeight, $game->getPlayer(1)->getPositionImmutable()->y);
    }

    public function testWallPenetration(): void
    {
        $game = $this->createTestGame(Action::tickCountJump());
        $p = $game->getPlayer(1);
        $box = new Box($p->getPositionImmutable()->clone()->addZ(Action::moveDistancePerTick()), $p->getBoundingRadius(), 50, $p->getBoundingRadius());
        $game->getWorld()->addBox($box);
        $wall = new Wall(new Point(-200, -10, $box->getBase()->z + $box->depthZ), true, 400, 3 * Action::playerJumpHeight());
        $game->getWorld()->addWall($wall);
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->jump();
            $state->getPlayer(1)->moveForward();
        });

        $game->start();
        $this->assertGreaterThanOrEqual($box->heightY, $game->getPlayer(1)->getPositionImmutable()->y);
        $this->assertSame($wall->getBase() - $p->getBoundingRadius() - 1, $game->getPlayer(1)->getPositionImmutable()->z);
    }

    public function testBoxPenetration(): void
    {
        $game = $this->createTestGame(50);
        $p = $game->getPlayer(1);
        $box = new Box($p->getPositionImmutable()->clone()->addZ(2 * Action::moveDistancePerTick()), 700, 50, 3 * $p->getBoundingRadius());
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
        $this->assertGreaterThanOrEqual($box->heightY, $game->getPlayer(1)->getPositionImmutable()->y);
        $this->assertSame($box->getBase()->z + $box->depthZ - $p->getBoundingRadius() - 1, $game->getPlayer(1)->getPositionImmutable()->z);
    }

}
