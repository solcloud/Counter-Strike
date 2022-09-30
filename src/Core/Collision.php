<?php

namespace cs\Core;

class Collision
{

    public static function pointWithPlane(Point2D $center, Plane $plane): bool
    {
        $ppStart = $plane->getPoint2DStart();
        $ppEnd = $plane->getPoint2DEnd();

        return (
            $center->x >= $ppStart->x
            && $center->x <= $ppEnd->x
            && $center->y >= $ppStart->y
            && $center->y <= $ppEnd->y
        );
    }

    public static function pointWithCircle(Point2D $point, Point2D $circleCenter, int $circleRadius): bool
    {
        return (
            pow($point->x - $circleCenter->x, 2) + pow($point->y - $circleCenter->y, 2)
            <=
            pow($circleRadius, 2)
        );
    }

    public static function circleWithCircle(Point2D $circleCenterA, int $circleRadiusA, Point2D $circleCenterB, int $circleRadiusB): bool
    {
        return static::pointWithCircle($circleCenterA, $circleCenterB, $circleRadiusA + $circleRadiusB);
    }

    public static function pointWithSphere(Point $point, Point $sphereCenter, int $sphereRadius): bool
    {
        if ($point->y < $sphereCenter->y - $sphereRadius || $point->y > $sphereCenter->y + $sphereRadius) {
            return false;
        }

        return self::pointWithCircle($point->to2D('xz'), $sphereCenter->to2D('xz'), $sphereRadius);
    }

    public static function cylinderWithCylinder(
        Point $cylinderBottomCenterA,
        int   $cylinderRadiusA,
        int   $cylinderHeightA,
        Point $cylinderBottomCenterB,
        int   $cylinderRadiusB,
        int   $cylinderHeightB,
    ): bool
    {
        $yTop = min($cylinderBottomCenterA->y + $cylinderHeightA, $cylinderBottomCenterB->y + $cylinderHeightB);
        $yBottom = max($cylinderBottomCenterA->y, $cylinderBottomCenterB->y);
        if ($yTop - $yBottom < 0) {
            return false;
        }

        return self::pointWithCircle(
            $cylinderBottomCenterA->to2D('xz'),
            $cylinderBottomCenterB->to2D('xz'),
            $cylinderRadiusA + $cylinderRadiusB
        );
    }

    public static function pointWithCylinder(Point $point, Point $cylinderBottomCenter, int $cylinderRadius, int $cylinderHeight): bool
    {
        if ($point->y < $cylinderBottomCenter->y || $point->y > $cylinderBottomCenter->y + $cylinderHeight) {
            return false;
        }

        return self::pointWithCircle($point->to2D('xz'), $cylinderBottomCenter->to2D('xz'), $cylinderRadius);
    }

    public static function planeWithPlane(Point2D $pointA, int $planeWidthA, int $planeHeightA, Point2D $pointB, int $planeWidthB, int $planeHeightB): bool
    {
        return (
            $pointA->x + $planeWidthA >= $pointB->x
            &&
            $pointA->x <= $pointB->x + $planeWidthB
            &&
            $pointA->y + $planeHeightA >= $pointB->y
            &&
            $pointA->y <= $pointB->y + $planeHeightB
        );
    }

    public static function circleWithPlane(Point2D $circleCenter, int $circleRadius, Plane $plane): bool
    {
        if ($circleRadius === 0) {
            return static::pointWithPlane($circleCenter, $plane);
        }

        $planeStart = $plane->getPoint2DStart();
        $planeEnd = $plane->getPoint2DEnd();
        $circleX = $circleCenter->x;
        $circleY = $circleCenter->y;

        if ($circleX < $planeStart->x) {
            $testX = $planeStart->x;
        } elseif ($circleX > $planeEnd->x) {
            $testX = $planeEnd->x;
        } else {
            $testX = $circleX;
        }
        if ($circleY < $planeStart->y) {
            $testY = $planeStart->y;
        } elseif ($circleY > $planeEnd->y) {
            $testY = $planeEnd->y;
        } else {
            $testY = $circleY;
        }

        return (
            pow($circleX - $testX, 2) + pow($circleY - $testY, 2)
            <=
            pow($circleRadius, 2)
        );
    }

}
