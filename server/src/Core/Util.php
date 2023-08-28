<?php

namespace cs\Core;

final class Util
{

    public const GRAVITY = 9.8;
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

    public static function normalizeAngleVertical(float $angleVerticalDegree): float
    {
        return max(-90.0, min(90.0, fmod($angleVerticalDegree, 360)));
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

    /**
     * @return null[]|float[] world angles [horizontal, vertical] in degree
     */
    public static function worldAngle(Point $point, Point $origin = new Point()): array
    {
        $cx = $point->x - $origin->x;
        $cy = $point->y - $origin->y;
        $cz = $point->z - $origin->z;

        $h = null;
        if ($cz !== 0 || $cx !== 0) {
            $h = fmod(450 - rad2deg(atan2($cz, $cx)), 360.0);
        }

        $d = Util::distanceSquared($origin, $point);
        if ($d === 0) {
            return [$h, null];
        }
        $v = rad2deg(asin(abs($cy) / sqrt($d)));
        return [$h, (($cy) >= 0 ? $v : -$v)];
    }

    public static function directionX(float $angleHorizontal): int
    {
        return ($angleHorizontal === 0.0 || $angleHorizontal === 180.0) ? 0 : ($angleHorizontal > 0 && $angleHorizontal < 180 ? +1 : -1);
    }

    public static function directionZ(float $angleHorizontal): int
    {
        return ($angleHorizontal === 90.0 || $angleHorizontal === 270.0) ? 0 : ($angleHorizontal > 270 || $angleHorizontal < 90 ? +1 : -1);
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

    public static function lerpInt(int $start, int $end, float $percentage): int
    {
        return (int)round($start + $percentage * ($end - $start));
    }

    public static function lerpPoint(Point $start, Point $end, float $percentage): Point
    {
        return new Point(
            self::lerpInt($start->x, $end->x, $percentage),
            self::lerpInt($start->y, $end->y, $percentage),
            self::lerpInt($start->z, $end->z, $percentage)
        );
    }

    /**
     * @return int[] new [$x, $z]
     */
    public static function rotatePointY(float $angle, int $x, int $z, int $centerX = 0, int $centerZ = 0): array
    {
        $sin = sin(deg2rad($angle));
        $cos = cos(deg2rad($angle));

        return [
            $centerX + self::nearbyInt($cos * ($x - $centerX) + $sin * ($z - $centerZ)),
            $centerZ + self::nearbyInt(-$sin * ($x - $centerX) + $cos * ($z - $centerZ)),
        ];
    }

    /**
     * @return int[] new [$y, $z]
     */
    public static function rotatePointX(float $angle, int $y, int $z, int $centerY = 0, int $centerZ = 0): array
    {
        $sin = sin(deg2rad($angle));
        $cos = cos(deg2rad($angle));

        return [
            $centerY + self::nearbyInt($cos * ($y - $centerY) - $sin * ($z - $centerZ)),
            $centerZ + self::nearbyInt($sin * ($y - $centerY) + $cos * ($z - $centerZ)),
        ];
    }

    /**
     * @return int[] new [$x, $y]
     */
    public static function rotatePointZ(float $angle, int $x, int $y, int $centerX = 0, int $centerY = 0): array
    {
        $sin = sin(deg2rad($angle));
        $cos = cos(deg2rad($angle));

        return [
            $centerX + self::nearbyInt($cos * ($x - $centerX) + $sin * ($y - $centerY)),
            $centerY + self::nearbyInt(-$sin * ($x - $centerX) + $cos * ($y - $centerY)),
        ];
    }

}
