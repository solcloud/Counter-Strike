<?php

namespace cs\Map;

use cs\Core\Action;
use cs\Core\Point;

class TestMap extends Map
{

    public function __construct()
    {
        $this->setAttackersSpawnPositions([new Point()]);
        $this->setDefendersSpawnPositions([(new Point())->setZ(Action::moveDistancePerTick())]);
    }

}
