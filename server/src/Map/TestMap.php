<?php

namespace cs\Map;

use cs\Core\Box;
use cs\Core\Point;

class TestMap extends Map
{

    private Box $buyArea;

    public function __construct()
    {
        $this->setAttackersSpawnPositions([new Point()]);
        $this->setDefendersSpawnPositions([(new Point())->setZ(50)]);

        $this->buyArea = new Box(new Point(), 99999, 999, 99999);
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
