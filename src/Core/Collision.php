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
