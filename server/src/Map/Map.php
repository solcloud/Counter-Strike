<?php

namespace cs\Map;

use cs\Core\BoxGroup;
use cs\Core\Floor;
use cs\Core\NavigationMesh;
use cs\Core\Point;
use cs\Core\Wall;
use ReflectionClass;

abstract class Map
{

    /** @var list<Point> */
    protected array $spawnPositionAttacker = [];
    /** @var list<Point> */
    protected array $spawnPositionDefender = [];

    /** @param list<Point> $positions */
    public function setAttackersSpawnPositions(array $positions): void
    {
        $this->spawnPositionAttacker = $positions;
    }

    /** @param list<Point> $positions */
    public function setDefendersSpawnPositions(array $positions): void
    {
        $this->spawnPositionDefender = $positions;
    }

    /** @return list<Point> */
    public function getStartingPointsForNavigationMesh(): array
    {
        return array_merge(
            array_slice($this->getSpawnPositionAttacker(), 0, 1),
            array_slice($this->getSpawnPositionDefender(), 0, 1),
        );
    }

    /** @return list<Wall> */
    public function getWalls(): array
    {
        return [
            (new Wall(new Point(0, 0, -1), true, 99999))->setPenetrable(false),
            (new Wall(new Point(-1, 0, 0), false, 99999))->setPenetrable(false),
        ];
    }

    /** @return list<Floor> */
    public function getFloors(): array
    {
        return [
            (new Floor(new Point(), 99999, 99999))->setPenetrable(false),
        ];
    }

    /** @return list<Point> */
    public function getSpawnPositionAttacker(): array
    {
        return $this->spawnPositionAttacker;
    }

    /** @return list<Point> */
    public function getSpawnPositionDefender(): array
    {
        return $this->spawnPositionDefender;
    }

    public function getSpawnRotationAttacker(): int
    {
        return 0;
    }

    public function getSpawnRotationDefender(): int
    {
        return 0;
    }

    public function getBombMaxBlastDistance(): int
    {
        return 1000;
    }

    public function getName(): string
    {
        return strtolower(str_replace('Map', '', (new ReflectionClass($this))->getShortName()));
    }

    public function generateNavigationMeshKey(int $tileSize, int $colliderHeight): string
    {
        return "{$tileSize}-{$colliderHeight}";
    }

    public function getNavigationMeshPath(string $key): string
    {
        return __DIR__ . "/data/{$this->getName()}.navmesh.{$key}.bin";
    }

    public function getNavigationMesh(string $key): ?NavigationMesh
    {
        $path = $this->getNavigationMeshPath($key);
        if (file_exists($path)) {
            return NavigationMesh::unserialize(file_get_contents($path)); // @phpstan-ignore argument.type
        }

        return null;
    }

    public abstract function getBuyArea(bool $forAttackers): BoxGroup;

    public abstract function getPlantArea(): BoxGroup;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'floors'                 => array_map(fn(Floor $o): array => $o->toArray(), $this->getFloors()),
            'walls'                  => array_map(fn(Wall $o): array => $o->toArray(), $this->getWalls()),
            'spawnAttackers'         => array_map(fn(Point $o): array => $o->toArray(), $this->getSpawnPositionAttacker()),
            'spawnDefenders'         => array_map(fn(Point $o): array => $o->toArray(), $this->getSpawnPositionDefender()),
            'startingPointsNavMesh'  => array_map(fn(Point $p): array => $p->toArray(), $this->getStartingPointsForNavigationMesh()),
            'buyAreaAttackers'       => $this->getBuyArea(true)->toArray(),
            'buyAreaDefenders'       => $this->getBuyArea(false)->toArray(),
            'plantArea'              => $this->getPlantArea()->toArray(),
            'spawnRotationAttackers' => $this->getSpawnRotationAttacker(),
            'spawnRotationDefenders' => $this->getSpawnRotationDefender(),
        ];
    }

}
