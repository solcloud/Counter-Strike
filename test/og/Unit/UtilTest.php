<?php

namespace Test\Unit;

use cs\Core\PlayerCamera;
use cs\Core\Point;
use cs\Core\Point2D;
use cs\Core\Util;
use Test\BaseTest;

class UtilTest extends BaseTest
{

    public function testPlayerCameraAngles(): void
    {
        $camera = new PlayerCamera();

        $camera->lookHorizontal(45 - 90);
        $this->assertSame(360 - 45, $camera->getRotationHorizontal());
        $camera->lookHorizontal(15 - 90);
        $this->assertSame(360 - 75, $camera->getRotationHorizontal());
        $camera->lookHorizontal(-90);
        $this->assertSame(270, $camera->getRotationHorizontal());
        $camera->lookHorizontal(-91);
        $this->assertSame(269, $camera->getRotationHorizontal());
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

}
