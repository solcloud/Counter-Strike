<?php

namespace Test\Unit;

use cs\Core\Box;
use cs\Core\Collision;
use cs\Core\Floor;
use cs\Core\Point;
use cs\Core\Point2D;
use Test\BaseTest;

class CollisionTest extends BaseTest
{

    public function testPointWithCircle(): void
    {
        $this->assertTrue(Collision::pointWithCircle(10, 10, 10, 10, 1));
        $this->assertTrue(Collision::pointWithCircle(11, 10, 10, 10, 1));
        $this->assertTrue(Collision::pointWithCircle(10, 11, 10, 10, 1));

        $this->assertFalse(Collision::pointWithCircle(10, 13, 10, 10, 2));
        $this->assertFalse(Collision::pointWithCircle(13, 10, 10, 10, 2));
    }

    public function testCircleWithPlaneFalse(): void
    {
        $radius = 2;
        $floor = new Floor(new Point(1, 0, 1), 5, 2);
        $circles = [
            new Point2D(-1, 4),
            new Point2D(-2, 2),
            new Point2D(0, -1),
            new Point2D(3, -2),
            new Point2D(7, -1),
            new Point2D(8, 4),
            new Point2D(8, 4),
            new Point2D(6, 6),
            new Point2D(-1, 0),
        ];
        foreach ($circles as $circleCenter) {
            $this->assertFalse(Collision::circleWithPlane($circleCenter, $radius, $floor), "Circle: {$circleCenter} x Floor: {$floor}");
            $fs = $floor->getPoint2DStart();
            $fe = $floor->getPoint2DEnd();
            $this->assertFalse(Collision::circleWithRect(
                $circleCenter->x, $circleCenter->y, $radius,
                $fs->x, $fe->x, $fs->y, $fe->y
            ), "Circle: {$circleCenter} x Floor: {$floor}");
        }
    }

    public function testCircleWithPlaneTrue(): void
    {
        $radius = 2;
        $floor = new Floor(new Point(1, 0, 1), 5, 2);
        $circles = [
            new Point2D(-1, 3),
            new Point2D(-1, 2),
            new Point2D(3, 5),
            new Point2D(7, 2),
            new Point2D(6, -1),
            new Point2D(4, -1),
            new Point2D(3, 0),
            new Point2D(1, 0),
            new Point2D(0, 1),
        ];
        foreach ($circles as $circleCenter) {
            $this->assertTrue(Collision::circleWithPlane($circleCenter, $radius, $floor), "Circle: {$circleCenter} x Floor: {$floor}");
            $fs = $floor->getPoint2DStart();
            $fe = $floor->getPoint2DEnd();
            $this->assertTrue(Collision::circleWithRect(
                $circleCenter->x, $circleCenter->y, $radius,
                $fs->x, $fe->x, $fs->y, $fe->y
            ), "Circle: {$circleCenter} x Floor: {$floor}");
        }
    }

    public function testPointWithCylinderTrue(): void
    {
        $cylinderBottom = new Point(3, -1, 0);
        $radius = 3;
        $height = 6;
        $points = [
            new Point(0, 0, 0),
            new Point(3, 0, 0),
            new Point(3, 0, 1),
            new Point(4, 2, 0),
            new Point(4, 1, 1),
            new Point(6, 0, 0),
        ];
        foreach ($points as $point) {
            $this->assertTrue(Collision::pointWithCylinder($point, $cylinderBottom, $radius, $height), "Point: {$point}");
        }

    }

    public function testPointWithCylinderFalse(): void
    {
        $this->assertFalse(Collision::cylinderWithCylinder(new Point(132, 3, 88), 44, 190, new Point(55, -10, 45), 44, 190));
        $cylinderBottom = new Point(3, -1, 0);
        $radius = 3;
        $height = 6;
        $points = [
            new Point(0, -3, 0),
            new Point(4, 6, 0),
            new Point(2, -3, 0),
            new Point(6, 0, 1),
        ];
        foreach ($points as $point) {
            $this->assertFalse(Collision::pointWithCylinder($point, $cylinderBottom, $radius, $height), "Point: {$point}");
        }
    }

    public function testCylinderWithCylinderTrue(): void
    {
        $centerA = new Point(4, 0, 0);
        $radiusA = 3;
        $heightA = 6;
        $radiusB = 2;
        $heightB = 4;
        $centers = [
            new Point(0, 0, 0),
            new Point(0, -3, 0),
            new Point(4, 6, 0),
            new Point(2, -3, 0),
            new Point(4, 2, 0),
            new Point(4, 1, 1),
        ];
        foreach ($centers as $centerB) {
            $this->assertTrue(Collision::cylinderWithCylinder($centerA, $radiusA, $heightA, $centerB, $radiusB, $heightB), "CenterB: {$centerB}");
        }
    }

    public function testCylinderWithCylinderFalse(): void
    {
        $centerA = new Point(-2, 1, 2);
        $radiusA = 2;
        $heightA = 6;
        $radiusB = 2;
        $heightB = 4;
        $centers = [
            new Point(-2, 11, 2),
            new Point(-6, 2, 0),
            new Point(-8, 2, 0),
            new Point(-7, 2, 0),
            new Point(5, 2, 0),
            new Point(2, 2, 0),
            new Point(1, 2, 5),
            new Point(5, 3, 5),
            new Point(0, -5, 0),
        ];
        foreach ($centers as $centerB) {
            $this->assertFalse(Collision::cylinderWithCylinder($centerA, $radiusA, $heightA, $centerB, $radiusB, $heightB), "CenterB: {$centerB}");
        }
    }

    public function testPointWithSphere(): void
    {
        $sphereCenter = new Point();
        $sphereRadius = 10;

        $points = [
            new Point(-4, 6, 6),
            new Point(-5, 6, 6),
            new Point(-1, 7, 6),
            new Point(-1, 7, 7),
            new Point(-5, 5, 6),
            new Point(-1, 6, 7),
            new Point(-6, 6, 5),
        ];
        foreach ($points as $point) {
            $this->assertTrue(Collision::pointWithSphere($point, $sphereCenter, $sphereRadius), "Point: {$point}");
        }
        $points = [
            new Point(-6, 6, 6),
            new Point(-5, 7, 6),
            new Point(-5, 8, 6),
            new Point(4, -7, 6),
            new Point(7, 2, 7),
            new Point(8, 2, 7),
        ];
        foreach ($points as $point) {
            $this->assertFalse(Collision::pointWithSphere($point, $sphereCenter, $sphereRadius), "Point: {$point}");
        }
    }

    public function testPointWithBox(): void
    {
        $box = new Box(new Point(1, 1, 1), 10, 2, 4);

        $points = [
            new Point(1, 1, 3),
            new Point(2, 2, 4),
            new Point(11, 2, 1),
            new Point(10, 3, 3),
        ];
        foreach ($points as $point) {
            $this->assertTrue(Collision::pointWithBox($point, $box), "Point: {$point}");
        }
        $points = [
            new Point(1, 1, -3),
            new Point(2, 4, 4),
            new Point(12, 2, 1),
            new Point(-1, 3, 3),
            new Point(2, 2, 0),
            new Point(2, 2, 6),
        ];
        foreach ($points as $point) {
            $this->assertFalse(Collision::pointWithBox($point, $box), "Point: {$point}");
        }
    }

    public function testPointWithSphereTrue(): void
    {
        $sphereCenter = new Point();
        $sphereRadius = 2;
        $points = [
            new Point(),
            new Point(0, 0, 1),
            new Point(0, 0, 2),
            new Point(1, 0, 1),
            new Point(1, 1, 1),
            new Point(2, 0, 0),
        ];
        foreach ($points as $point) {
            $this->assertTrue(Collision::pointWithSphere($point, $sphereCenter, $sphereRadius), "Point: {$point}");
        }
    }

    public function testPointWithSphereFalse(): void
    {
        $sphereCenter = new Point();
        $sphereRadius = 2;
        $points = [
            new Point(-2, 1, 1),
            new Point(-2, 2, 1),
            new Point(-4, 0, 1),
            new Point(-4, 2, 3),
            new Point(0, 2, 5),
            new Point(1, 2, 1),
            new Point(1, 2, 5),
            new Point(1, 3, 5),
            new Point(1, 0, 2),
            new Point(2, -2, 1),
            new Point(2, 1, 1),
            new Point(2, 2, 1),
        ];
        foreach ($points as $point) {
            $this->assertFalse(Collision::pointWithSphere($point, $sphereCenter, $sphereRadius), "Point: {$point}");
        }
    }

    public function testPlaneWithPlane(): void
    {
        $this->assertTrue(Collision::planeWithPlane(new Point2D(0, 0), 3440, 950, 45, 0, 88, 190));
        $this->assertTrue(Collision::planeWithPlane(new Point2D(45, 0), 3440, 950, 45, 0, 88, 190));
        $this->assertFalse(Collision::planeWithPlane(new Point2D(145, 0), 3440, 950, 45, 0, 88, 190));
    }

    public function testPointWithBoxBoundary(): void
    {
        $this->assertTrue(Collision::pointWithBoxBoundary(new Point(), new Point(-5, 0, -5), new Point(5, 4, 5)));
        $this->assertTrue(Collision::pointWithBoxBoundary(new Point(4, 2, 5), new Point(-5, 0, -5), new Point(5, 4, 5)));
        $this->assertTrue(Collision::pointWithBoxBoundary(new Point(0, 1, 1), new Point(), new Point(1, 1, 1)));
        $this->assertTrue(Collision::pointWithBoxBoundary(new Point(0, 1, 1), new Point(), new Point(1, 8, 1)));
        $this->assertTrue(Collision::pointWithBoxBoundary(new Point(1, 1, 1), new Point(), new Point(1, 8, 1)));
        $this->assertTrue(Collision::pointWithBoxBoundary(new Point(1, 1, 0), new Point(), new Point(1, 8, 1)));

        $this->assertFalse(Collision::pointWithBoxBoundary(new Point(-6), new Point(-5, 0, -5), new Point(5, 4, 5)));
        $this->assertFalse(Collision::pointWithBoxBoundary(new Point(4, 5, 2), new Point(-5, 0, -5), new Point(5, 4, 5)));
        $this->assertFalse(Collision::pointWithBoxBoundary(new Point(4, 2, 6), new Point(-5, 0, -5), new Point(5, 4, 5)));
    }

    public function testBoxWithBox(): void
    {
        $this->assertTrue(Collision::boxWithBox(
            new Point(-5, 0, -5), new Point(5, 4, 5),
            new Point(1, 0, -1), new Point(3, 3, 1),
        ));
        $this->assertTrue(Collision::boxWithBox(
            new Point(-5, 0, -5), new Point(5, 4, 5),
            new Point(1, 4, -1), new Point(3, 7, 1),
        ));
        $this->assertTrue(Collision::boxWithBox(
            new Point(-5, 0, -5), new Point(5, 4, 5),
            new Point(1, -2, -1), new Point(3, 1, 1),
        ));
        $this->assertTrue(Collision::boxWithBox(
            new Point(-5, 0, -5), new Point(5, 4, 5),
            new Point(1, 2, -3), new Point(3, 5, -1),
        ));
        $this->assertTrue(Collision::boxWithBox(
            new Point(-5, 0, -5), new Point(5, 4, 5),
            new Point(-3, 3, 2), new Point(-1, 6, 4),
        ));
        $this->assertTrue(Collision::boxWithBox(
            new Point(-5, 0, -5), new Point(5, 4, 5),
            new Point(1, 2, -7), new Point(3, 5, -5),
        ));
        $this->assertTrue(Collision::boxWithBox(
            new Point(-5, 0, -5), new Point(5, 4, 5),
            new Point(5, 2, -7), new Point(6, 5, -5),
        ));
        $this->assertTrue(Collision::boxWithBox(
            new Point(-5, 0, -5), new Point(5, 4, 5),
            new Point(1, 2, -5), new Point(3, 5, 5),
        ));
        $this->assertTrue(Collision::boxWithBox(
            new Point(-5, 0, -5), new Point(5, 4, 5),
            new Point(1, -2, -5), new Point(3, 0, 5),
        ));
        $this->assertTrue(Collision::boxWithBox(
            new Point(-5, 0, -5), new Point(5, 4, 5),
            new Point(-6, 0, -5), new Point(-5, 2, 5),
        ));
        $this->assertTrue(Collision::boxWithBox(
            new Point(2, 0, 1), new Point(5, 4, 5),
            new Point(-1, -1, 5), new Point(2, 2, 5),
        ));


        $this->assertFalse(Collision::boxWithBox(
            new Point(-5, 0, -5), new Point(5, 4, 5),
            new Point(1, 5, 2), new Point(3, 8, 4),
        ));
        $this->assertFalse(Collision::boxWithBox(
            new Point(-5, 0, -5), new Point(5, 4, 5),
            new Point(1, 2, -8), new Point(3, 5, -6),
        ));
        $this->assertFalse(Collision::boxWithBox(
            new Point(-5, 0, -5), new Point(5, 4, 5),
            new Point(1, -6, -5), new Point(3, -3, -3),
        ));
    }


}
