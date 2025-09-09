<?php

namespace cs\HitGeometry;

use cs\Core\GameException;
use cs\Core\Point;
use cs\Core\Util;
use cs\Core\World;

class BallCollider
{

    private Point $candidate;
    private Point $lastValidPosition;
    private Point $lastExtremePosition;
    private int $lastMoveY;

    private const int angleWorldPrecision = 1_000_000;
    private const int angleRoundDecimalPlaces = 2;

    public function __construct(
        protected World $world,
        Point           $origin,
        private int     $radius,
        private float   $angleHorizontal,
        private float   $angleVertical,
    )
    {
        if ($this->radius <= 0) {
            throw new GameException("Radius needs to be bigger than zero"); // @codeCoverageIgnore
        }

        $this->candidate = new Point();
        $this->lastMoveY = $this->angleVertical <=> 0;
        $this->lastValidPosition = $origin->clone();
        $this->lastExtremePosition = $origin->clone();
    }

    public function hasCollision(Point $point): ?bool
    {
        $moveX = $point->x <=> $this->lastValidPosition->x;
        $moveY = $point->y <=> $this->lastValidPosition->y;
        $moveZ = $point->z <=> $this->lastValidPosition->z;

        $r = $this->radius;
        $planeCollision = null;
        $this->candidate->set($point->x + $r * $moveX, $point->y + $r * $moveY, $point->z + $r * $moveZ);

        if ($moveY !== 0 && $planeCollision = $this->world->findFloorSquare($this->candidate, $r)) {
        } elseif ($moveX !== 0 && $planeCollision = $this->world->checkXSideWallCollision($this->candidate, 2 * $r, $r)) {
        } elseif ($moveZ !== 0 && $planeCollision = $this->world->checkZSideWallCollision($this->candidate, 2 * $r, $r)) {
        }

        if ($planeCollision !== null) {
            if ($planeCollision->getNormal()[1] !== 0) {
                $this->angleVertical = Util::worldAngle($point, $this->lastExtremePosition)[1];
            }
            $precision = self::angleWorldPrecision;
            $normalVec = $planeCollision->getNormalizedNormal($this->angleHorizontal, $this->angleVertical, $precision);
            $directionVec = Util::movementXYZ($this->angleHorizontal, $this->angleVertical, $precision);
            $doubleDotProduct = 2 * ($directionVec[0] * $normalVec[0] + $directionVec[1] * $normalVec[1] + $directionVec[2] * $normalVec[2]);
            if ($doubleDotProduct == 0) {
                return null;
            }
            $reflectionVec = [
                Util::nearbyInt($directionVec[0] - ($doubleDotProduct * $normalVec[0])),
                Util::nearbyInt($directionVec[1] - ($doubleDotProduct * $normalVec[1])),
                Util::nearbyInt($directionVec[2] - ($doubleDotProduct * $normalVec[2])),
            ];

            $this->candidate->setFromArray($reflectionVec);
            [$h, $v] = Util::worldAngle($this->candidate);
            $this->angleHorizontal = round($h ?? 0, self::angleRoundDecimalPlaces);
            $this->angleVertical = round($v, self::angleRoundDecimalPlaces);
            $this->lastExtremePosition->setFrom($point);
            return true;
        }

        if ($moveY !== 0 && $this->lastMoveY !== $moveY) {
            $this->lastMoveY = $moveY;
            $this->lastExtremePosition->setFrom($point);
        }

        $this->lastValidPosition->setFrom($point);
        return false;
    }

    public function getLastValidPosition(): Point
    {
        return $this->lastValidPosition->clone();
    }

    public function getLastExtremePosition(): Point
    {
        return $this->lastExtremePosition->clone();
    }

    public function getResolutionAngleHorizontal(): float
    {
        return $this->angleHorizontal;
    }

    public function getResolutionAngleVertical(): float
    {
        return $this->angleVertical;
    }

}
