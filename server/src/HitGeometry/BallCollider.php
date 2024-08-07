<?php

namespace cs\HitGeometry;

use cs\Core\GameException;
use cs\Core\Point;
use cs\Core\Util;
use cs\Core\World;

class BallCollider
{

    /** TODO REMOVE planes */
    private const PLANE_Y_DOWN = 0;
    private const PLANE_Y_UP = 1;
    private const PLANE_Z_FAR = 2;
    private const PLANE_Z_NEAR = 3;
    private const PLANE_X_LEFT = 4;
    private const PLANE_X_RIGHT = 5;
    private const ALL_PLANES = [
        self::PLANE_Y_DOWN,
        self::PLANE_Y_UP,
        self::PLANE_Z_FAR,
        self::PLANE_Z_NEAR,
        self::PLANE_X_LEFT,
        self::PLANE_X_RIGHT,
    ];

    /** @var array<int,array<int,array<int,int>>> */
    private array $hits = []; // todo remove or use just for resolution angle tests
    private bool $yGrowing;
    private Point $origin;
    private ?Point $lastExtremePosition = null;
    private Point $lastValidPosition;
    private readonly Point $firstHit;
    private readonly Point $resolutionPoint;
    private float $resolutionAngleHorizontal;
    private float $resolutionAngleVertical;

    public function __construct(
        protected World     $world,
        Point               $origin,
        public readonly int $radius,
    )
    {
        if ($this->radius <= 0) {
            throw new GameException("Radius needs to be bigger than zero"); // @codeCoverageIgnore
        }

        $this->origin = $origin->clone();
        $this->lastValidPosition = $origin->clone();
        $this->firstHit = new Point();
        $this->resolutionPoint = new Point();
    }

    public function hasCollision(Point $point, float $angleHorizontal, float $angleVertical): bool
    {
        if ($point->y < 0 || $point->x < 0 || $point->z < 0) {
            throw new GameException("Point '{$point}' cannot be lower than zero! Invalid angle resolution somewhere..."); // @codeCoverageIgnore
        }

        if ($this->lastExtremePosition === null) {
            $yGrowing = ($point->y <=> $this->origin->y);
            if ($yGrowing !== 0) {
                $this->yGrowing = ($yGrowing === 1);
                $this->lastExtremePosition = $point->clone();
            }
        }

        $this->hits = [];
        $firstHit = null;
        $radius = $this->radius;
        $radius2 = 2 * $this->radius;
        $candidate = new Point();
        if ($this->lastExtremePosition && Util::distanceSquared($this->lastExtremePosition, $point) > 9) {
            [$angleH, $angleV] = Util::worldAngle($point, $this->lastExtremePosition);
            $angleH = $angleH ?? 0;
        } else {
            $angleH = $angleHorizontal;
            $angleV = $angleVertical;
        }
        $isFullVertical = (abs($angleV) === 90.0);
        [$x, $y, $z] = $point->toFlatArray();
        [$xPrev, $yPrev, $zPrev] = $this->lastValidPosition->toFlatArray();

        $absX = 0;
        $absY = 0;
        $absZ = 0;
        $distance = 0;
        while ($distance < 9999) {
            if ($absY <= $radius && $yPrev !== $y && (
                    ($y < $yPrev && $y - $radius >= 0 && $this->world->findFloorSquare($candidate->set($x, $y - $radius, $z), $radius))
                    || ($y > $yPrev && $this->world->findFloorSquare($candidate->set($x, $y + $radius, $z), $radius))
                )
            ) {
                if ($firstHit === null) {
                    $firstHit = $this->firstHit->set($x, $y + ($y > $yPrev ? $radius : -$radius), $z);
                    $this->resolutionPoint->setFrom($firstHit);
                }
                $this->calculatePlaneHitsY($x, $y, $z, $y > $yPrev);
            }
            if (!$isFullVertical && $absX <= $radius && $xPrev !== $x && (
                    ($x > $xPrev && $this->world->checkXSideWallCollision($candidate->set($x + $radius, $y - $radius + 1, $z), $radius - 1, $radius))
                    || ($x < $xPrev && $this->world->checkXSideWallCollision($candidate->set($x - $radius, $y - $radius + 1, $z), $radius - 1, $radius))
                )
            ) {
                if ($firstHit === null) {
                    $firstHit = $this->firstHit->set($x, $y + ($y > $yPrev ? $radius : -$radius), $z);
                    $this->resolutionPoint->setFrom($firstHit);
                }
                $this->calculatePlaneHitsX($x, $y, $z, $x > $xPrev);
            }
            if (!$isFullVertical && $absZ <= $radius && $zPrev !== $z && (
                    ($z > $zPrev && $this->world->checkZSideWallCollision($candidate->set($x, $y - $radius + 1, $z + $radius), $radius - 1, $radius))
                    || ($z < $zPrev && $this->world->checkZSideWallCollision($candidate->set($x, $y - $radius + 1, $z - $radius), $radius - 1, $radius))
                )
            ) {
                if ($firstHit === null) {
                    $firstHit = $this->firstHit->set($x, $y + ($y > $yPrev ? $radius : -$radius), $z);
                    $this->resolutionPoint->setFrom($firstHit);
                }
                $this->calculatePlaneHitsZ($x, $y, $z, $z > $zPrev);
            }

            if ($distance === 0 && $this->hits === []) { // no collision
                if ($this->lastExtremePosition) {
                    if ($this->yGrowing && $point->y < $this->lastValidPosition->y) {
                        $this->lastExtremePosition->setFrom($point);
                        $this->yGrowing = false;
                    } elseif (!$this->yGrowing && $point->y > $this->lastValidPosition->y) {
                        $this->lastExtremePosition->setFrom($point);
                        $this->yGrowing = true;
                    }
                }
                $this->lastValidPosition->setFrom($point);
                return false;
            }

            if (($isFullVertical || fmod(abs($angleV), 90) < 1) && $absY >= $radius2) {
                break;
            }
            if ($angleV > -1 && $angleV < 1 && $absX >= $radius2 && $absZ >= $radius2) {
                break;
            }
            if (fmod($angleH, 90) < 1) {
                if ($angleV === 0.0 && ($absX >= $radius2 || $absZ >= $radius2)) {
                    break;
                }
                if ($absY >= $radius2 && ($absX >= $radius2 || $absZ >= $radius2)) {
                    break;
                }
            }
            if ($absX >= $radius && $absY >= $radius && $absZ >= $radius) {
                break;
            }

            $xPrev = $x;
            $yPrev = $y;
            $zPrev = $z;
            [$xR, $yR, $zR] = Util::movementXYZ($angleH, $angleV, ++$distance);
            $x = $point->x + $xR;
            $y = $point->y + $yR;
            $z = $point->z + $zR;
            $absX = abs($point->x - $x);
            $absY = abs($point->y - $y);
            $absZ = abs($point->z - $z);
            $x = max(-1, min($x, $point->x + $radius));
            $y = max(-1, min($y, $point->y + $radius));
            $z = max(-1, min($z, $point->z + $radius));
        }
        if ($this->hits === []) {
            throw new GameException("None of sensor was hit!"); // @codeCoverageIgnore
        }

        $validPosition = $this->lastValidPosition->clone();
        [$h, $v] = $this->getResolutionAngles($point, $validPosition, $angleH, $angleV);
        $this->lastExtremePosition = $point->clone();
        $this->yGrowing = ($v > 0);
        $this->resolutionAngleHorizontal = $h;
        $this->resolutionAngleVertical = $v;
        return true;
    }

    /**
     * @return float[] [horizontal, vertical]
     */
    private function getResolutionAngles(Point $firstTouch, Point $validPosition, float $h, float $v): array
    {
        // todo remove self::planes and just do some resolution point math normalization magic //return Util::worldAngle($this->firstHit, $this->resolutionPoint);

        $sums = [];
        foreach (self::ALL_PLANES as $planeKey) {
            $sums[$planeKey] = 0;
        }
        foreach ($this->hits as $planeKey => $data) {
            foreach ($data as $a => $data2) {
                foreach ($data2 as $b => $hitCount) {
                    $sums[$planeKey] += $hitCount;
                }
            }
        }

        $yDirection = $sums[self::PLANE_Y_DOWN] <=> $sums[self::PLANE_Y_UP];
        if ($yDirection !== 0) {
            $v = $yDirection === 1 ? abs($v) : -abs($v);
        }

        $xMax = max($sums[self::PLANE_X_LEFT], $sums[self::PLANE_X_RIGHT]);
        $zMax = max($sums[self::PLANE_Z_NEAR], $sums[self::PLANE_Z_FAR]);
        if (($xMax <=> $zMax) !== 0) {
            if ($xMax > $zMax) {
                $h = 360 - $h;
            } else {
                $h = 360 - $h + 180;
            }
        }

        return [Util::normalizeAngle($h), Util::normalizeAngleVertical($v)];
    }

    private function calculatePlaneHitsY(int $x, int $y, int $z, bool $yGrowing): void
    {
        $radius = $this->radius;
        $radius2 = 2 * $radius;
        $plane = ($yGrowing ? self::PLANE_Y_UP : self::PLANE_Y_DOWN);
        if (count($this->hits[$plane] ?? []) === $radius2 + 1) { // full collision on first layer, do not go deeper
            return;
        }

        $base = new Point($x - $radius, $y + ($yGrowing ? $radius : -$radius), $z - $radius);
        $floors = $this->world->getYFloors($base->y);
        if ($floors === []) {
            throw new GameException("Plane Y '{$y}' intersect but no floors found"); // @codeCoverageIgnore
        }

        $wasHit = false;
        $candidate = new Point();
        for ($x = 0; $x <= $radius2; $x++) {
            $candidate->setFrom($base);
            $candidate->x = $base->x + $x;

            for ($z = 0; $z <= $radius2; $z++) {
                $candidate->z = $base->z + $z;

                foreach ($floors as $floor) {
                    if (!$floor->intersect($candidate)) {
                        continue;
                    }

                    $this->resolutionPoint->addX($candidate->x <=> $this->firstHit->x);
                    $this->resolutionPoint->addY($candidate->y <=> $this->firstHit->y);
                    $this->resolutionPoint->addZ($candidate->z <=> $this->firstHit->z);
                    if (!isset($this->hits[$plane][$candidate->x][$candidate->z])) {
                        $this->hits[$plane][$candidate->x][$candidate->z] = 0;
                    }
                    $this->hits[$plane][$candidate->x][$candidate->z]++;
                    $wasHit = true;
                    break;
                }
            }
        }

        if ($wasHit === false) {
            throw new GameException("Plane Y '{$plane}' intersect [{$base}] but none of points"); // @codeCoverageIgnore
        }
    }

    private function calculatePlaneHitsX(int $x, int $y, int $z, bool $xGrowing): void
    {
        $radius = $this->radius;
        $radius2 = 2 * $radius;
        $plane = ($xGrowing ? self::PLANE_X_RIGHT : self::PLANE_X_LEFT);
        if (count($this->hits[$plane] ?? []) === $radius2 + 1) { // full collision on first layer, do not go deeper
            return;
        }

        $base = new Point($x + ($xGrowing ? $radius : -$radius), $y - $radius + 1, $z - $radius);
        $walls = $this->world->getXWalls($base->x);
        if ($walls === []) {
            throw new GameException("Plane X '{$x}' intersect but no walls found"); // @codeCoverageIgnore
        }

        $wasHit = false;
        $candidate = new Point();
        for ($z = 0; $z <= $radius2; $z++) {
            $candidate->setFrom($base);
            $candidate->z = $base->z + $z;

            for ($y = 0; $y < $radius2; $y++) {
                $candidate->y = $base->y + $y;

                foreach ($walls as $wall) {
                    if (!$wall->intersect($candidate)) {
                        continue;
                    }

                    $this->resolutionPoint->addX($candidate->x <=> $this->firstHit->x);
                    $this->resolutionPoint->addY($candidate->y <=> $this->firstHit->y);
                    $this->resolutionPoint->addZ($candidate->z <=> $this->firstHit->z);
                    if (!isset($this->hits[$plane][$candidate->z][$candidate->y])) {
                        $this->hits[$plane][$candidate->z][$candidate->y] = 0;
                    }
                    $this->hits[$plane][$candidate->z][$candidate->y]++;
                    $wasHit = true;
                    break;
                }
            }
        }

        if ($wasHit === false) {
            throw new GameException("Plane X '{$xGrowing}' intersect [{$base}] but none of points"); // @codeCoverageIgnore
        }
    }

    private function calculatePlaneHitsZ(int $x, int $y, int $z, bool $zGrowing): void
    {
        $radius = $this->radius;
        $radius2 = 2 * $radius;
        $plane = ($zGrowing ? self::PLANE_Z_FAR : self::PLANE_Z_NEAR);
        if (count($this->hits[$plane] ?? []) === $radius2 + 1) { // full collision on first layer, do not go deeper
            return;
        }

        $base = new Point($x - $radius, $y - $radius + 1, $z + ($zGrowing ? $radius : -$radius));
        $walls = $this->world->getZWalls($base->z);
        if ($walls === []) {
            throw new GameException("Plane Z '{$z}' intersect but no walls found"); // @codeCoverageIgnore
        }

        $wasHit = false;
        $candidate = new Point();
        for ($x = 0; $x <= $radius2; $x++) {
            $candidate->setFrom($base);
            $candidate->x = $base->x + $x;

            for ($y = 0; $y < $radius2; $y++) {
                $candidate->y = $base->y + $y;

                foreach ($walls as $wall) {
                    if (!$wall->intersect($candidate)) {
                        continue;
                    }

                    $this->resolutionPoint->addX($candidate->x <=> $this->firstHit->x);
                    $this->resolutionPoint->addY($candidate->y <=> $this->firstHit->y);
                    $this->resolutionPoint->addZ($candidate->z <=> $this->firstHit->z);
                    if (!isset($this->hits[$plane][$candidate->x][$candidate->y])) {
                        $this->hits[$plane][$candidate->x][$candidate->y] = 0;
                    }
                    $this->hits[$plane][$candidate->x][$candidate->y]++;
                    $wasHit = true;
                    break;
                }
            }
        }

        if ($wasHit === false) {
            throw new GameException("Plane Z '{$plane}' intersect [{$base}] but none of points"); // @codeCoverageIgnore
        }
    }

    public function getLastValidPosition(): Point
    {
        return $this->lastValidPosition->clone();
    }

    public function getResolutionAngleHorizontal(): float
    {
        return $this->resolutionAngleHorizontal;
    }

    public function getResolutionAngleVertical(): float
    {
        return $this->resolutionAngleVertical;
    }

}
