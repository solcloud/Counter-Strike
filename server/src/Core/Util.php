<?php

namespace cs\Core;

use cs\Traits\Util\CosAngle;
use cs\Traits\Util\SinAngle;

final class Util
{
    use CosAngle;
    use SinAngle;

    public static int $TICK_RATE = 20;

    public static function millisecondsToFrames(int $timeMs): int
    {
        if ($timeMs < 0) {
            throw new GameException("Negative time given");
        }
        return (int)ceil($timeMs / self::$TICK_RATE);
    }

    /**
     * @return int 0..359
     */
    public static function normalizeAngle(int $angleDegree): int
    {
        $angleDegree = $angleDegree % 360;
        if ($angleDegree < 0) {
            $angleDegree = 360 + $angleDegree;
        }
        return $angleDegree;
    }

    /**
     * @return int[] [x, z]
     */
    public static function movementXZ(int $angleHorizontal, int $distance): array
    {
        return [
            (int)round(self::$sines[$angleHorizontal] * $distance),
            (int)round(self::$cosines[$angleHorizontal] * $distance),
        ];
    }

    /**
     * @return int[] [x, y, z]
     */
    public static function movementXYZ(int $angleHorizontal, int $angleVertical, int $distance): array
    {
        $y = $distance * self::$sines[$angleVertical];
        $z = (int)round(sqrt(pow($distance, 2) - pow($y, 2)));

        return [
            (int)round(self::$sines[$angleHorizontal] * $z),
            (int)round($y),
            (int)round(self::$cosines[$angleHorizontal] * $z),
        ];
    }

    public static function distanceFromOrigin(Point2D $point): int
    {
        return (int)round(hypot($point->x, $point->y));
    }

    public static function distanceSquared(Point $a, Point $b): int
    {
        return pow($a->x - $b->x, 2) + pow($a->y - $b->y, 2) + pow($a->z - $b->z, 2);
    }

    /**
     * @return int[] new [$x, $z]
     */
    public static function rotatePointY(int $angle, int $x, int $z, int $centerX = 0, int $centerZ = 0, bool $clockWise = true): array
    {
        $sin = self::$sines[$angle];
        $cos = self::$cosines[$angle];

        return [
            $centerX + (int)round($cos * ($x - $centerX) + $sin * ($z - $centerZ)),
            $centerZ + (int)round(($clockWise ? -1 : 1) * $sin * ($x - $centerX) + $cos * ($z - $centerZ)),
        ];
    }

}
