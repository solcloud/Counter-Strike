<?php

namespace cs\Map;

use cs\Core\Box;
use cs\Core\Floor;
use cs\Core\Point;
use cs\Core\Wall;

class ArrayMap extends Map
{

    /** @var array<string,mixed> */
    private array $data;
    /** @var Wall[] */
    private array $walls;
    /** @var Floor[] */
    private array $floors;

    /**
     * @param array<string,mixed> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;

        foreach ($data['spawnAttackers'] as $spawnData) { // @phpstan-ignore-line
            $this->spawnPositionAttacker[] = Point::fromArray($spawnData); // @phpstan-ignore-line
        }
        foreach ($data['spawnDefenders'] as $spawnData) { // @phpstan-ignore-line
            $this->spawnPositionDefender[] = Point::fromArray($spawnData); // @phpstan-ignore-line
        }
        foreach ($data['floors'] as $floorData) { // @phpstan-ignore-line
            $this->floors[] = Floor::fromArray($floorData); // @phpstan-ignore-line
        }
        foreach ($data['walls'] as $wallData) { // @phpstan-ignore-line
            $this->walls[] = Wall::fromArray($wallData); // @phpstan-ignore-line
        }
    }

    public function getWalls(): array
    {
        return $this->walls;
    }

    public function getFloors(): array
    {
        return $this->floors;
    }

    public function getSpawnRotationAttacker(): int
    {
        return $this->data['spawnRotationAttackers']; // @phpstan-ignore-line
    }

    public function getSpawnRotationDefender(): int
    {
        return $this->data['spawnRotationDefenders']; // @phpstan-ignore-line
    }

    public function getBuyArea(bool $forAttackers): Box
    {
        if ($forAttackers) {
            return Box::fromArray($this->data['buyAreaAttackers']); // @phpstan-ignore-line
        }
        return Box::fromArray($this->data['buyAreaDefenders']); // @phpstan-ignore-line
    }

    public function getPlantArea(): Box
    {
        return Box::fromArray($this->data['plantArea']); // @phpstan-ignore-line
    }

}
