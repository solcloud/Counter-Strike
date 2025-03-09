<?php

namespace cs\Map;

use cs\Core\Box;

abstract class BoxMap extends Map
{
    /** @var Box[] */
    private array $boxes = [];

    public function addBox(Box $box): void
    {
        $this->boxes[] = $box;
    }

    #[\Override]
    public function getFloors(): array
    {
        $floors = parent::getFloors();
        foreach ($this->boxes as $box) {
            $floors = [...$floors, ...$box->getFloors()];
        }
        return $floors;
    }

    #[\Override]
    public function getWalls(): array
    {
        $walls = parent::getWalls();
        foreach ($this->boxes as $box) {
            $walls = [...$walls, ...$box->getWalls()];
        }
        return $walls;
    }

}
