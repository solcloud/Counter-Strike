<?php

namespace cs\Map;

use cs\Core\Box;
use cs\Core\Floor;
use cs\Core\Point;
use cs\Core\Wall;

class ArrayMap extends Map
{

    /** @var Wall[] */
    private array $walls;
    /** @var Floor[] */
    private array $floors;
    /** @var Point[] */
    private array $startingPointsForNavigationMesh;

    /**
     * @param array<string,mixed> $data
     */
    public function __construct(private array $data)
    {
        foreach ($this->data['spawnAttackers'] as $spawnData) { // @phpstan-ignore-line
            $this->spawnPositionAttacker[] = Point::fromArray($spawnData); // @phpstan-ignore-line
        }
        foreach ($this->data['spawnDefenders'] as $spawnData) { // @phpstan-ignore-line
            $this->spawnPositionDefender[] = Point::fromArray($spawnData); // @phpstan-ignore-line
        }
        foreach ($this->data['floors'] as $floorData) { // @phpstan-ignore-line
            $this->floors[] = Floor::fromArray($floorData); // @phpstan-ignore-line
        }
        foreach ($this->data['walls'] as $wallData) { // @phpstan-ignore-line
            $this->walls[] = Wall::fromArray($wallData); // @phpstan-ignore-line
        }

        foreach($this->data['startingPointsNavMesh'] ?? [] as $pointData) { // @phpstan-ignore-line
            $this->startingPointsForNavigationMesh[] = Point::fromArray($pointData); // @phpstan-ignore-line
        }
        $this->startingPointsForNavigationMesh ??= parent::getStartingPointsForNavigationMesh();
    }

    #[\Override]
    public function getStartingPointsForNavigationMesh(): array
    {
        return $this->startingPointsForNavigationMesh;
    }

    #[\Override]
    public function getWalls(): array
    {
        return $this->walls;
    }

    #[\Override]
    public function getFloors(): array
    {
        return $this->floors;
    }

    #[\Override]
    public function getSpawnRotationAttacker(): int
    {
        return $this->data['spawnRotationAttackers']; // @phpstan-ignore-line
    }

    #[\Override]
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
