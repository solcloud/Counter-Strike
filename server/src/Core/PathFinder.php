<?php

namespace cs\Core;

use GraphPHP\Edge\DirectedEdge;
use GraphPHP\Node\Node;
use SplQueue;

final class PathFinder
{
    private Graph $graph;
    /** @var array<string,bool> */
    private array $visited = [];
    private readonly int $obstacleOvercomeHeight;
    /** @var array<int,array{int,int,int}> */
    private static array $moves = [
        90 => [+1, +0, +0],
        0 => [+0, +0, +1],
        270 => [-1, +0, +0],
        180 => [+0, +0, -1],
    ];

    public function __construct(private World $world, private NavigationMesh $navigationMesh)
    {
        $this->graph = new Graph();
        $this->obstacleOvercomeHeight = Setting::playerObstacleOvercomeHeight();
    }

    private function canFullyMoveTo(Point $candidate, int $angle, int $targetDistance, int $radius, int $height): bool
    {
        if ($angle % 90 !== 0) {
            GameException::notImplementedYet(); // @codeCoverageIgnore
        }

        $looseFloor = false;
        for ($distance = 1; $distance <= $targetDistance; $distance++) {
            $candidate->addPart(...self::$moves[$angle]);
            if (!$this->canMoveTo($candidate, $angle, $radius)) {
                return false;
            }

            if (!$looseFloor && !$this->world->findFloorSquare($candidate, $radius)) {
                $looseFloor = true;
            }
        }

        if (!$looseFloor) {
            return true;
        }

        $fallCandidate = $candidate->clone();
        foreach (range(1, 3 * $height) as $i) {
            $fallCandidate->addY(-1);
            if ($this->world->findFloorSquare($fallCandidate, $radius)) {
                $candidate->setY($fallCandidate->y); // side effect
                return true;
            }
        }

        return false;
    }

    private function canMoveTo(Point $start, int $angle, int $radius): bool
    {
        $maxWallCeiling = $start->y + $this->obstacleOvercomeHeight;
        $xWallMaxHeight = 0;
        if ($angle === 90 || $angle === 270) {
            $baseX = $start->clone()->addX(($angle === 90) ? $radius : -$radius);
            $xWallMaxHeight = $this->world->findHighestWall($baseX, $this->navigationMesh->colliderHeight, $radius, $maxWallCeiling, true);
        }
        $zWallMaxHeight = 0;
        if ($angle === 0 || $angle === 180) {
            $baseZ = $start->clone()->addZ(($angle === 0) ? $radius : -$radius);
            $zWallMaxHeight = $this->world->findHighestWall($baseZ, $this->navigationMesh->colliderHeight, $radius, $maxWallCeiling, false);
        }
        if ($xWallMaxHeight === 0 && $zWallMaxHeight === 0) { // no walls
            return true;
        }

        // Try step over ONE low height wall
        $highestWallCeiling = null;
        if ($xWallMaxHeight === 0 && $zWallMaxHeight <= $maxWallCeiling) {
            $highestWallCeiling = $zWallMaxHeight;
        } elseif ($zWallMaxHeight === 0 && $xWallMaxHeight <= $maxWallCeiling) {
            $highestWallCeiling = $xWallMaxHeight;
        }
        if ($highestWallCeiling === null) {
            return false;
        }

        $floor = $this->world->findFloor($start->clone()->setY($highestWallCeiling), $radius);
        if ($floor) {
            $start->setY($floor->getY()); // side effect
            return true;
        }

        return false;
    }

    public function findTile(Point $pointOnFloor, int $radius): ?Point
    {
        $floorNavmeshPoint = $pointOnFloor->clone();
        $this->convertToNavMeshNode($floorNavmeshPoint);
        if ($this->navigationMesh->has($floorNavmeshPoint->hash())) {
            return $floorNavmeshPoint;
        }

        $maxDistance = $this->navigationMesh->tileSize * 2;
        $maxY = $this->obstacleOvercomeHeight * 2;
        $checkAbove = function (Point $start, int $maxY, int $radius): ?Point {
            $yCandidate = $start->clone();
            $navMeshCenter = $yCandidate->clone();
            $this->convertToNavMeshNode($navMeshCenter);
            for ($i = 1; $i <= $maxY; $i++) {
                $yCandidate->addY(1);
                if ($this->world->findFloorSquare($yCandidate, $radius - 1)) {
                    return null;
                }
                if ($this->navigationMesh->has($navMeshCenter->setY($yCandidate->y)->hash())) {
                    return $navMeshCenter;
                }
            }

            return null;
        };

        // try navmesh above
        $above = $checkAbove($pointOnFloor, $maxY, $radius);
        if ($above) {
            return $above;
        }

        // try neighbour tiles
        $candidate = $pointOnFloor->clone();
        $navmesh = $pointOnFloor->clone();
        foreach (self::$moves as $angle => $move) {
            $candidate->setFrom($pointOnFloor);

            for ($distance = 1; $distance <= $maxDistance; $distance++) {
                $candidate->addPart(...self::$moves[$angle]);
                if (!$this->canFullyMoveTo($candidate, $angle, 1, $radius, $this->navigationMesh->colliderHeight)) { // @infection-ignore-all
                    break;
                }

                $prevNavmesh = $navmesh->hash();
                $navmesh->setFrom($candidate);
                $this->convertToNavMeshNode($navmesh);
                if ($prevNavmesh === $navmesh->hash()) {
                    continue;
                }

                if ($this->navigationMesh->has($navmesh->hash())) {
                    return $navmesh;
                }
                $above = $checkAbove($candidate, $maxY, $radius);
                if ($above) {
                    return $above;
                }
            }
        }

        return null;
    }

    public function convertToNavMeshNode(Point $point): void
    {
        $this->navigationMesh->convertToNavMeshNode($point);
    }

    public function buildNavigationMesh(Point $start, int $objectHeight, int $maxNodeCount = 2_000): void
    {
        $startPoint = $start->clone();
        $this->convertToNavMeshNode($startPoint);
        if (!$this->world->findFloorSquare($startPoint, 1)) {
            throw new GameException('No floor on start point'); // @codeCoverageIgnore
        }

        /** @var SplQueue<Point> $queue */
        $queue = new SplQueue();
        $queue->enqueue($startPoint);
        $candidate = new Point();

        $nodeCount = 0;
        while (!$queue->isEmpty()) {
            $current = $queue->dequeue();
            $currentKey = $current->hash();
            if (array_key_exists($currentKey, $this->visited)) {
                continue;
            }

            $this->visited[$currentKey] = true;
            $currentNode = $this->graph->getNodeById($currentKey);
            if ($currentNode === null) {
                $currentNode = new Node($currentKey, $current);
                $this->graph->addNode($currentNode);
            }

            foreach (self::$moves as $angle => $move) {
                $candidate->setFrom($current);
                if (!$this->canFullyMoveTo($candidate, $angle, $this->navigationMesh->tileSize, $this->navigationMesh->tileSizeHalf, $objectHeight)) {
                    continue;
                }

                $newNeighbour = $candidate->clone();
                $newNode = $this->graph->getNodeById($newNeighbour->hash());
                if ($newNode === null) {
                    $newNode = new Node($newNeighbour->hash(), $newNeighbour);
                    $this->graph->addNode($newNode);
                }
                $this->graph->addEdge(new DirectedEdge($currentNode, $newNode, 1));
                $queue->enqueue($newNeighbour);
            }
            if (++$nodeCount === $maxNodeCount) {
                GameException::notImplementedYet('MaxNodeCount hit - new map, tileSize or bad test (no boundary box, bad starting point)?'); // @codeCoverageIgnore
            }
        }
    }

    public function saveAndClear(): void
    {
        $this->visited = [];
        $this->navigationMesh->setData($this->getGraph()->generateNeighbors());
    }

    public function getGraph(): Graph
    {
        return $this->graph;
    }

    public function getNavigationMesh(): NavigationMesh
    {
        return $this->navigationMesh;
    }

}
