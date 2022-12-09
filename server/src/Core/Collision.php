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

    public static function pointWithCircle(int $pointX, int $pointY, int $circleCenterX, int $circleCenterY, int $circleRadius): bool
    {
        $a = $pointX - $circleCenterX;
        $b = $pointY - $circleCenterY;
        return (
            ($a * $a) + ($b * $b)
            <=
            $circleRadius * $circleRadius
        );
    }

    public static function pointWithSphere(Point $point, Point $sphereCenter, int $sphereRadius): bool
    {
        return Util::distanceSquared($point, $sphereCenter) <= $sphereRadius * $sphereRadius;
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

        $a = $cylinderBottomCenterA->x - $cylinderBottomCenterB->x;
        $b = $cylinderBottomCenterA->z - $cylinderBottomCenterB->z;
        $r = $cylinderRadiusA + $cylinderRadiusB;
        return (
            ($a * $a) + ($b * $b)
            <=
            $r * $r
        );
    }

    public static function pointWithCylinder(Point $point, Point $cylinderBottomCenter, int $cylinderRadius, int $cylinderHeight): bool
    {
        if ($point->y < $cylinderBottomCenter->y || $point->y > $cylinderBottomCenter->y + $cylinderHeight) {
            return false;
        }

        $a = $point->x - $cylinderBottomCenter->x;
        $b = $point->z - $cylinderBottomCenter->z;
        return (
            ($a * $a) + ($b * $b)
            <=
            $cylinderRadius * $cylinderRadius
        );
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

        $a = $circleX - $testX;
        $b = $circleY - $testY;
        return (
            ($a * $a) + ($b * $b)
            <=
            $circleRadius * $circleRadius
        );
    }

    public static function pointWithBox(Point $point, Box $box): bool
    {
        $base = $box->getBase();
        if ($point->x < $base->x || $point->x > $base->x + $box->widthX) {
            return false;
        }
        if ($point->y < $base->y || $point->y > $base->y + $box->heightY) {
            return false;
        }
        if ($point->z < $base->z || $point->z > $base->z + $box->depthZ) {
            return false;
        }

        return true;
    }

}
