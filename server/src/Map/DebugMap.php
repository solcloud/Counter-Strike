<?php

namespace cs\Map;

use cs\Core\Box;
use cs\Core\BoxGroup;
use cs\Core\Floor;
use cs\Core\Plane;
use cs\Core\Point;
use cs\Core\Wall;

class DebugMap extends Map
{
    /** @var list<Wall> */
    public array $walls = [];
    /** @var list<Floor> */
    public array $floors = [];

    public function addPlane(Plane $plane): void
    {
        if ($plane instanceof Wall) {
            $this->walls[] = $plane;
            return;
        }
        if ($plane instanceof Floor) {
            $this->floors[] = $plane;
            return;
        }
    }

    /** @param list<Plane> $planes */
    public function addPlanes(array $planes, bool $penetrable = true): void
    {
        foreach ($planes as $plane) {
            $this->addPlane($plane->setPenetrable($penetrable));
        }
    }

    public function getBuyArea(bool $forAttackers): BoxGroup
    {
        if ($forAttackers) {
            return new BoxGroup([new Box(new Point(0, 0, -1), 1000, 20, 1)]);
        }

        return new BoxGroup([new Box(new Point(-1, 0, 0), 1, 50, 1000)]);
    }

    public function getPlantArea(): BoxGroup
    {
        return new BoxGroup([new Box(new Point(-10, -1, -10), 500, 1, 500)]);
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
}
