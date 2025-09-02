<?php

namespace cs\Core;

final class PlaneBuilder
{
    /** @var array<string,Point> */
    private array $voxels = [];
    /** @var array{?float,float} */
    private array $voxelNormal;

    /** @return list<Plane> */
    public function create(Point $a, Point $b, Point $c, ?Point $d = null, ?float $jaggedness = null): array
    {
        if ($d === null) {
            return $this->fromTriangle($a, $b, $c, $jaggedness ?? 10.0);
        }

        return $this->fromQuad($a, $b, $c, $d, $jaggedness);
    }

    /** @return list<Plane> */
    public function fromTriangle(Point $a, Point $b, Point $c, float $voxelSizeDotThreshold): array
    {
        $voxelSize = (int)$voxelSizeDotThreshold;
        $voxelThreshold = max(1, intval(str_replace('0.', '', abs($voxelSizeDotThreshold - $voxelSize)))); // @phpstan-ignore argument.type
        if ($voxelSize > 0) {
            $voxelSize = max(1, $voxelSize);
            $matchSize = true;
        } else {
            $voxelSize = max(1, abs($voxelSize));
            $matchSize = false;
        }

        $planes = [];
        foreach ($this->voxelizeTriangle($a, $b, $c, $voxelSize, $voxelThreshold, $matchSize) as $voxelPoint) {
            $planes[] = (new Wall($voxelPoint, true, $voxelSize, $voxelSize))->setNormal($this->voxelNormal[0], $this->voxelNormal[1]);
            $planes[] = (new Wall($voxelPoint, false, $voxelSize, $voxelSize))->setNormal($this->voxelNormal[0], $this->voxelNormal[1]);
            $planes[] = (new Wall($voxelPoint->clone()->addX($voxelSize), false, $voxelSize, $voxelSize))->setNormal($this->voxelNormal[0], $this->voxelNormal[1]);
            $planes[] = (new Floor($voxelPoint->clone()->addY($voxelSize), $voxelSize, $voxelSize))->setNormal($this->voxelNormal[0], $this->voxelNormal[1]);
        }
        return $planes;
    }

    /** @return list<Plane> */
    public function fromQuad(Point $a, Point $b, Point $c, Point $d, ?float $jaggedness = null): array
    {
        $minX = min($a->x, $b->x, $c->x, $d->x);
        $maxX = max($a->x, $b->x, $c->x, $d->x);
        $minY = min($a->y, $b->y, $c->y, $d->y);
        $maxY = max($a->y, $b->y, $c->y, $d->y);
        $minZ = min($a->z, $b->z, $c->z, $d->z);
        $maxZ = max($a->z, $b->z, $c->z, $d->z);

        // Floor
        if ($minY === $maxY) {
            $sort = [];
            foreach ([$a, $b, $c, $d] as $point) {
                $sort[0][$point->x][] = $point;
                $sort[1][$point->z][] = $point;
            }

            // AABB floor
            if (count($sort[0]) === 2 && count($sort[1]) === 2 && count($sort[0][$minX]) === 2) {
                return [new Floor(new Point($minX, $minY, $minZ), $maxX - $minX, $maxZ - $minZ)];
            }

            GameException::notImplementedYet("skew floor?"); // @codeCoverageIgnore
        }

        // Wall
        if ($minX === $maxX || $minZ === $maxZ) {
            $widthOnXAxis = ($minZ === $maxZ);
            $width = ($widthOnXAxis ? $maxX - $minX : $maxZ - $minZ);

            if (false === (
                    ($a->y === $minY || $a->y === $maxY)
                    && ($b->y === $minY || $b->y === $maxY)
                    && ($c->y === $minY || $c->y === $maxY)
                    && ($d->y === $minY || $d->y === $maxY)
                )) {
                GameException::invalid('Skew wall Y coord');
            }

            return [new Wall(new Point($minX, $minY, $minZ), $widthOnXAxis, $width, $maxY - $minY)];
        }

        // Maybe rotated wall or stairs
        $wallRotated = [];
        $isWallRotatedCheck = [];
        $isStairs = [];
        $isRamp = [];
        foreach ([$a, $b, $c, $d] as $point) {
            $yIndex = ($point->y === $minY || $point->y === $maxY ? 0 : 1);
            $wallRotated[$point->x][] = $point;
            $isWallRotatedCheck[$yIndex]["{$point->x}|{$point->z}"][] = $point;
            $isStairs[$point->y][] = $point;
            $isRamp[$point->x][$point->z][] = $point;
        }

        // Rotated wall
        if (count($isWallRotatedCheck) === 1 && count($isWallRotatedCheck[0] ?? []) === 2) {
            $xCoordinates = array_keys($wallRotated);
            sort($xCoordinates, SORT_NUMERIC);
            $start = $wallRotated[$xCoordinates[0]][0];
            $end = $wallRotated[$xCoordinates[1]][0];

            return $this->rotatedWall($start->setY($minY), $end->setY($minY), $maxY - $minY, $jaggedness ?? 1.0);
        }

        // Ramp
        $minXKeys = array_keys($isRamp[$minX]);
        if (count($isRamp[$minX][$minZ] ?? []) === 1 && count($isRamp[$minX][$maxZ] ?? []) === 1
            && count($minXKeys) === 2 && isset($isRamp[$maxX][$minXKeys[0]]) && isset($isRamp[$maxX][$minXKeys[1]])
        ) {
            $min = $isRamp[$minX][$minZ][0];
            $max = $isRamp[$minX][$maxZ][0];

            $rampDirectionOnX = ($min->y === $max->y);
            $width = ($rampDirectionOnX ? $maxZ - $minZ : $maxX - $minX);
            if ($rampDirectionOnX) {
                $max->setX($maxX)->setZ($minZ);
            }

            $max->setY($min->y === $minY ? $maxY : $minY);
            return $this->ramp($min, $max, $width, $jaggedness ?? 1.0);
        }

        // Stairs maybe
        if (count($isStairs[$minY] ?? []) === 2 && count($isStairs[$maxY] ?? []) === 2) {
            [$baseA, $baseB] = $isStairs[$minY];
            [$topA, $topB] = $isStairs[$maxY];
            $onX = ($baseA->z === $baseB->z);
            $base = ($onX ? $baseA->x < $baseB->x : $baseA->z < $baseB->z) ? $baseA : $baseB;
            $top = ($onX ? $topA->x < $topB->x : $topA->z < $topB->z) ? $topA : $topB;
            $topSize = abs($onX ? $topA->x - $topB->x : $topA->z - $topB->z);

            // Stairs
            if ($topSize > 0) {
                $stepHeight = (int)($jaggedness ?? 15);

                return $this->stairs($base, $top, $topSize, $stepHeight, $onX);
            }
        }

        GameException::notImplementedYet(); // @codeCoverageIgnore
    }

    /** @return list<Wall> */
    private function rotatedWall(Point $start, Point $end, int $height, float $jaggedness): array
    {
        [$angleH, $angleV] = Util::worldAngle($end, $start);
        assert($angleV === 0.0 && $angleH !== null);
        $direction = [Util::directionX($angleH), Util::directionZ($angleH)];
        assert($direction[0] === 1 && abs($direction[1]) === 1);

        $walls = [];
        $previous = $start->clone();
        $points = Util::continuousPointsBetween($start, $end, $jaggedness);
        $widthOnXAxis = ($points[1][2] === $points[0][2]);

        $i = 0;
        $maxIteration = count($points);
        while (++$i <= $maxIteration) {
            $xyz = $points[$i] ?? null;

            if ($xyz !== null) {
                $hasSameBaseAxisAsPrevious = (!$widthOnXAxis && $previous->x === $xyz[0]) || ($widthOnXAxis && $previous->z === $xyz[2]);
                if ($hasSameBaseAxisAsPrevious) {
                    continue;
                }
            }

            $current = $points[$i - 1];
            $width = abs($widthOnXAxis ? $previous->x - $current[0] : $previous->z - $current[2]);
            $leftPoint = ($direction[1] === 1 || $widthOnXAxis ? $previous->clone() : new Point($current[0], $start->y, $current[2]));

            $walls[] = (new Wall($leftPoint, $widthOnXAxis, $width, $height))->setNormal(90 + $angleH, 0);
            $previous->set($current[0], $start->y, $current[2]);
            $widthOnXAxis = !$widthOnXAxis;
        }

        return $walls;
    }

    /** @return list<Plane> */
    private function ramp(Point $start, Point $end, int $width, float $jaggedness): array
    {
        [$angleH, $angleV] = Util::worldAngle($end, $start);
        if ($angleH === null || fmod($angleH, 90) !== 0.0) {
            GameException::invalid(); // @codeCoverageIgnore
        }
        assert($angleV !== 0.0);
        $normalH = $angleH + 90; // fixme: embrace jaggedness
        $normalV = $angleV + 90; // fixme: embrace jaggedness

        $planes = [];
        $previous = $start->clone();
        $points = Util::continuousPointsBetween($start, $end, $jaggedness);
        $isFloor = ($points[1][1] === $points[0][1]);
        $stairsGoingUp = ($angleV > 0);
        $wallWidthOnXAxis = (Util::directionX($angleH) === 0);

        $i = 0;
        $maxIteration = count($points);
        while (++$i <= $maxIteration) {
            $xyz = $points[$i] ?? null;

            if ($xyz !== null) {
                if ($isFloor) {
                    if ($previous->y === $xyz[1]) {
                        continue;
                    }
                } else {
                    $hasSameBaseAxisAsPrevious = (!$wallWidthOnXAxis && $previous->x === $xyz[0]) || ($wallWidthOnXAxis && $previous->z === $xyz[2]);
                    if ($hasSameBaseAxisAsPrevious) {
                        continue;
                    }
                }
            }

            $current = $points[$i - 1];
            if ($isFloor) {
                if ($wallWidthOnXAxis) {
                    $planes[] = (new Floor($previous->clone(), $width, $current[2] - $previous->z))->setNormal($normalH, $normalV);
                } else {
                    $planes[] = (new Floor($previous->clone(), $current[0] - $previous->x, $width))->setNormal($normalH, $normalV);
                }
            } else {
                $wallStart = ($stairsGoingUp ? $previous->clone() : new Point(...$current));
                $planes[] = (new Wall($wallStart, $wallWidthOnXAxis, $width, abs($current[1] - $previous->y)))->setNormal($normalH, $normalV);
            }

            $previous->set($current[0], $current[1], $current[2]);
            $isFloor = !$isFloor;
        }

        return $planes;
    }

    /** @return list<Plane> */
    private function stairs(Point $base, Point $top, int $topSize, int $stepHeight, bool $onX): array
    {
        assert($topSize > 0);
        assert($stepHeight > 0);
        $fullHeight = $top->y - $base->y;
        assert($fullHeight > 1 && $fullHeight > $stepHeight);
        $stepCount = (int)ceil($fullHeight / $stepHeight);

        $previous = $base->clone();
        $width = abs($top->x - $base->x);
        $depth = abs($top->z - $base->z);
        $stepWidth = (int)floor($width / $stepCount);
        $stepDepth = (int)floor($depth / $stepCount);

        [$angleH, $angleV] = Util::worldAngle($top, $base);
        assert($angleH !== null && $angleV > 0);

        $negativeZ = (Util::directionZ($angleH) === -1);
        $negativeX = (Util::directionX($angleH) === -1);
        if ($negativeX || $negativeZ) {
            $previous->addPart($negativeX ? -$width : 0, 0, $negativeZ ? -$depth : 0);
        }

        $planes = [];
        for ($step = 1; $step <= $stepCount; $step++) {
            if ($step === $stepCount) {
                $stepHeight = $top->y - $previous->y;
            }

            $box = new Box($previous->clone(), $onX ? 2 * $width + $topSize : $width, $stepHeight, $onX ? $depth : 2 * $depth + $topSize, Box::SIDE_ALL ^ Box::SIDE_BOTTOM);
            foreach (array_merge($box->getWalls(), $box->getFloors()) as $plane) {
                $planes[] = $plane;
            }

            $width = max(1, $width - $stepWidth);
            $depth = max(1, $depth - $stepDepth);
            $previous->addPart(
                $negativeX ? 0 : ($width === 1 ? 0 : $stepWidth),
                $stepHeight,
                $negativeZ ? 0 : ($depth === 1 ? 0 : $stepDepth),
            );
        }

        return $planes;
    }

    /**
     * @param positive-int $voxelSize
     * @param positive-int $voxelThreshold
     * @return list<Point>
     */
    private function voxelizeTriangle(Point $a, Point $b, Point $c, int $voxelSize, int $voxelThreshold, bool $matchTriangleSize): array
    {
        $u = [$b->x - $a->x, $b->y - $a->y, $b->z - $a->z];
        $v = [$c->x - $a->x, $c->y - $a->y, $c->z - $a->z];
        $this->voxelNormal = Util::worldAngle(new Point(
            ($u[1] * $v[2]) - ($u[2] * $v[1]),
            ($u[2] * $v[0]) - ($u[0] * $v[2]),
            ($u[0] * $v[1]) - ($u[1] * $v[0]),
        ));

        $this->voxels = [];
        $this->voxelizeLine($a, $b);
        $this->voxelizeLine($b, $c);
        $this->voxelizeLine($c, $a);

        $perAxis = [];
        foreach ($this->voxels as $voxel) {
            $perAxis[0][$voxel->x][$voxel->z] = $voxel;
            $perAxis[1][$voxel->y][$voxel->x] = $voxel;
            $perAxis[2][$voxel->z][$voxel->x] = $voxel;
        }
        foreach ($perAxis as $axisData) {
            foreach ($axisData as $data) {
                if (count($data) === 1) {
                    continue;
                }
                $axisKeys = array_keys($data);
                $this->voxelizeLine($data[min($axisKeys)], $data[max($axisKeys)]);
            }
        }

        $bbMin = new Point(
            min($a->x, $b->x, $c->x),
            min($a->y, $b->y, $c->y),
            min($a->z, $b->z, $c->z),
        );
        $bbMax = new Point(
            max($a->x, $b->x, $c->x) - ($matchTriangleSize ? $voxelSize : 0),
            max($a->y, $b->y, $c->y) - ($matchTriangleSize ? $voxelSize : 0),
            max($a->z, $b->z, $c->z) - ($matchTriangleSize ? $voxelSize : 0),
        );

        $data = [];
        for ($y = $bbMin->y; $y <= $bbMax->y; $y++) {
            for ($x = $bbMin->x; $x <= $bbMax->x; $x++) {
                for ($z = $bbMin->z; $z <= $bbMax->z; $z++) {
                    if (!isset($this->voxels["$x,$y,$z"])) {
                        continue;
                    }

                    $key = implode(',', [
                        (int)ceil(($x - $bbMin->x) / $voxelSize),
                        (int)ceil(($y - $bbMin->y) / $voxelSize),
                        (int)ceil(($z - $bbMin->z) / $voxelSize),
                    ]);
                    if (!isset($data[$key])) {
                        $data[$key] = 0;
                    }
                    $data[$key]++;
                }
            }
        }

        $startPoints = [];
        foreach ($data as $key => $hits) {
            if ($hits < $voxelThreshold) {
                continue;
            }

            $sizeIncrements = explode(',', $key);
            $startPoints[] = $bbMin->clone()->addPart(
                $voxelSize * (int)$sizeIncrements[0],
                $voxelSize * (int)$sizeIncrements[1],
                $voxelSize * (int)$sizeIncrements[2],
            );
        }
        return $startPoints;
    }

    private function voxelizeLine(Point $start, Point $end): void
    {
        $x = $start->x;
        $y = $start->y;
        $z = $start->z;

        [$steps, $xIncrement, $yIncrement, $zIncrement] = Util::stepsAndIncrements($start, $end);
        for ($step = 0; $step <= $steps; $step++) {
            $this->addVoxel((int)round($x), (int)round($y), (int)round($z));
            $x += $xIncrement;
            $y += $yIncrement;
            $z += $zIncrement;
        }
    }

    private function addVoxel(int $x, int $y, int $z): void
    {
        $key = "{$x},{$y},{$z}";
        if (isset($this->voxels[$key])) {
            return;
        }

        $this->voxels[$key] = new Point($x, $y, $z);
    }

}
