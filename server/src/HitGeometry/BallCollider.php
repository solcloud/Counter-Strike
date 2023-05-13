<?php

namespace cs\HitGeometry;

use cs\Core\GameException;
use cs\Core\Point;
use cs\Core\Util;
use cs\Core\World;

class BallCollider
{

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
    private array $hits = [];
    /** @var array<int,int[]> */
    private array $lastPositions = [];
    private int $backtrackCount = 0;
    private bool $yGrowing;
    private ?Point $lastExtremePosition = null;
    private ?Point $lastResolution = null;

    public function __construct(
        protected World     $world,
        private Point       $origin,
        public readonly int $radius,
    )
    {
        if ($this->radius <= 0) {
            throw new GameException("Radius needs to be bigger than zero");
        }
    }

    /**
     * @return null|array{0: Point, 1: ?float, 2: ?float}
     */
    public function resolveCollision(Point $point): ?array
    {
        array_unshift($this->lastPositions, [$point->x, $point->y, $point->z]);

        // fixme players collision, molotov-smoke collision
        if ($this->world->findFloor($point)) {
            return $this->getResolution();
        }
        if ($this->world->isWallAt($point)) {
            return $this->getResolution();
        }

        if ($this->lastExtremePosition === null) {
            $yGrowing = ($point->y <=> $this->origin->y);
            if ($yGrowing !== 0) {
                $this->yGrowing = ($yGrowing === 1);
                $this->lastExtremePosition = $this->origin->clone();
            }
        } else {
            if ($this->yGrowing && $point->y < $this->lastExtremePosition->y) {
                $this->lastExtremePosition->setFrom($point);
                $this->yGrowing = false;
            } elseif (!$this->yGrowing && $point->y > $this->lastExtremePosition->y) {
                $this->lastExtremePosition->setFrom($point);
                $this->yGrowing = true;
            }
        }

        return null;
    }

    /**
     * @return array{0: Point, 1: ?float, 2: ?float}
     */
    private function getResolution(): array
    {
        $this->hits = [];
        $this->backtrackCount = 0;
        $firstHit = null;
        $origin = $this->lastExtremePosition ?? $this->origin;
        $radius = $this->radius;

        $centerHit = array_shift($this->lastPositions);
        if ($centerHit === null) {
            throw new GameException("Should not be here");
        }
        $centerHit = new Point($centerHit[0], $centerHit[1], $centerHit[2]);
        if ($centerHit->equals($origin)) {
            throw new GameException("Instant collision on new origin?");
        }

        $candidate = new Point();
        [$angleH, $angleV] = Util::worldAngle($centerHit, $origin);
        $isFullVertical = (abs($angleV) === 90.0);
        foreach ($this->lastPositions as $pos) {
            $this->backtrackCount++;
            $collision = false;
            [$x, $y, $z] = $pos;

            if (($angleV < 0 && $y - $radius >= 0 && $this->world->findFloorSquare($candidate->set($x, $y - $radius, $z), $radius))
                || ($angleV > 0 && $this->world->findFloorSquare($candidate->set($x, $y + $radius, $z), $radius))
            ) {
                $collision = true;
                $this->calculatePlaneHitsY($x, $y, $z, $angleV);
            }

            if (!$isFullVertical && (
                    ($angleH > 0 && $angleH < 180 && $this->world->checkXSideWallCollision($candidate->set($x + $radius, $y - $radius + 1, $z), $radius - 1, $radius))
                    || ($angleH > 180 && $angleH < 360 && $this->world->checkXSideWallCollision($candidate->set($x - $radius, $y - $radius + 1, $z), $radius - 1, $radius))
                )
            ) {
                $collision = true;
                $this->calculatePlaneHitsX($x, $y, $z, $angleH);
            }

            if (!$isFullVertical && (
                    (($angleH > 270 || $angleH < 90) && $this->world->checkZSideWallCollision($candidate->set($x, $y - $radius + 1, $z + $radius), $radius - 1, $radius))
                    || ($angleH > 90 && $angleH < 270 && $this->world->checkZSideWallCollision($candidate->set($x, $y - $radius + 1, $z - $radius), $radius - 1, $radius))
                )
            ) {
                $collision = true;
                $this->calculatePlaneHitsZ($x, $y, $z, $angleH);
            }

            if ($firstHit && abs($firstHit[0] - $x) >= $radius && abs($firstHit[1] - $y) >= $radius && abs($firstHit[2] - $z) >= $radius) {
                break;
            }
            if (!$collision) {
                continue;
            }

            $firstHit = $pos;
        }

        if (null === $firstHit) {
            if ($this->lastResolution) {
                return [$this->lastResolution, null, null];
            }
            throw new GameException("NONE of sensor was hit. Already inside something from start?");
        }

        $firstHit = new Point($firstHit[0], $firstHit[1], $firstHit[2]);
        [$h, $v] = $this->getResolutionAngles($centerHit, $firstHit, $angleH, $angleV);
        $this->lastPositions = [];
        $this->lastResolution = $firstHit->clone();
        $this->lastExtremePosition = $firstHit->clone();
        $this->yGrowing = ($v > 0);
        return [$firstHit, $h, $v];
    }

    /**
     * @return float[] [horizontal, vertical]
     */
    private function getResolutionAngles(Point $centerHit, Point $firstHit, float $h, float $v): array
    {
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
            $v = -$v;
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

        $quarter = [1 => 0, 0, 0, 0];
        foreach ([self::PLANE_Y_DOWN, self::PLANE_Y_UP] as $planeKey) {
            foreach (($this->hits[$planeKey] ?? []) as $x => $data2) {
                $xDirection = ($firstHit->x <=> $x);

                foreach ($data2 as $z => $hitCount) {
                    $zDirection = ($firstHit->z <=> $z);
                    if ($zDirection === 1) {
                        if ($xDirection === 1) {
                            $quarter[1] += $hitCount;
                        } elseif ($xDirection === -1) {
                            $quarter[3] += $hitCount;
                        }
                    } elseif ($zDirection === -1) {
                        if ($xDirection === 1) {
                            $quarter[2] += $hitCount;
                        } elseif ($xDirection === -1) {
                            $quarter[4] += $hitCount;
                        }
                    }
                }
            }
        }

        $sum = array_sum($quarter);
        if ($sum > 16) {
            $sumXLeft = ($quarter[3] + $quarter[4]);
            $sumXRight = ($quarter[1] + $quarter[2]);
            $sumZFar = ($quarter[3] + $quarter[1]);
            $sumZNear = ($quarter[4] + $quarter[2]);

            $value = 0;
            $delta = 0;
            if ($sumXLeft > $sumXRight) {
                if ($sumZFar > $sumZNear) {
                    $delta = Util::smallestDeltaAngle((int)$h, 135);
                    $value = $quarter[3];
                } elseif ($sumZNear > $sumZFar) {
                    $delta = Util::smallestDeltaAngle((int)$h, 45);
                    $value = $quarter[4];
                }
            } elseif ($sumXRight > $sumXLeft) {
                if ($sumZFar > $sumZNear) {
                    $delta = Util::smallestDeltaAngle((int)$h, 225);
                    $value = $quarter[1];
                } elseif ($sumZNear > $sumZFar) {
                    $delta = Util::smallestDeltaAngle((int)$h, 315);
                    $value = $quarter[2];
                }
            }

            if ($value > 0 && $value > 1.2 * $sum / 4) {
                $max = $this->backtrackCount * $this->radius * $this->radius;
                $h += 0.5 * $delta * $value / $max;
            }
        }

        // todo some plane quarter? sums magic simplification to single line planes and do percentage offset angle math :D
        return [Util::normalizeAngle($h), Util::normalizeAngleVertical($v)];
    }

    private function calculatePlaneHitsY(int $x, int $y, int $z, float $angleVertical): void
    {
        $radius = $this->radius;
        $base = new Point($x - $radius, $y + ($angleVertical > 0 ? $radius : -$radius), $z - $radius);
        $floors = $this->world->getYFloors($base->y);
        if ($floors === []) {
            throw new GameException("Plane Y '{$angleVertical}' intersect but no floors found");
        }

        $wasHit = false;
        $radius2 = 2 * $radius;
        $candidate = new Point();
        $plane = ($angleVertical > 0 ? self::PLANE_Y_UP : self::PLANE_Y_DOWN);
        for ($x = 0; $x <= $radius2; $x++) {
            $candidate->setFrom($base);
            $candidate->x = $base->x + $x;

            for ($z = 0; $z <= $radius2; $z++) {
                $candidate->z = $base->z + $z;

                foreach ($floors as $floor) {
                    if (!$floor->intersect($candidate)) {
                        continue;
                    }

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
            throw new GameException("Plane Y '{$angleVertical}' intersect but none of points");
        }
    }

    private function calculatePlaneHitsX(int $x, int $y, int $z, float $angleHorizontal): void
    {
        $radius = $this->radius;
        $base = new Point($x + ($angleHorizontal > 0 && $angleHorizontal < 180 ? $radius : -$radius), $y - $radius, $z - $radius);
        $walls = $this->world->getXWalls($base->x);
        if ($walls === []) {
            throw new GameException("Plane X '{$angleHorizontal}' intersect but no walls found");
        }

        $wasHit = false;
        $radius2 = 2 * $radius;
        $candidate = new Point();
        $plane = ($angleHorizontal > 0 && $angleHorizontal < 180 ? self::PLANE_X_RIGHT : self::PLANE_X_LEFT);
        for ($z = 0; $z <= $radius2; $z++) {
            $candidate->setFrom($base);
            $candidate->z = $base->z + $z;

            for ($y = 1; $y < $radius2; $y++) {
                $candidate->y = $base->y + $y;

                foreach ($walls as $wall) {
                    if (!$wall->intersect($candidate)) {
                        continue;
                    }

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
            throw new GameException("Plane X '{$angleHorizontal}' intersect but none of points");
        }
    }

    private function calculatePlaneHitsZ(int $x, int $y, int $z, float $angleHorizontal): void
    {
        $radius = $this->radius;
        $base = new Point($x - $radius, $y - $radius, $z + ($angleHorizontal > 90 && $angleHorizontal < 270 ? -$radius : $radius));
        $walls = $this->world->getZWalls($base->z);
        if ($walls === []) {
            throw new GameException("Plane Z '{$angleHorizontal}' intersect but no walls found");
        }

        $wasHit = false;
        $radius2 = 2 * $radius;
        $candidate = new Point();
        $plane = ($angleHorizontal > 90 && $angleHorizontal < 270 ? self::PLANE_Z_NEAR : self::PLANE_Z_FAR);
        for ($x = 0; $x <= $radius2; $x++) {
            $candidate->setFrom($base);
            $candidate->x = $base->x + $x;

            for ($y = 1; $y < $radius2; $y++) {
                $candidate->y = $base->y + $y;

                foreach ($walls as $wall) {
                    if (!$wall->intersect($candidate)) {
                        continue;
                    }

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
            throw new GameException("Plane Z '{$angleHorizontal}' intersect but none of points");
        }
    }

}
