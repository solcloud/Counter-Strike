<?php

namespace cs\HitGeometry;

use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Setting;

class HitBoxLegs extends SphereGroupHitBox
{
    /** @var array<int,SphereHitBox[]> */
    private array $headHeightParts = [];

    public function __construct()
    {
        parent::__construct();
        $this->createLeftLimb();
        $this->createRightLimb();

        $steps = array_keys($this->headHeightParts);
        foreach (range(Setting::playerHeadHeightCrouch(), Setting::playerHeadHeightStand()) as $height) {
            if (isset($this->headHeightParts[$height])) {
                continue;
            }

            $key = null;
            $closest = 9999;
            foreach ($steps as $stepHeight) {
                $delta = abs($height - $stepHeight);
                if ($delta < $closest) {
                    $closest = $delta;
                    $key = $stepHeight;
                }
            }
            $this->headHeightParts[$height] = $this->headHeightParts[$key];
        }
    }

    public function getParts(Player $player): array
    {
        return $this->headHeightParts[$player->getHeadHeight()];
    }

    private function createLeftLimb(): void
    {
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-17, 5, 28), 6);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-17, 6, 38), 7);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-17, 6, 48), 6);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-17, 5, 56), 5);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-16, 14, 33), 5);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-16, 23, 35), 5);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-15, 31, 38), 6);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-14, 40, 44), 7);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-14, 39, 37), 7);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-14, 46, 40), 7);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-14, 49, 47), 7);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-12, 55, 46), 8);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-12, 60, 41), 8);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-12, 62, 34), 8);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-12, 53, 35), 8);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-11, 58, 25), 11);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-10, 57, 15), 11);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-5, 53, 4), 9);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(-7, 59, 0), 9);

        $this->headHeightParts[165][] = $this->createHitBox(new Point(-17, 4, 26), 6);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(-17, 5, 35), 7);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(-17, 4, 45), 6);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(-17, 4, 54), 5);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(-16, 14, 31), 5);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(-16, 22, 33), 5);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(-15, 31, 35), 6);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(-14, 39, 41), 7);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(-14, 39, 34), 7);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(-14, 45, 37), 7);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(-14, 49, 44), 7);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(-12, 54, 44), 8);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(-12, 60, 39), 8);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(-12, 62, 34), 8);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(-12, 53, 35), 8);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(-11, 65, 25), 11);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(-10, 73, 15), 11);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(-5, 73, 4), 9);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(-7, 78, 0), 9);

        $this->headHeightParts[175][] = $this->createHitBox(new Point(-16, 18, 15), 5);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-16, 26, 14), 7);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-14, 52, 19), 6);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-14, 50, 14), 6);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-14, 38, 16), 10);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-13, 60, 22), 7);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-12, 60, 17), 7);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-12, 72, 17), 9);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-10, 83, 11), 9);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-7, 91, 0), 9);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-11, 69, 12), 9);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-8, 81, 4), 8);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-8, 96, 4), 8);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-16, 8, 14), 5);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-17, 4, 12), 6);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-17, 3, 18), 6);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-17, 3, 25), 6);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-15, 2, 31), 4);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-20, 2, 29), 4);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-15, 3, 36), 3);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(-18, 3, 35), 3);

        $this->headHeightParts[190][] = $this->createHitBox(new Point(-16, 18, -5), 5);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-16, 26, -5), 7);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-14, 52, 0), 6);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-14, 50, -6), 6);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-14, 40, -6), 8);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-13, 60, 3), 7);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-12, 60, -2), 7);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-12, 70, 0), 9);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-11, 80, 0), 9);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-10, 91, 0), 9);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-8, 101, -3), 10);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-8, 104, 6), 8);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-8, 96, 4), 8);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-18, 34, -2), 4);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-18, 35, -10), 4);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-12, 33, -2), 4);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-12, 34, -10), 4);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-16, 11, -5), 5);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-17, 7, -7), 6);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-17, 6, -1), 6);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-17, 6, 6), 6);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-15, 5, 12), 4);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-20, 4, 10), 4);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-15, 6, 17), 3);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(-18, 6, 16), 3);
    }

    private function createRightLimb(): void
    {
        $this->headHeightParts[140][] = $this->createHitBox(new Point(17, 5, 28), 6);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(17, 6, 38), 7);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(17, 6, 48), 6);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(17, 5, 56), 5);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(16, 14, 33), 5);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(16, 23, 35), 5);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(15, 31, 38), 6);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(14, 40, 44), 7);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(14, 39, 37), 7);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(14, 46, 40), 7);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(14, 49, 47), 7);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(12, 55, 46), 8);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(12, 60, 41), 8);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(12, 62, 34), 8);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(12, 53, 35), 8);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(11, 58, 25), 11);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(10, 57, 15), 11);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(5, 53, 4), 9);
        $this->headHeightParts[140][] = $this->createHitBox(new Point(7, 59, 0), 9);

        $this->headHeightParts[165][] = $this->createHitBox(new Point(17, 4, 26), 6);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(17, 5, 35), 7);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(17, 4, 45), 6);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(17, 4, 54), 5);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(16, 14, 31), 5);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(16, 22, 33), 5);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(15, 31, 35), 6);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(14, 39, 41), 7);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(14, 39, 34), 7);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(14, 45, 37), 7);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(14, 49, 44), 7);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(12, 54, 44), 8);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(12, 60, 39), 8);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(12, 62, 34), 8);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(12, 53, 35), 8);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(11, 65, 25), 11);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(10, 73, 15), 11);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(5, 73, 4), 9);
        $this->headHeightParts[165][] = $this->createHitBox(new Point(7, 78, 0), 9);

        $this->headHeightParts[175][] = $this->createHitBox(new Point(16, 18, 15), 5);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(16, 26, 14), 7);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(14, 52, 19), 6);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(14, 50, 14), 6);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(14, 38, 16), 10);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(13, 60, 22), 7);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(12, 60, 17), 7);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(12, 72, 17), 9);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(10, 83, 11), 9);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(7, 91, 0), 9);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(11, 69, 12), 9);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(8, 81, 4), 8);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(8, 96, 4), 8);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(16, 8, 14), 5);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(17, 4, 12), 6);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(17, 3, 18), 6);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(17, 3, 25), 6);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(15, 2, 31), 4);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(20, 2, 29), 4);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(15, 3, 36), 3);
        $this->headHeightParts[175][] = $this->createHitBox(new Point(18, 3, 35), 3);

        $this->headHeightParts[190][] = $this->createHitBox(new Point(16, 18, -5), 5);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(16, 26, -5), 7);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(14, 52, 0), 6);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(14, 50, -6), 6);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(14, 40, -6), 8);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(13, 60, 3), 7);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(12, 60, -2), 7);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(12, 70, 0), 9);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(11, 80, 0), 9);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(10, 91, 0), 9);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(8, 101, -3), 10);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(8, 104, 6), 8);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(8, 96, 4), 8);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(18, 34, -2), 4);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(18, 35, -10), 4);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(12, 33, -2), 4);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(12, 34, -10), 4);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(16, 11, -5), 5);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(17, 7, -7), 6);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(17, 6, -1), 6);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(17, 6, 6), 6);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(15, 5, 12), 4);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(20, 4, 10), 4);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(15, 6, 17), 3);
        $this->headHeightParts[190][] = $this->createHitBox(new Point(18, 6, 16), 3);
    }

}
