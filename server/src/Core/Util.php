<?php

namespace cs\Core;

final class Util
{

    public static int $TICK_RATE = 20;

    public static function millisecondsToFrames(int $timeMs): int
    {
        if ($timeMs < 0) {
            throw new GameException("Negative time given");
        }
        return (int)ceil($timeMs / self::$TICK_RATE);
    }

    /**
     * @return float 0..359
     */
    public static function normalizeAngle(float $angleDegree): float
    {
        $angleDegree = fmod($angleDegree, 360);
        if ($angleDegree < 0) {
            $angleDegree = 360 + $angleDegree;
        }
        return $angleDegree;
    }

    /**
     * @return int[] [x, z]
     */
    public static function movementXZ(float $angleHorizontal, int $distance): array
    {
        return [
            self::nearbyInt(sin(deg2rad($angleHorizontal)) * $distance),
            self::nearbyInt(cos(deg2rad($angleHorizontal)) * $distance),
        ];
    }

    public static function smallestDeltaAngle(int $start, int $target): int
    {
        $a = (($start - $target) % 360 + 360) % 360;
        $b = (($target - $start) % 360 + 360) % 360;
        return ($a < $b ? -$a : $b);
    }

    public static function nearbyInt(float $float): int
    {
        return (int)($float > 0 ? $float + .5 : $float - .5);
    }

    /**
     * @return int[] [x, y, z]
     */
    public static function movementXYZ(float $angleHorizontal, float $angleVertical, int $distance): array
    {
        $y = $distance * sin(deg2rad($angleVertical));
        $z = self::nearbyInt(sqrt(($distance * $distance) - ($y * $y)));

        return [
            self::nearbyInt(sin(deg2rad($angleHorizontal)) * $z),
            (int)($y > 0 ? $y + .5 : $y - .5),
            self::nearbyInt(cos(deg2rad($angleHorizontal)) * $z),
        ];
    }

    public static function distanceFromOrigin(Point2D $point): int
    {
        return self::nearbyInt(hypot($point->x, $point->y));
    }

    public static function distanceSquared(Point $a, Point $b): int
    {
        $dx = $a->x - $b->x;
        $dy = $a->y - $b->y;
        $dz = $a->z - $b->z;
        return ($dx * $dx) + ($dy * $dy) + ($dz * $dz);
    }

    /**
     * @return int[] new [$x, $z]
     */
    public static function rotatePointY(float $angle, int $x, int $z, int $centerX = 0, int $centerZ = 0, bool $clockWise = true): array
    {
        $sin = sin(deg2rad($angle));
        $cos = cos(deg2rad($angle));

        return [
            $centerX + self::nearbyInt($cos * ($x - $centerX) + $sin * ($z - $centerZ)),
            $centerZ + self::nearbyInt(($clockWise ? -1 : 1) * $sin * ($x - $centerX) + $cos * ($z - $centerZ)),
        ];
    }

}
