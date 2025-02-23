<?php

namespace cs\Map;

use cs\Core\Box;
use cs\Core\Floor;
use cs\Core\Plane;
use cs\Core\Point;
use cs\Core\Wall;

class DebugMap extends Map
{
    /** @var Wall[] */
    public array $walls = [];
    /** @var Floor[] */
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

    public function getBuyArea(bool $forAttackers): Box
    {
        if ($forAttackers) {
            return new Box(new Point(0, 0, -1), 1000, 20, 1);
        }

        return new Box(new Point(-1, 0, 0), 1, 50, 1000);
    }

    public function getPlantArea(): Box
    {
        return new Box(new Point(-10, -1, -10), 500, 1, 500);
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
