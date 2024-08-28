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
    private bool $yGrowing;

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
        $this->yGrowing = $this->angleVertical > 0;
        $this->lastMoveY = $this->angleVertical <=> 0;
        $this->lastValidPosition = $origin->clone();
        $this->lastExtremePosition = $origin->clone();
    }

    public function hasCollision(Point $point): bool
    {
        $moveX = $point->x <=> $this->lastValidPosition->x;
        $moveY = $point->y <=> $this->lastValidPosition->y;
        $moveZ = $point->z <=> $this->lastValidPosition->z;

        $r = $this->radius;
        $isCollision = false;
        $this->candidate->set($point->x + $r * $moveX, $point->y + $r * $moveY, $point->z + $r * $moveZ);

        if ($moveY !== 0 && $this->world->findFloorSquare($this->candidate, $r)) {
            $this->angleVertical = Util::nearbyInt(Util::worldAngle($point, $this->lastExtremePosition)[1]);
            $this->angleVertical = $moveY > 0 ? -abs($this->angleVertical) : abs($this->angleVertical);
            $this->yGrowing = $this->angleVertical > 0;
            $isCollision = true;
        }

        if ($moveX !== 0 && $this->world->checkXSideWallCollision($this->candidate, 2 * $r, $r)) {
            $this->angleHorizontal = Util::normalizeAngle(360 - $this->angleHorizontal);
            $isCollision = true;
        } elseif ($moveZ !== 0 && $this->world->checkZSideWallCollision($this->candidate, 2 * $r, $r)) {
            $this->angleHorizontal = Util::normalizeAngle(360 - $this->angleHorizontal + 180);
            $isCollision = true;
        }

        if ($isCollision) {
            if ($moveY !== 0 && $this->yGrowing === false && $this->angleVertical > 0 && ($moveX !== 0 || $moveZ !== 0)) {
                $this->angleVertical = min(-10, Util::nearbyInt(Util::worldAngle($point, $this->lastExtremePosition)[1]));
            }
            $this->lastExtremePosition->setFrom($point);
            return true;
        }

        if ($moveY !== 0 && $this->lastMoveY !== $moveY) {
            $this->lastMoveY = $moveY;
            $this->lastExtremePosition->setFrom($point);
            $this->yGrowing = $moveY === 1;
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
