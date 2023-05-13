<?php

namespace Test\Unit;

use cs\Core\Floor;
use cs\Core\GameFactory;
use cs\Core\Plane;
use cs\Core\Point;
use cs\Core\Util;
use cs\Core\Wall;
use cs\Core\World;
use cs\HitGeometry\BallCollider;
use cs\Map\TestMap;
use Test\BaseTest;

class BallColliderTest extends BaseTest
{

    public function testSingleWallBounce(): void
    {
        $this->_testSingleWallBounce(new Point(5, 5, 11), 4, 0, 90, new Floor(new Point(5, 16, 11)), 0, -90);
        $this->_testSingleWallBounce(new Point(5, 15, 11), 3, 0, -90, new Floor(new Point(5, 4, 11)), 0, 90);
        foreach (range(1, 5) as $r) {
            $this->_testSingleWallBounce(new Point(5, 5, 11), $r, 0, 90, new Floor(new Point(5, 16, 11)), 0, -90);
        }
        $this->_testSingleWallBounce(new Point(5, 6, 11), 2, 90, -45, new Floor(new Point(5, 2, 11), 9), 90, 45);

        $this->_testSingleWallBounce(new Point(5, 5, 0), 3, 0, 0, new Wall(new Point(5, 1, 11), true), 180, 0);
        $this->_testSingleWallBounce(new Point(5, 5, 0), 2, 90, 0, new Wall(new Point(11, 0, 0), false), 270, 0);
        foreach (range(1, 5) as $r) {
            $this->_testSingleWallBounce(new Point(5, 5, 14), $r, 180, 0, new Wall(new Point(5, 5, 2), true), 0, 0);
        }
        $this->_testSingleWallBounce(new Point(15, 5, 0), 2, 270, 0, new Wall(new Point(4, 2, 0), false), 90, 0);
        $this->_testSingleWallBounce(new Point(15, 5, 0), 2, 270, 45, new Wall(new Point(4, 2, 0), false), 90, 45);
    }

    private function _testSingleWallBounce(
        Point $ballCenter, int $ballRadius, float $angleHorizontal, float $angleVertical,
        Plane $plane, float $expectedAngleHorizontal, float $expectedAngleVertical, int $maxDistance = 16
    ): void
    {
        $world = $this->createWorld();
        if ($plane instanceof Floor) {
            $world->addFloor($plane);
            $isWall = false;
        } elseif ($plane instanceof Wall) {
            $world->addWall($plane);
            $isWall = true;
        } else {
            $this->fail("Unknown plane given");
        }
        $ball = new BallCollider($world, $ballCenter->clone(), $ballRadius);
        $direction = Util::movementXYZ($angleHorizontal, $angleVertical, 1);

        for ($distance = 1; $distance <= $maxDistance; $distance++) {
            $collision = $ball->resolveCollision($ballCenter);
            if (!$collision) {
                $ballCenter->addFromArray($direction);
                continue;
            }

            [$newPosition, $angleH, $angleV] = $collision;
            $this->assertInstanceOf(Point::class, $newPosition);
            $this->assertSame($expectedAngleHorizontal, $angleH);
            $this->assertSame($expectedAngleVertical, $angleV);

            $p = $plane->getStart();
            if ($isWall) {
                if ($plane->getPlane() === 'xy') {
                    $this->assertSame($p->clone()->addZ($angleHorizontal > 270 || $angleHorizontal < 90 ? -$ballRadius : +$ballRadius)->z, $newPosition->z);
                } else {
                    $this->assertSame($p->clone()->addX($angleHorizontal > 0 && $angleHorizontal < 180 ? -$ballRadius : +$ballRadius)->x, $newPosition->x);
                }
            } else {
                if ($angleVertical === 0.0) {
                    $this->fail('Floor with 0 vertical angle');
                }
                $this->assertSame($p->clone()->addY($angleVertical > 0 ? -$ballRadius : +$ballRadius)->y, $newPosition->y);
            }

            return;
        }

        $this->fail();
    }

    private function createWorld(): World
    {
        $game = GameFactory::createDebug();
        $world = new World($game);
        $world->loadMap(new TestMap());

        return $world;
    }

}
