<?php

namespace Test\World;

use cs\Core\Box;
use cs\Core\Game;
use cs\Core\GameException;
use cs\Core\GameState;
use cs\Core\Point;
use cs\Core\Ramp;
use cs\Core\Setting;
use cs\Core\Wall;
use cs\Core\World;
use cs\Enum\RampDirection;
use Test\BaseTestCase;

class WallTest extends BaseTestCase
{

    public function testIsWallAt(): void
    {
        $wallHorizontal = new Wall(new Point(0, 0, 0), true, 2, 1);
        $wallVertical = new Wall(new Point(0, 0, 0), false, 2, 1);
        $wallHorizontal->setHitAntiForce(123, 10, 1);
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
        $this->assertNotNull($world->checkZSideWallCollision(new Point(15, 10, $z), 2, 5));
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
        $this->assertNotNull($world->checkXSideWallCollision(new Point($x, 10, 15), 2, 5));
        $this->assertNull($world->checkXSideWallCollision(new Point($x, 0, 0), 10, 22));
        $this->assertNotNull($world->checkXSideWallCollision(new Point($x, 0, 0), 12, 20));
    }

    public function testWallSerialization(): void
    {
        $wall = new Wall(new Point(1, 2, 3), true, 10, 20);
        $this->assertSame([
            's' => [
                'x' => 1,
                'y' => 2,
                'z' => 3,
            ],
            'e' => [
                'x' => 11,
                'y' => 22,
                'z' => 3,
            ],
            'p' => 'xy',
        ], $wall->toArray());

        $wall = new Wall(new Point(1, 2, 3), false, 10, 20);
        $this->assertSame([
            's' => [
                'x' => 1,
                'y' => 2,
                'z' => 3,
            ],
            'e' => [
                'x' => 1,
                'y' => 22,
                'z' => 13,
            ],
            'p' => 'zy',
        ], $wall->toArray());
        $wallClone = Wall::fromArray($wall->toArray());
        $this->assertSame($wall->getFloor(), $wallClone->getFloor());
        $this->assertSame($wall->isWidthOnXAxis(), $wallClone->isWidthOnXAxis());
    }

    public function testZeroWallWidth(): void
    {
        $this->expectException(GameException::class);
        new Wall(new Point(), true, 0, 1);
    }

    public function testZeroWallHeight(): void
    {
        $this->expectException(GameException::class);
        new Wall(new Point(), true, 1, 0);
    }

    public function testRampGenerate(): void
    {
        $stepDepth = 2;
        $stepHeight = 8;
        $ramp = new Ramp(new Point(0, 0, 0), RampDirection::GROW_TO_POSITIVE_Z, 10, 10, true, $stepDepth, $stepHeight);
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
            new Point(0, 0, Setting::playerBoundingRadius() + 1),
            RampDirection::GROW_TO_POSITIVE_Z,
            $numOfBoxes * 3,
            10,
            true,
            Setting::moveDistancePerTick(),
            Setting::playerObstacleOvercomeHeight(),
        );
        $game->getWorld()->addRamp($ramp);

        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertPositionSame(
            new Point(0, $numOfBoxes * Setting::playerObstacleOvercomeHeight(), $numOfBoxes * Setting::moveDistancePerTick()),
            $player->getPositionClone()
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
                    $base->clone()->setZ($i * Setting::moveDistancePerTick()),
                    1,
                    Setting::playerObstacleOvercomeHeight(),
                    Setting::moveDistancePerTick()
                )
            );
            $base->addY(Setting::playerObstacleOvercomeHeight());
        }

        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertPlayerPosition($game, new Point(0, Setting::playerObstacleOvercomeHeight() * $numOfBoxes, Setting::moveDistancePerTick() * $numOfBoxes));
    }

    public function testPlayerRunningStairsToDeath(): void
    {
        $numOfBoxes = 40;
        $game = $this->createOneRoundGame($numOfBoxes + 40);
        $player = $game->getPlayer(1);
        $this->assertTrue($player->isAlive());

        $base = new Point(-1);
        for ($i = 1; $i <= $numOfBoxes; $i++) {
            $box = new Box(
                $base->clone()->setZ($i * Setting::moveDistancePerTick()),
                20,
                Setting::playerObstacleOvercomeHeight(),
                Setting::moveDistancePerTick()
            );
            $game->getWorld()->addBox($box);
            $base->addY(Setting::playerObstacleOvercomeHeight());
        }
        $box = new Box($box->getBase()->clone()->addZ($box->depthZ + Setting::moveDistancePerTick() + 2)->setY(0), 20, $box->getBase()->y + $box->heightY, 10);
        $game->getWorld()->addBox($box);

        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertPositionSame(new Point(0, 0, $box->getBase()->z - 1), $player->getPositionClone());
        $this->assertFalse($player->isAlive());
    }

    public function testPlayerRunningStairsToDeathBoundingRadius(): void
    {
        $numOfBoxes = 40;
        $game = $this->createTestGameNoPause($numOfBoxes + 40);
        $player = $game->getPlayer(1);
        $this->assertTrue($player->isAlive());

        $base = new Point(-1);
        for ($i = 1; $i <= $numOfBoxes; $i++) {
            $box = new Box(
                $base->clone()->setZ($i * Setting::moveDistancePerTick()),
                20,
                Setting::playerObstacleOvercomeHeight(),
                Setting::moveDistancePerTick()
            );
            $game->getWorld()->addBox($box);
            $base->addY(Setting::playerObstacleOvercomeHeight());
        }
        $box = new Box($box->getBase()->clone()->addZ($box->depthZ + Setting::moveDistancePerTick() + Setting::playerBoundingRadius() + 2)->setY(0), 10, $box->getBase()->y + $box->heightY, 10);
        $game->getWorld()->addBox($box);

        $game->onTick(function (GameState $state) use ($numOfBoxes) {
            $state->getPlayer(1)->moveForward();
            if ($state->getTickId() === $numOfBoxes + 2) {
                $this->assertGreaterThan(0, $state->getPlayer(1)->getPositionClone()->y);
                $this->assertSame(Setting::playerObstacleOvercomeHeight() * $numOfBoxes, $state->getPlayer(1)->getPositionClone()->y);
            }
            if ($state->getTickId() === $numOfBoxes + 3) {
                $this->assertLessThan(Setting::playerObstacleOvercomeHeight() * $numOfBoxes, $state->getPlayer(1)->getPositionClone()->y);
                $this->assertSame(Setting::playerObstacleOvercomeHeight() * $numOfBoxes - Setting::fallAmountPerTick() - 1, $state->getPlayer(1)->getPositionClone()->y); // test for initial (one-shot) gravity bump
            }
        });
        $game->start();
        $this->assertPositionSame(new Point(0, 0, $box->getBase()->z - 1 - $player->getBoundingRadius()), $player->getPositionClone());
        $this->assertFalse($player->isAlive());
    }

}
