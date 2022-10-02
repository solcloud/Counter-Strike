<?php

namespace Test\World;

use cs\Core\Action;
use cs\Core\Box;
use cs\Core\Game;
use cs\Core\GameState;
use cs\Core\Point;
use cs\Core\Point2D;
use cs\Core\Ramp;
use cs\Core\Wall;
use cs\Core\World;
use Test\BaseTestCase;

class WallTest extends BaseTestCase
{

    public function testIsWallAt(): void
    {
        $wallHorizontal = new Wall(new Point(0, 0, 0), true, 2, 1);
        $wallVertical = new Wall(new Point(0, 0, 0), false, 2, 1);
        $world = new World(new Game());
        $world->addWall($wallHorizontal);
        $world->addWall($wallVertical);

        $this->assertNotNull($world->isWallAt(new Point(0, 0, 0)));
        $this->assertNotNull($world->isWallAt(new Point(0, 0, 1)));
        $this->assertNotNull($world->isWallAt(new Point(0, 0, 2)));
        $this->assertNotNull($world->isWallAt(new Point(1, 0, 0)));
        $this->assertNotNull($world->isWallAt(new Point(2, 0, 0)));
        $this->assertNull($world->isWallAt(new Point(1, 0, 1)));
        $this->assertNull($world->isWallAt(new Point(1, 1, 2)));
        $this->assertNull($world->isWallAt(new Point(0, -1, 0)));
        $this->assertNull($world->isWallAt(new Point(0, 2, 0)));
        $this->assertNull($world->isWallAt(new Point(0, 0, 3)));
        $this->assertNull($world->isWallAt(new Point(0, 2, 0)));
        $this->assertNull($world->isWallAt(new Point(1, 1, 1)));
    }

    public function testZWallCollision(): void
    {
        $z = 8;
        $world = new World(new Game());
        $world->addWall(new Wall(new Point(20, 11, $z), true, 10, 20));

        $this->assertNull($world->checkZSideWallCollision(new Point(20, 32, $z), 1, 9));
        $this->assertNotNull($world->checkZSideWallCollision(new Point(20, 20, $z), 2, 9));
        $this->assertNull($world->checkZSideWallCollision(new Point(18, 32, $z), 1, 2));
        $this->assertNotNull($world->checkZSideWallCollision(new Point(15, 20, $z), 2, 5));
        $this->assertNull($world->checkZSideWallCollision(new Point(18, 9, $z), 1, 22));
        $this->assertNotNull($world->checkZSideWallCollision(new Point(15, 9, $z), 2, 5));
        $this->assertNull($world->checkZSideWallCollision(new Point(0, 0, $z), 10, 22));
        $this->assertNotNull($world->checkZSideWallCollision(new Point(0, 0, $z), 12, 20));
    }

    public function testXWallCollision(): void
    {
        $x = 8;
        $world = new World(new Game());
        $world->addWall(new Wall(new Point($x, 11, 20), false, 10, 20));

        $this->assertNull($world->checkXSideWallCollision(new Point($x, 32, 20), 1, 9));
        $this->assertNotNull($world->checkXSideWallCollision(new Point($x, 20, 20), 2, 9));
        $this->assertNull($world->checkXSideWallCollision(new Point($x, 32, 18), 1, 2));
        $this->assertNotNull($world->checkXSideWallCollision(new Point($x, 20, 15), 2, 5));
        $this->assertNull($world->checkXSideWallCollision(new Point($x, 9, 18), 1, 22));
        $this->assertNotNull($world->checkXSideWallCollision(new Point($x, 9, 15), 2, 5));
        $this->assertNull($world->checkXSideWallCollision(new Point($x, 0, 0), 10, 22));
        $this->assertNotNull($world->checkXSideWallCollision(new Point($x, 0, 0), 12, 20));
    }

    public function testRampGenerate(): void
    {
        $stepDepth = 2;
        $stepHeight = 8;
        $ramp = new Ramp(new Point(0, 0, 0), new Point2D(0, 1), 10, 10, true, $stepDepth, $stepHeight);
        $boxes = $ramp->getBoxes();
        $this->assertCount(10, $boxes);
        $depth = 0;
        $height = $stepHeight;

        foreach ($boxes as $box) {
            $this->assertSame(0, $box->getBase()->y);
            $floors = $box->getFloors();
            $this->assertCount(2, $floors);
            $this->assertSame(0, $floors[0]->getY());
            $this->assertSame($height, $floors[1]->getY());
            $this->assertSame(0, $floors[0]->getStart()->x, "$floors[0]");
            $this->assertSame(0, $floors[1]->getStart()->x);
            $this->assertSame($depth, $floors[0]->getStart()->z, "$floors[0]");
            $this->assertSame($depth, $floors[1]->getStart()->z);
            $height += $stepHeight;
            $depth += $stepDepth;
            $this->assertSame($depth, $floors[0]->getEnd()->z);
            $this->assertSame($depth, $floors[1]->getEnd()->z);
        }
    }

    public function testPlayerRampSpeed(): void
    {
        $numOfBoxes = 20;
        $game = $this->createTestGame($numOfBoxes);
        $player = $game->getPlayer(1);

        $ramp = new Ramp(
            new Point(0, 0, Action::playerBoundingRadius()),
            new Point2D(0, 1),
            $numOfBoxes * 3,
            10,
            true,
            Action::moveDistancePerTick(),
            Action::playerObstacleOvercomeHeight(),
        );
        $game->getWorld()->addRamp($ramp);

        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertPositionSame(
            new Point(0, $numOfBoxes * Action::playerObstacleOvercomeHeight(), $numOfBoxes * Action::moveDistancePerTick()),
            $player->getPositionImmutable()
        );
    }

    public function testPlayerRunningStairs(): void
    {
        $numOfBoxes = 10;
        $game = $this->createOneRoundGame($numOfBoxes);

        $base = new Point();
        for ($i = 1; $i <= $numOfBoxes; $i++) {
            $game->getWorld()->addBox(
                new Box(
                    $base->clone()->setZ($i * Action::moveDistancePerTick()),
                    1,
                    Action::playerObstacleOvercomeHeight(),
                    Action::moveDistancePerTick()
                )
            );
            $base->addY(Action::playerObstacleOvercomeHeight());
        }

        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertPlayerPosition($game, new Point(0, Action::playerObstacleOvercomeHeight() * $numOfBoxes, Action::moveDistancePerTick() * $numOfBoxes));
    }

    public function testPlayerRunningStairsToDeath(): void
    {
        $numOfBoxes = 40;
        $game = $this->createOneRoundGame($numOfBoxes + 40);
        $player = $game->getPlayer(1);
        $this->assertTrue($player->isAlive());

        $base = new Point(-1);
        for ($i = 1; $i <= $numOfBoxes; $i++) {
            $game->getWorld()->addBox(
                new Box(
                    $base->clone()->setZ($i * Action::moveDistancePerTick()),
                    20,
                    Action::playerObstacleOvercomeHeight(),
                    Action::moveDistancePerTick()
                )
            );
            $base->addY(Action::playerObstacleOvercomeHeight());
        }
        $box = new Box(new Point(-5, 0, Action::moveDistancePerTick() * ($numOfBoxes + 2)), 10, $base->y, 10);
        $game->getWorld()->addBox($box);

        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertPositionSame(new Point(0, 0, $box->getBase()->z - 1), $player->getPositionImmutable());
        $this->assertFalse($player->isAlive());
    }

    public function testPlayerRunningStairsToDeathBoundingRadius(): void
    {
        $numOfBoxes = 40;
        $game = $this->createTestGame($numOfBoxes + 40);
        $player = $game->getPlayer(1);
        $this->assertTrue($player->isAlive());

        $base = new Point(-1);
        for ($i = 1; $i <= $numOfBoxes; $i++) {
            $game->getWorld()->addBox(
                new Box(
                    $base->clone()->setZ($i * Action::moveDistancePerTick()),
                    20,
                    Action::playerObstacleOvercomeHeight(),
                    Action::moveDistancePerTick()
                )
            );
            $base->addY(Action::playerObstacleOvercomeHeight());
        }
        $box = new Box(new Point(-5, 0, Action::moveDistancePerTick() * ($numOfBoxes + 2) + $player->getBoundingRadius()), 10, $base->y, 10);
        $game->getWorld()->addBox($box);

        $game->onTick(function (GameState $state) use ($numOfBoxes) {
            $state->getPlayer(1)->moveForward();
            if ($state->getTickId() === $numOfBoxes) {
                $this->assertGreaterThan(0, $state->getPlayer(1)->getPositionImmutable()->y);
            }
        });
        $game->start();
        $this->assertPositionSame(new Point(0, 0, $box->getBase()->z - 1 - $player->getBoundingRadius()), $player->getPositionImmutable());
        $this->assertFalse($player->isAlive());
    }

}
