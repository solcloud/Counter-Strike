<?php

namespace cs\Map;

use cs\Core\Box;
use cs\Core\BoxGroup;
use cs\Core\Point;

class TestMap extends Map
{

    private BoxGroup $buyArea;
    public Point $startPointForNavigationMesh;

    public function __construct()
    {
        $this->setAttackersSpawnPositions([new Point(), new Point(999, 0, 999)]);
        $this->setDefendersSpawnPositions([
            (new Point())->setZ(50),
            new Point(9991, 0, 9991),
            new Point(9992, 0, 9992),
            new Point(9993, 0, 9993),
            new Point(9994, 0, 9994),
        ]);

        $this->buyArea = new BoxGroup([new Box(new Point(), 99999, 999, 99999)]);
        $this->startPointForNavigationMesh = new Point(100, 0, 100);
    }

    #[\Override]
    public function getStartingPointsForNavigationMesh(): array
    {
        return [$this->startPointForNavigationMesh];
    }

    public function getBuyArea(bool $forAttackers): BoxGroup
    {
        return $this->buyArea;
    }

    public function getPlantArea(): BoxGroup
    {
        return $this->buyArea;
    }

}
