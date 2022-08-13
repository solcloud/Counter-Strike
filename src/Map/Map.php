<?php

namespace cs\Map;

use cs\Core\Floor;
use cs\Core\Point;
use cs\Core\Wall;

abstract class Map
{

    /** @var Point[] */
    private array $spawnPositionAttacker = [];
    /** @var Point[] */
    private array $spawnPositionDefender = [];

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

    /**
     * @return Wall[]
     */
    public function getWalls(): array
    {
        return [
            new Wall(new Point(0, 0, -1), true, PHP_INT_MAX),
            new Wall(new Point(-1, 0, 0), false, PHP_INT_MAX),
        ];
    }

    /**
     * @return Floor[]
     */
    public function getFloors(): array
    {
        return [new Floor(new Point(), PHP_INT_MAX, PHP_INT_MAX)];
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

}
