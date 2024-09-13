<?php

namespace Test\Unit;

use cs\Core\GameException;
use cs\Core\GameProperty;
use cs\Core\PlayerCamera;
use cs\Core\Point;
use cs\Core\Point2D;
use cs\Core\Util;
use Test\BaseTest;

class UtilTest extends BaseTest
{

    public function testNegativeTime(): void
    {
        $this->expectExceptionMessage('Negative time given');
        Util::millisecondsToFrames(-1);
    }

    public function testPlayerCameraAngles(): void
    {
        $camera = new PlayerCamera();

        $camera->lookHorizontal(45 - 90);
        $this->assertSame(360.0 - 45, $camera->getRotationHorizontal());
        $camera->lookHorizontal(15 - 90);
        $this->assertSame(360.0 - 75, $camera->getRotationHorizontal());
        $camera->lookHorizontal(-90);
        $this->assertSame(270.0, $camera->getRotationHorizontal());
        $camera->lookHorizontal(-91);
        $this->assertSame(269.0, $camera->getRotationHorizontal());
    }

    public function testHorizontalMovementXZ(): void
    {
        $hypotenuse = 15;
        $this->assertSame([0, 15], Util::movementXZ(0, $hypotenuse));
        $this->assertSame([11, 11], Util::movementXZ(45, $hypotenuse));
        $this->assertSame([15, 0], Util::movementXZ(90, $hypotenuse));
        $this->assertSame([11, -11], Util::movementXZ(135, $hypotenuse));
        $this->assertSame([0, -15], Util::movementXZ(180, $hypotenuse));
        $this->assertSame([-11, -11], Util::movementXZ(225, $hypotenuse));
        $this->assertSame([-15, 0], Util::movementXZ(270, $hypotenuse));
        $this->assertSame([-11, 11], Util::movementXZ(315, $hypotenuse));
        $this->assertSame([0, 15], Util::movementXZ(359, $hypotenuse));
    }

    public function testCubeDiagonalMovement(): void
    {
        $this->assertSame([1, 1, 1], Util::movementXYZ(45, 45, 1));
        $this->assertSame([8, 11, 8], Util::movementXYZ(45, 45, 15));
        $this->assertSame([2, 5, 5], Util::movementXYZ(22, 47, 7));
        $this->assertSame([2, -5, 5], Util::movementXYZ(22, -47, 7));
        $this->assertSame([48, 34, 11], Util::movementXYZ(77, 35, 60));
        $this->assertSame([2, 2, 5], Util::movementXYZ(18, 23, 5));
        $this->assertSame([0, -5, 0], Util::movementXYZ(45, -89, 5));
        $this->assertSame([1, -50, 1], Util::movementXYZ(45, -89, 50));
        $this->assertSame([6, -49, 6], Util::movementXYZ(45, -80, 50));
        $this->assertSame([10, -47, 14], Util::movementXYZ(35, -70, 50));
    }

    public function testMovementXYZWithFullVertical(): void
    {
        foreach (range(0, 36) as $i) {
            $this->assertSame([0, 100, 0], Util::movementXYZ($i * 10, 90, 100), "{$i}");
            $this->assertSame([0, -100, 0], Util::movementXYZ($i * 10, -90, 100), "{$i}");
        }
    }

    public function testRotatePointY(): void
    {
        $data = [
            0   => [-45, 32],
            45  => [-10, 67],
            65  => [11, 73],
            149 => [79, 18],
            192 => [69, -28],
            322 => [-47, -10],
        ];

        foreach ($data as $angle => $xz) {
            $this->assertSame($xz, Util::rotatePointY($angle, -45, 32, 15, 8));
        }

        $this->assertSame([830, 2013], Util::rotatePointY(111, 115, 478, 1000, 1000));
        $this->assertSame([10, 1000], Util::rotatePointY(45, 300, 300, 1000, 1000));
        $this->assertSame([300, 300], Util::rotatePointY(0, 300, 300, 1000, 1000));
        $this->assertSame([-39, 39], Util::rotatePointY(10, -45, 32, 0, 0));
        $this->assertSame([-31, 45], Util::rotatePointY(20, -45, 32, 0, 0));
        $this->assertSame([-9, 54], Util::rotatePointY(45, -45, 32, 0, 0));

        $this->assertSame([10, -2], Util::rotatePointY(63, 6, 8, 0, 0));
        $this->assertSame([5, -9], Util::rotatePointY(116, 6, 8, 0, 0));
        $this->assertSame([6, 8], Util::rotatePointY(-3, 6, 8, 0, 0));
        $this->assertSame([3, 10], Util::rotatePointY(-22, 6, 8, 0, 0));
        $this->assertSame([-4, 9], Util::rotatePointY(-63, 6, 8, 0, 0));
        $this->assertSame([5, 9], Util::rotatePointY(351, 6, 8, 0, 0));
        $this->assertSame([7, 7], Util::rotatePointY(-351, 6, 8, 0, 0));

        $this->assertSame([-2, 2], Util::rotatePointY(0, -2, 2, 2, 3));
        $this->assertSame([-2, 4], Util::rotatePointY(22, -2, 2, 2, 3));
        $this->assertSame([3, 7], Util::rotatePointY(123, -2, 2, 2, 3));
        $this->assertSame([4, 7], Util::rotatePointY(132, -2, 2, 2, 3));
        $this->assertSame([1, -1], Util::rotatePointY(298, -2, 2, 2, 3));
        $this->assertSame([-1, 6], Util::rotatePointY(-298, -2, 2, 2, 3));
        $this->assertSame([-2, 1], Util::rotatePointY(-14, -2, 2, 2, 3));
    }

    public function testRotatePointX(): void
    {
        $data = [
            0  => [20, 10],
            4  => [20, 11],
            76 => [5, 29],
            90 => [0, 30],
        ];

        foreach ($data as $angle => $xz) {
            $this->assertSame($xz, Util::rotatePointX($angle, 20, 10, 0, 10), "Angle: {$angle}");
        }

        $this->assertSame([0, 20], Util::rotatePointX(90, 20, 0));
        $this->assertSame([-2, -9], Util::rotatePointX(198, 5, 8));
        $this->assertSame([2, 9], Util::rotatePointX(22, 5, 8));
        $this->assertSame([2, 9], Util::rotatePointX(22, 5, 8));
        $this->assertSame([9, -2], Util::rotatePointX(287, 5, 8));
        $this->assertSame([4, 9], Util::rotatePointX(9, 5, 8));
        $this->assertSame([9, 4], Util::rotatePointX(-33, 5, 8, 0, 0));
        $this->assertSame([1, 9], Util::rotatePointX(-333, 5, 8, 0, 0));

        $this->assertSame([-2, 2], Util::rotatePointX(0, -2, 2, 2, 3));
        $this->assertSame([-1, 1], Util::rotatePointX(22, -2, 2, 2, 3));
        $this->assertSame([5, 0], Util::rotatePointX(123, -2, 2, 2, 3));
        $this->assertSame([5, 1], Util::rotatePointX(132, -2, 2, 2, 3));
        $this->assertSame([-1, 6], Util::rotatePointX(298, -2, 2, 2, 3));
        $this->assertSame([1, -1], Util::rotatePointX(-298, -2, 2, 2, 3));
        $this->assertSame([-2, 3], Util::rotatePointX(-14, -2, 2, 2, 3));
    }

    public function testRotatePointZ(): void
    {
        $data = [
            0   => [6, 5],
            6   => [6, 4],
            112 => [2, -7],
            254 => [-6, 4],
            322 => [2, 8],
        ];

        foreach ($data as $angle => $xz) {
            $this->assertSame($xz, Util::rotatePointZ($angle, 6, 5), "Angle: {$angle}");
        }

        $this->assertSame([8, 0], Util::rotatePointY(-322, 6, 5, 0, 0));
        $this->assertSame([8, 0], Util::rotatePointZ(-322, 6, 5, 0, 0));
        $this->assertSame([1, 8], Util::rotatePointY(-45, 6, 5, 0, 0));
        $this->assertSame([1, 8], Util::rotatePointZ(-45, 6, 5, 0, 0));

        $this->assertSame([-1, 16], Util::rotatePointZ(45, -12, 10, 0, 0));
        $this->assertSame([-15, 5], Util::rotatePointZ(-22, -12, 10, 0, 0));
        $this->assertSame([-7, 14], Util::rotatePointZ(22, -12, 10, 0, 0));

        $this->assertSame([-1, -2], Util::rotatePointZ(0, -1, -2, 3, 2));
        $this->assertSame([-2, 0], Util::rotatePointZ(22, -1, -2, 3, 2));
        $this->assertSame([2, 8], Util::rotatePointZ(123, -1, -2, 3, 2));
        $this->assertSame([3, 8], Util::rotatePointZ(132, -1, -2, 3, 2));
        $this->assertSame([5, -3], Util::rotatePointZ(298, -1, -2, 3, 2));
        $this->assertSame([-2, 4], Util::rotatePointZ(-298, -1, -2, 3, 2));
        $this->assertSame([0, -3], Util::rotatePointZ(-14, -1, -2, 3, 2));
    }

    public function testWorldAngleUsingMovement(): void
    {
        foreach (range(0, 359) as $h) {
            foreach (range(-89, 89) as $v) {
                $start = new Point(rand(-1000, 1000), rand(-1000, 1000), rand(-1000, 1000));
                $this->_testWorldAngleUsingMovement($start, $h, $v);
            }
        }
    }

    protected function _testWorldAngleUsingMovement(Point $start, float $h, float $v, int $distance = 9999): void
    {
        $end = $start->clone();
        $end->addFromArray(Util::movementXYZ($h, $v, $distance));
        [$actualH, $actualV] = Util::worldAngle($end, $start);
        if (is_float($actualH)) {
            $actualH = round($actualH);
        }
        if (is_float($actualV)) {
            $actualV = round($actualV);
        }
        $this->assertSame([$h, $v], [$actualH, $actualV], "{$start}, angle ({$h},{$v})");
    }

    public function testAngleNormalize(): void
    {
        $this->assertSame(0.0, Util::normalizeAngle(360.0));
        $this->assertSame(0.0, Util::normalizeAngle(720));
        $this->assertSame(347.8, Util::normalizeAngle(-12.20));
        $this->assertSame(190.3, Util::normalizeAngle(190.3));

        $this->assertSame(-90.0, Util::normalizeAngleVertical(-91));
        $this->assertSame(-90.0, Util::normalizeAngleVertical(-207.23));
        $this->assertSame(90.0, Util::normalizeAngleVertical(90.1));
        $this->assertSame(43.1, Util::normalizeAngleVertical(43.1));
    }

    public function testWorldAngle(): void
    {
        $this->assertSame([0.0, 0.0], Util::worldAngle(new Point(10, 10, 20), new Point(10, 10, 10)));
        $this->assertSame([180.0, 0.0], Util::worldAngle(new Point(10, 10, 20), new Point(10, 10, 30)));
        $this->assertSame([90.0, 0.0], Util::worldAngle(new Point(11, 10, 20), new Point(10, 10, 20)));
        $this->assertSame([270.0, 0.0], Util::worldAngle(new Point(9, 10, 20), new Point(10, 10, 20)));

        $this->assertSame([null, -90.0], Util::worldAngle(new Point(), new Point(0, 10, 0)));
        $this->assertSame([null, 90.0], Util::worldAngle(new Point(), new Point(0, -10, 0)));
        $this->assertSame([Util::normalizeAngle(-90.0), 0.0], Util::worldAngle(new Point(), new Point(10, 0, 0)));
        $this->assertSame([180.0, 0.0], Util::worldAngle(new Point(), new Point(0, 0, 10)));
        $this->assertSame([0.0, 0.0], Util::worldAngle(new Point(), new Point(0, 0, -10)));
        $this->assertNotSame([180.0, -90.0], Util::worldAngle(new Point(829, 773, 10), new Point(829, 940, 145)));
        $this->assertSame([90.0, 0.0], Util::worldAngle(new Point(10, 0, 0)));
        $this->assertSame([null, 90.0], Util::worldAngle(new Point(10, 4, 6), new Point(10, 2, 6)));

        $this->assertSame([90.0, 0.0], Util::worldAngle(new Point(10, 0, 0)));
        $this->assertSame([0.0, 0.0], Util::worldAngle(new Point(0, 0, 10)));
        $this->assertSame([45.0, 0.0], Util::worldAngle(new Point(5, 0, 5)));
        $this->assertSame([null, 0.0], Util::worldAngle(new Point(10, 2, 6), new Point(10, 2, 6)));
    }

    public function testLerp(): void
    {
        $this->assertSame(1, Util::lerpInt(1, 9, 0));
        $this->assertSame(4, Util::lerpInt(1, 9, 0.35));
        $this->assertSame(5, Util::lerpInt(1, 9, 0.5));
        $this->assertSame(9, Util::lerpInt(1, 9, 1));
        $this->assertSame(17, Util::lerpInt(1, 9, 2));
        $this->assertPositionSame(new Point(50, 70, 80), Util::lerpPoint(new Point(), new Point(100, 140, 160), .5));
        $this->assertPositionSame(new Point(10, 14, 22), Util::lerpPoint(new Point(2, 7, 9), new Point(11, 15, 23), .9));
    }

    public function testPoint(): void
    {
        $point = new Point();
        $point->addPart(1, 2, 3);
        $this->assertSame(1, $point->x);
        $this->assertSame(2, $point->y);
        $this->assertSame(3, $point->z);
        $this->assertSame([
            'x' => 2,
            'y' => 4,
        ], $point->to2D('zy')->add(-1, 2)->toArray());
        $point->setFromArray([1,3,2]);
        $this->assertTrue((new Point(1,3,2))->equals($point));
    }

    public function testGamePropertyUnknownFieldGet(): void
    {
        $prop = new GameProperty();
        $this->expectException(GameException::class);
        $this->assertSame(123, $prop->not_exists); // @phpstan-ignore-line
    }

    public function testGamePropertyUnknownFieldSet(): void
    {
        $prop = new GameProperty();
        $this->expectException(GameException::class);
        $prop->not_exists = 1; // @phpstan-ignore-line
    }

    public function testPointToPointDistance(): void
    {
        $this->assertSame(105, Util::distanceSquared(new Point(4, 1, -8), new Point(2, 2, 2)));
        $this->assertSame(578, Util::distanceSquared(new Point(11, -1, -8), new Point(-2, -4, 12)));
    }

    public function testPointToOriginDistance(): void
    {
        $this->assertSame(4, Util::distanceFromOrigin(new Point2D(4, 1)));
        $this->assertSame(4, Util::distanceFromOrigin(new Point2D(0, 4)));
    }

    public function testSmallestDeltaAngle(): void
    {
        $this->assertSame(4, Util::smallestDeltaAngle(0, 4));
        $this->assertSame(-4, Util::smallestDeltaAngle(8, 4));
        $this->assertSame(-135, Util::smallestDeltaAngle(-45, 180));
        $this->assertSame(45, Util::smallestDeltaAngle(90, 135));
        $this->assertSame(-45, Util::smallestDeltaAngle(135, 90));
        $this->assertSame(-136, Util::smallestDeltaAngle(-45, 179));
        $this->assertSame(24, Util::smallestDeltaAngle(-46, -22));
        $this->assertSame(68, Util::smallestDeltaAngle(-46, 22));
        $this->assertSame(68, Util::smallestDeltaAngle(-46, 22));
        $this->assertSame(92, Util::smallestDeltaAngle(290, 22));
        $this->assertSame(136, Util::smallestDeltaAngle(246, 22));
        $this->assertSame(1, Util::smallestDeltaAngle(720, 1));
        $this->assertSame(-1, Util::smallestDeltaAngle(1, 720));
        $this->assertSame(90, Util::smallestDeltaAngle(-46, 44));
    }

    public function testMovementXZOnlyGrowByMaxOfOneFromPrevious(): void
    {
        $prev = [0, 0];
        for ($distance = 1; $distance <= 105123; $distance++) {
            $test = Util::movementXZ(42, $distance);

            [$x, $y] = $test;
            if (false === ($prev[0] === $x || $prev[0] + 1 === $x)) {
                $this->fail("X grows more than 1 unit, from '{$prev[0]}' to '{$x}'");
            }
            if (false === ($prev[1] === $y || $prev[1] + 1 === $y)) {
                $this->fail("Y grows more than 1 unit, from '{$prev[0]}' to '{$y}'");
            }
            $prev = $test;
        }
        $this->assertSame([70341, 78122], $test);
    }

    public function testMovementXYZOnlyGrowByMaxOfOneFromPrevious(): void
    {
        $prev = [0, 0, 0];
        for ($distance = 1; $distance <= 105123; $distance++) {
            $test = Util::movementXYZ(42, 42, $distance);

            [$x, $y, $z] = $test;
            if (false === ($prev[0] === $x || $prev[0] + 1 === $x)) {
                $this->fail("X grows more than 1 unit, from '{$prev[0]}' to '{$x}'");
            }
            if (false === ($prev[1] === $y || $prev[1] + 1 === $y)) {
                $this->fail("Y grows more than 1 unit, from '{$prev[0]}' to '{$y}'");
            }
            if (false === ($prev[2] === $z || $prev[2] + 1 === $z)) {
                $this->fail("Z grows more than 1 unit, from '{$prev[0]}' to '{$z}'");
            }
            $prev = $test;
        }
        $this->assertSame([52274, 70341, 58056], $test);
    }

    public function testMovementXYZOnlyGrowByMaxOfOneFromPrevious1(): void
    {
        $prev = [0, 0, 0];
        for ($distance = 1; $distance <= 105123; $distance++) {
            $test = Util::movementXYZ(.1, .1, $distance);

            [$x, $y, $z] = $test;
            if (false === ($prev[0] === $x || $prev[0] + 1 === $x)) {
                $this->fail("X grows more than 1 unit, from '{$prev[0]}' to '{$x}'");
            }
            if (false === ($prev[1] === $y || $prev[1] + 1 === $y)) {
                $this->fail("Y grows more than 1 unit, from '{$prev[0]}' to '{$y}'");
            }
            if (false === ($prev[2] === $z || $prev[2] + 1 === $z)) {
                $this->fail("Z grows more than 1 unit, from '{$prev[0]}' to '{$z}'");
            }
            $prev = $test;
        }
        $this->assertSame([183, 183, 105123], $test);
    }

}
