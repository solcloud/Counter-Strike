<?php

namespace cs\Core;

class Collision
{

    public static function boxWithSegment(Point $boxMin, Point $boxMax, Point $segmentStart, Point $segmentEnd): bool
    {
        assert($boxMin->x < $boxMax->x);
        assert($boxMin->y < $boxMax->y);
        assert($boxMin->z < $boxMax->z);

        $tMin = 0;
        $tMax = INF;
        $aabbMin = [$boxMin->x, $boxMin->y, $boxMin->z];
        $aabbMax = [$boxMax->x, $boxMax->y, $boxMax->z];
        $lineStart = [$segmentStart->x, $segmentStart->y, $segmentStart->z];
        $lineEnd = [$segmentEnd->x, $segmentEnd->y, $segmentEnd->z];

        for ($i = 0; $i < 3; $i++) {
            $direction = $lineEnd[$i] - $lineStart[$i];
            if ($direction === 0) {
                if ($lineStart[$i] < $aabbMin[$i] || $lineStart[$i] > $aabbMax[$i]) {
                    return false;
                }
            } else {
                $inverseDirection = 1.0 / $direction;
                $t0 = ($aabbMin[$i] - $lineStart[$i]) * $inverseDirection;
                $t1 = ($aabbMax[$i] - $lineStart[$i]) * $inverseDirection;

                if ($inverseDirection < 0.0) {
                    [$t0, $t1] = [$t1, $t0];
                }

                $tMin = max($tMin, $t0);
                $tMax = min($tMax, $t1);

                if ($tMax <= $tMin) {
                    return false;
                }
            }
        }

        return true;
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
        $dx = $point->x - $sphereCenter->x;
        $dy = $point->y - $sphereCenter->y;
        $dz = $point->z - $sphereCenter->z;
        return (($dx * $dx) + ($dy * $dy) + ($dz * $dz)) <= ($sphereRadius * $sphereRadius);
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

    public static function planeWithPlane(Point2D $pointA, int $planeWidthA, int $planeHeightA, int $pointBx, int $pointBy, int $planeWidthB, int $planeHeightB): bool
    {
        return (
            $pointA->x + $planeWidthA >= $pointBx
            &&
            $pointA->x <= $pointBx + $planeWidthB
            &&
            $pointA->y + $planeHeightA >= $pointBy
            &&
            $pointA->y <= $pointBy + $planeHeightB
        );
    }

    public static function circleWithRect(
        int $circleCenterX, int $circleCenterY, int $circleRadius,
        int $rectStartX, int $rectEndX,
        int $rectStartY, int $rectEndY
    ): bool
    {
        $testX = $circleCenterX;
        if ($circleCenterX < $rectStartX) {
            $testX = $rectStartX;
        } elseif ($circleCenterX > $rectEndX) {
            $testX = $rectEndX;
        }
        $testY = $circleCenterY;
        if ($circleCenterY < $rectStartY) {
            $testY = $rectStartY;
        } elseif ($circleCenterY > $rectEndY) {
            $testY = $rectEndY;
        }

        $a = $circleCenterX - $testX;
        $b = $circleCenterY - $testY;
        return (
            ($a * $a) + ($b * $b)
            <=
            $circleRadius * $circleRadius
        );
    }

    public static function circleCenterToPlaneBoundaryDistanceSquared(int $circleCenterX, int $circleCenterY, Plane $plane): int
    {
        $planeStart = $plane->getPoint2DStart();
        $planeEnd = $plane->getPoint2DEnd();

        if ($circleCenterX < $planeStart->x) {
            $testX = $planeStart->x;
        } elseif ($circleCenterX > $planeEnd->x) {
            $testX = $planeEnd->x;
        } else {
            $testX = $circleCenterX;
        }
        if ($circleCenterY < $planeStart->y) {
            $testY = $planeStart->y;
        } elseif ($circleCenterY > $planeEnd->y) {
            $testY = $planeEnd->y;
        } else {
            $testY = $circleCenterY;
        }

        $a = $circleCenterX - $testX;
        $b = $circleCenterY - $testY;
        return (($a * $a) + ($b * $b));
    }

    public static function circleWithPlane(Point2D $circleCenter, int $circleRadius, Plane $plane): bool
    {
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

    public static function pointWithBoxBoundary(Point $point, Point $boxMin, Point $boxMax): bool
    {
        assert($boxMin->x < $boxMax->x);
        assert($boxMin->y < $boxMax->y);
        assert($boxMin->z < $boxMax->z);

        if ($point->y > $boxMax->y || $point->y < $boxMin->y) {
            return false;
        }
        if ($point->x > $boxMax->x || $point->x < $boxMin->x) {
            return false;
        }
        if ($point->z > $boxMax->z || $point->z < $boxMin->z) {
            return false;
        }

        return true;
    }

    public static function boxWithBox(Point $boundaryAMin, Point $boundaryAMax, Point $boundaryBMin, Point $boundaryBMax): bool
    {
        assert($boundaryAMin->x < $boundaryAMax->x);
        assert($boundaryAMin->y < $boundaryAMax->y);
        assert($boundaryAMin->z < $boundaryAMax->z);

        assert($boundaryBMin->x < $boundaryBMax->x);
        assert($boundaryBMin->y < $boundaryBMax->y);
        assert($boundaryBMin->z < $boundaryBMax->z);

        if ($boundaryAMin->x > $boundaryBMax->x || $boundaryBMin->x > $boundaryAMax->x) {
            return false;
        }
        if ($boundaryAMin->y > $boundaryBMax->y || $boundaryBMin->y > $boundaryAMax->y) {
            return false;
        }
        if ($boundaryAMin->z > $boundaryBMax->z || $boundaryBMin->z > $boundaryAMax->z) {
            return false;
        }
        return true;
    }

}
