<?php

namespace cs\Map;

use cs\Core\Player;
use cs\Core\Point;

class TestMap extends Map
{

    public function __construct()
    {
        $this->setAttackersSpawnPositions([new Point()]);
        $this->setDefendersSpawnPositions([(new Point())->setZ(Player::speedMove)]);
    }

}
