<?php

namespace cs\Map;

use cs\Core\Point;
use cs\Core\Setting;

class TestMap extends Map
{

    public function __construct()
    {
        $this->setAttackersSpawnPositions([new Point()]);
        $this->setDefendersSpawnPositions([(new Point())->setZ(Setting::moveDistancePerTick())]);
    }

}
