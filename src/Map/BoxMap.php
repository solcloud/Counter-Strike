<?php

namespace cs\Map;

use cs\Core\Box;
use cs\Core\Floor;
use cs\Core\Point;
use cs\Core\Wall;

abstract class BoxMap extends Map
{
    /** @var Box[] */
    private array $boxes = [];

    public function addBox(Box $box): void
    {
        $this->boxes[] = $box;
    }

    public function getFloors(): array
    {
        $floors = [new Floor(new Point(), PHP_INT_MAX, PHP_INT_MAX)];
        foreach ($this->boxes as $box) {
            $floors = [...$floors, ...$box->getFloors()];
        }
        return $floors;
    }

    public function getWalls(): array
    {
        $walls = [
            new Wall(new Point(0, 0, -1), true, PHP_INT_MAX),
            new Wall(new Point(-1, 0, 0), false, PHP_INT_MAX),
        ];
        foreach ($this->boxes as $box) {
            $walls = [...$walls, ...$box->getWalls()];
        }
        return $walls;
    }

    /**
     * @return Box[]
     */
    public function getBoxes(): array
    {
        return $this->boxes;
    }

}
