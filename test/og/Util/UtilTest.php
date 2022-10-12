<?php

namespace Test\Util;

use cs\Core\GameProperty;
use cs\Core\Player;
use cs\Core\PlayerCamera;
use cs\Core\Point;
use cs\Core\Point2D;
use cs\Core\Util;
use cs\Enum\BuyMenuItem;
use cs\Weapon\RifleAk;
use Test\BaseTestCase;

class UtilTest extends BaseTestCase
{

    public function testMsToTickConstantTenOnTest(): void
    {
        $this->assertSame(1, Util::millisecondsToFrames(10));
        $this->assertSame(123, Util::millisecondsToFrames(1230));
        $this->assertSame(240, Util::millisecondsToFrames(RifleAk::reloadTimeMs));
    }

    public function testSkippingTicksPlayerSimulation(): void
    {
        $called = false;
        $playerCommands = [
            3,
            fn(Player $p) => $p->buyItem(BuyMenuItem::RIFLE_AK),
            function (Player $p) use (&$called): void {
                $this->assertSame($p->getEquippedItem()->getId(), RifleAk::class);
                $called = true;
            },
        ];

        $game = $this->simulateGame($playerCommands, [GameProperty::START_MONEY => 16000]);
        $this->assertSame(5, $game->getTickId());
        $this->assertTrue($called);
    }

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
        $this->assertSame([0, 15], Util::horizontalMovementXZ(0, $hypotenuse));
        $this->assertSame([11, 11], Util::horizontalMovementXZ(45, $hypotenuse));
        $this->assertSame([15, 0], Util::horizontalMovementXZ(90, $hypotenuse));
        $this->assertSame([11, -11], Util::horizontalMovementXZ(135, $hypotenuse));
        $this->assertSame([0, -15], Util::horizontalMovementXZ(180, $hypotenuse));
        $this->assertSame([-11, -11], Util::horizontalMovementXZ(225, $hypotenuse));
        $this->assertSame([-15, 0], Util::horizontalMovementXZ(270, $hypotenuse));
        $this->assertSame([-11, 11], Util::horizontalMovementXZ(315, $hypotenuse));
        $this->assertSame([0, 15], Util::horizontalMovementXZ(359, $hypotenuse));
    }

    public function testCubeDiagonalMovement(): void
    {
        $this->assertSame([1, 1, 1], Util::movementXYZ(45, 45, 1));
        $this->assertSame([11, 11, 11], Util::movementXYZ(45, 45, 15));
        $this->assertSame([3, 5, 6], Util::movementXYZ(22, 47, 7));
        $this->assertSame([3, -5, 6], Util::movementXYZ(22, -47, 7));
        $this->assertSame([58, 34, 13], Util::movementXYZ(77, 35, 60));
        $this->assertSame([2, 2, 5], Util::movementXYZ(18, 23, 5));
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

        $data = [
            111 => [830, 2013],
        ];

        foreach ($data as $angle => $xz) {
            $this->assertSame($xz, Util::rotatePointY($angle, 115, 478, 1000, 1000));
        }
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

}
