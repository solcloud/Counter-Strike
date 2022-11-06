<?php

namespace Test\Unit;

use cs\Core\Player;
use cs\Core\Point;
use cs\Enum\Color;
use cs\HitGeometry\SphereHitBox;
use Test\BaseTest;

class HitBoxTest extends BaseTest
{

    public function testSphereWorldPointOrigin(): void
    {
        $y = 1;
        $sphere = new SphereHitBox(new Point(49, $y, 9), 38);
        $player = new Player(1, Color::GREEN, true);
        $data = [
            -90  => [-9, 49],
            0    => [49, 9],
            360  => [49, 9],
            1    => [49, 8],
            20   => [49, -8],
            30   => [47, -17],
            45   => [41, -28],
            57   => [34, -36],
            79   => [18, -46],
            90   => [9, -49],
            94   => [6, -50],
            131  => [-25, -43],
            169  => [-46, -18],
            180  => [-49, -9],
            191  => [-50, 1],
            232  => [-37, 33],
            240  => [-32, 38],
            -120 => [-32, 38],
            269  => [-10, 49],
            270  => [-9, 49],
            290  => [8, 49],
            322  => [33, 37],
            358  => [49, 11],
        ];

        foreach ($data as $angle => $xz) {
            $player->getSight()->lookHorizontal($angle);
            $this->assertPositionSame(new Point($xz[0], $y, $xz[1]), $sphere->calculateWorldCoordinate($player), " Angle: {$angle}");
        }
    }

    public function testSphereWorldPointCenter(): void
    {
        $sphere = new SphereHitBox(new Point(-45, 12, 32), 38);
        $player = new Player(1, Color::GREEN, true, new Point(15, -20, 8));
        $y = -8;
        $data = [
            45  => [6, 62],
            65  => [25, 62],
            149 => [70, 4],
            192 => [52, -33],
            322 => [-40, 6],
        ];

        foreach ($data as $angle => $xz) {
            $player->getSight()->lookHorizontal($angle);
            $this->assertPositionSame(new Point($xz[0], $y, $xz[1]), $sphere->calculateWorldCoordinate($player), " Angle: {$angle}");
        }
    }

    public function testSphereWorldPointCenterModifier(): void
    {
        $sphere = new SphereHitBox(new Point(-45, 12, 32), 38);
        $player = new Player(1, Color::GREEN, true);
        $player->getSight()->lookHorizontal(45);
        $this->assertPositionSame(new Point(6, -8, 62), $sphere->calculateWorldCoordinate($player, new Point(15, -20, 8)));
    }

    public function testSphereWorldCoordinate(): void
    {
        $sphere = new SphereHitBox(new Point(0, 0, 0), 30);
        $player = new Player(1, Color::GREEN, true);
        $player->getSight()->lookHorizontal(108);
        $this->assertPositionNotSame(new Point(499, 0, 3277), $sphere->calculateWorldCoordinate($player, new Point(1440, 0, 1457)));
    }

    public function testSphereHitBoxIntersect(): void
    {
        $sphere = new SphereHitBox(new Point(-45, 12, 32), 38);
        $player = new Player(1, Color::GREEN, true);

        $this->assertFalse($sphere->intersect($player, new Point(-10, -8, 67)));
        $player->getSight()->lookHorizontal(10);
        $this->assertFalse($sphere->intersect($player, new Point(-10, -8, 67)));
        $player->getSight()->lookHorizontal(20);
        $this->assertTrue($sphere->intersect($player, new Point(-10, -8, 67)));
    }

}
