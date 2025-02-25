<?php

namespace cs\Core;

final class PlaneBuilder
{
    /** @var array<string,Point> */
    private array $voxels = [];

    /** @return list<Plane> */
    public function create(Point $a, Point $b, Point $c, ?Point $d = null, float $jaggedness = 1.0): array
    {
        if ($d === null) {
            return $this->fromTriangle($a, $b, $c, $jaggedness);
        }

        return $this->fromQuad($a, $b, $c, $d, $jaggedness);
    }

    /** @return list<Plane> */
    public function fromTriangle(Point $a, Point $b, Point $c, float $voxelSizeDotThreshold = 1.0): array
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
            $planes[] = new Wall($voxelPoint, true, $voxelSize, $voxelSize);
            $planes[] = new Wall($voxelPoint, false, $voxelSize, $voxelSize);
            $planes[] = new Floor($voxelPoint->clone()->addY($voxelSize), $voxelSize, $voxelSize);
        }
        return $planes;
    }

    /** @return list<Plane> */
    public function fromQuad(Point $a, Point $b, Point $c, Point $d, float $jaggedness = 1.0): array
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

            return [new Wall(new Point($minX, $minY, $minZ), $widthOnXAxis, $width, $maxY - $minY)];
        }

        // Maybe rotated wall or stairs
        $wallRotated = [];
        $isWallRotatedCheck = [];
        $isStairs = [];
        foreach ([$a, $b, $c, $d] as $point) {
            $yIndex = ($point->y === $minY || $point->y === $maxY ? 0 : 1);
            $wallRotated[$point->x][] = $point;
            $isWallRotatedCheck[$yIndex]["{$point->x}|{$point->z}"][] = $point;
            $isStairs[$point->x][$point->z][] = $point;
        }

        // Rotated wall
        if (count($isWallRotatedCheck) === 1 && count($isWallRotatedCheck[0] ?? []) === 2) {
            $xCoordinates = array_keys($wallRotated);
            sort($xCoordinates, SORT_NUMERIC);
            $start = $wallRotated[$xCoordinates[0]][0];
            $end = $wallRotated[$xCoordinates[1]][0];

            return $this->rotatedWall($start->setY($minY), $end->setY($minY), $maxY - $minY, $jaggedness);
        }

        // Ramp
        if (count($isStairs[$minX][$minZ]) === 1 && count($isStairs[$minX][$maxZ]) === 1) {
            $min = $isStairs[$minX][$minZ][0];
            $max = $isStairs[$minX][$maxZ][0];

            $rampDirectionOnX = ($min->y === $max->y);
            $width = ($rampDirectionOnX ? $maxZ - $minZ : $maxX - $minX);
            if ($rampDirectionOnX) {
                $max->setX($maxX)->setZ($minZ);
            }

            $max->setY($min->y === $minY ? $maxY : $minY);
            return $this->ramp($min, $max, $width, $jaggedness);
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

            $walls[] = new Wall($leftPoint, $widthOnXAxis, $width, $height);
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
                    $planes[] = new Floor($previous->clone(), $width, $current[2] - $previous->z);
                } else {
                    $planes[] = new Floor($previous->clone(), $current[0] - $previous->x, $width);
                }
            } else {
                $wallStart = ($stairsGoingUp ? $previous->clone() : new Point(...$current));
                $planes[] = new Wall($wallStart, $wallWidthOnXAxis, $width, abs($current[1] - $previous->y));
            }

            $previous->set($current[0], $current[1], $current[2]);
            $isFloor = !$isFloor;
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
