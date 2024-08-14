<?php

namespace cs\Map;

use cs\Core\Box;
use cs\Core\Point;

class TestMap extends Map
{

    private Box $buyArea;
    public Point $startPointForNavigationMesh;

    public function __construct()
    {
        $this->setAttackersSpawnPositions([new Point()]);
        $this->setDefendersSpawnPositions([
            (new Point())->setZ(50),
            new Point(9999, 0, 9999),
            new Point(9999, 0, 9999),
            new Point(9999, 0, 9999),
            new Point(9999, 0, 9999),
        ]);

        $this->buyArea = new Box(new Point(), 99999, 999, 99999);
        $this->startPointForNavigationMesh = new Point(100, 0, 100);
    }

    public function getStartingPointsForNavigationMesh(): array
    {
        return [$this->startPointForNavigationMesh];
    }

    public function getBuyArea(bool $forAttackers): Box
    {
        return $this->buyArea;
    }

    public function getPlantArea(): Box
    {
        return $this->buyArea;
    }

}
