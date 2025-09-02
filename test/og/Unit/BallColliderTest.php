<?php

namespace Test\Unit;

use cs\Core\Box;
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

    public function testResolution1(): void
    {
        /////
        $radius = 2;
        $angleHorizontal = 0.0;
        $angleVertical = -90.0;
        $start = new Point(10, 10, 0);
        $resolutionPoint = new Point(10, 4, 0);
        $resolutionAngleHorizontal = $angleHorizontal;
        $resolutionAngleVertical = 90.0;
        /////

        $world = $this->createWorld();
        $ball = $this->createBall($start, $radius, $world, $angleHorizontal, $angleVertical);
        $world->addBox(new Box(new Point(9), 1, 1, 1));
        $world->addBox(new Box(new Point(10), 1, 1, 1));
        $world->addBox(new Box(new Point(11), 1, 1, 1));

        $this->runCollision($ball, $start, $angleHorizontal, $angleVertical);
        $this->assertPositionSame($resolutionPoint, $ball->getLastValidPosition());
        $this->assertSame($resolutionAngleHorizontal, $ball->getResolutionAngleHorizontal());
        $this->assertSame($resolutionAngleVertical, $ball->getResolutionAngleVertical());
    }

    public function testResolution2(): void
    {
        /////
        $radius = 2;
        $angleHorizontal = rand(1, 40);
        $angleVertical = 45.0;
        $start = new Point(14, $radius, 0);
        $extreme = $start->clone();
        /////

        $world = $this->createWorld();
        $ball = $this->createBall($start, $radius, $world, $angleHorizontal, $angleVertical);
        $this->assertPositionSame($extreme, $ball->getLastExtremePosition());
        $this->assertFalse($ball->hasCollision($start->addPart(-2, 3, 0)));
        $this->assertPositionSame($extreme, $ball->getLastExtremePosition());
        $this->assertFalse($ball->hasCollision($start->addPart(-2, 3, 0)));
        $this->assertPositionSame($extreme, $ball->getLastExtremePosition());
        $this->assertFalse($ball->hasCollision($start->addPart(-2, 2, 0)));
        $this->assertPositionSame($extreme, $ball->getLastExtremePosition());
        $this->assertFalse($ball->hasCollision($start->addPart(-2, 0, 0)));
        $this->assertPositionSame($extreme, $ball->getLastExtremePosition());
        $this->assertFalse($ball->hasCollision($start->addPart(-2, 0, 0)));
        $this->assertPositionSame($extreme, $ball->getLastExtremePosition());
        $this->assertFalse($ball->hasCollision($start->addPart(-2, -2, 0)));
        $extreme->setFrom($start);
        $this->assertPositionSame($extreme, $ball->getLastExtremePosition());
        $this->assertTrue($ball->hasCollision($start->addPart(-1, -1, 0)));
        $this->assertPositionSame($start, $ball->getLastExtremePosition());
        $this->assertPositionSame(new Point($radius, $radius + 6, 0), $ball->getLastValidPosition());
        $this->assertSame(360.0 - $angleHorizontal, round($ball->getResolutionAngleHorizontal()));
    }

    protected function runCollision(BallCollider $ball, Point $start, float $angleHorizontal, float $angleVertical): void
    {
        $candidate = $start->clone();
        for ($distance = 1; $distance <= 128; $distance++) {
            $candidate->setFrom($start);
            $candidate->addFromArray(Util::movementXYZ($angleHorizontal, $angleVertical, $distance));
            if ($ball->hasCollision($candidate)) {
                return;
            }
        }

        $this->fail("No '{$start}' collision detected");
    }

    public function testSingleWallAngledBounce(): void
    {
        $this->_testSingleWallBounce(new Point(5, 15, 5), 1, 180, 10, (new Wall(new Point(1, 1, 1), true, 100))->setNormal(0, 40), 0, -62, new Point(5, 16, 2));
        $this->_testSingleWallBounce(new Point(5, 15, 5), 1, 180, 70, (new Wall(new Point(1, 1, 1), true, 100))->setNormal(0, -10), 0, 49, new Point(5, 23, 2));
    }

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
        Plane $plane, float $expectedAngleHorizontal, float $expectedAngleVertical,
        ?Point $expectedCollisionPoint = null, int $maxDistance = 16
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
        $ball = new BallCollider($world, $ballCenter, $ballRadius, $angleHorizontal, $angleVertical);

        $candidate = $ballCenter->clone();
        for ($distance = 1; $distance <= $maxDistance; $distance++) {
            $candidate->setFrom($ballCenter);
            $candidate->addFromArray(Util::movementXYZ($angleHorizontal, $angleVertical, $distance));
            if (!$ball->hasCollision($candidate)) {
                continue;
            }

            $expectedCollisionPoint && $this->assertPositionSame($expectedCollisionPoint, $candidate);
            $this->assertSame($expectedAngleHorizontal, round($ball->getResolutionAngleHorizontal()));
            $this->assertSame($expectedAngleVertical, round($ball->getResolutionAngleVertical()));

            $p = $plane->getStart();
            if ($isWall) {
                if ($plane->getPlane() === 'xy') {
                    $this->assertSame($p->clone()->addZ($angleHorizontal > 270 || $angleHorizontal < 90 ? -$ballRadius : +$ballRadius)->z, $candidate->z);
                } else {
                    $this->assertSame($p->clone()->addX($angleHorizontal > 0 && $angleHorizontal < 180 ? -$ballRadius : +$ballRadius)->x, $candidate->x);
                }
            } else {
                if ($angleVertical === 0.0) {
                    $this->fail('Floor with 0 vertical angle');
                }
                $this->assertSame($p->clone()->addY($angleVertical > 0 ? -$ballRadius : +$ballRadius)->y, $candidate->y);
            }

            return;
        }

        $this->fail('No collision detected');
    }

    private function createBall(Point $start, int $radius, World $world, float $angleHorizontal, float $angleVertical): BallCollider
    {
        return new BallCollider($world, $start, $radius, $angleHorizontal, $angleVertical);
    }

    private function createWorld(): World
    {
        $game = GameFactory::createDebug();
        $world = new World($game);
        $world->loadMap(new TestMap());

        return $world;
    }

}
