<?php

namespace cs\Map;

use cs\Core\Box;
use cs\Core\Floor;
use cs\Core\PathFinder;
use cs\Core\Point;
use cs\Core\Wall;

abstract class Map
{

    /** @var Point[] */
    protected array $spawnPositionAttacker = [];
    /** @var Point[] */
    protected array $spawnPositionDefender = [];

    /**
     * @param Point[] $positions
     */
    public function setAttackersSpawnPositions(array $positions): void
    {
        $this->spawnPositionAttacker = $positions;
    }

    /**
     * @param Point[] $positions
     */
    public function setDefendersSpawnPositions(array $positions): void
    {
        $this->spawnPositionDefender = $positions;
    }

    /** @return Point[] */
    public function getStartingPointsForNavigationMesh(): array
    {
        return array_merge($this->getSpawnPositionAttacker(), $this->getSpawnPositionDefender());
    }

    /**
     * @return Wall[]
     */
    public function getWalls(): array
    {
        return [
            (new Wall(new Point(0, 0, -1), true, 99999))->setPenetrable(false),
            (new Wall(new Point(-1, 0, 0), false, 99999))->setPenetrable(false),
        ];
    }

    /**
     * @return Floor[]
     */
    public function getFloors(): array
    {
        return [
            (new Floor(new Point(), 99999, 99999))->setPenetrable(false),
        ];
    }

    /**
     * @return Point[]
     */
    public function getSpawnPositionAttacker(): array
    {
        return $this->spawnPositionAttacker;
    }

    /**
     * @return Point[]
     */
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

    public function getNavigationMesh(string $key): ?PathFinder
    {
        return null;
    }

    public abstract function getBuyArea(bool $forAttackers): Box;

    public abstract function getPlantArea(): Box;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'floors'                 => array_map(fn(Floor $o) => $o->toArray(), $this->getFloors()),
            'walls'                  => array_map(fn(Wall $o) => $o->toArray(), $this->getWalls()),
            'spawnAttackers'         => array_map(fn(Point $o) => $o->toArray(), $this->getSpawnPositionAttacker()),
            'spawnDefenders'         => array_map(fn(Point $o) => $o->toArray(), $this->getSpawnPositionDefender()),
            'startingPointsNavMesh'  => array_map(fn(Point $p) => $p->toArray(), $this->getStartingPointsForNavigationMesh()),
            'buyAreaAttackers'       => $this->getBuyArea(true)->toArray(),
            'buyAreaDefenders'       => $this->getBuyArea(false)->toArray(),
            'plantArea'              => $this->getPlantArea()->toArray(),
            'spawnRotationAttackers' => $this->getSpawnRotationAttacker(),
            'spawnRotationDefenders' => $this->getSpawnRotationDefender(),
        ];
    }

}
