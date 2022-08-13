<?php

namespace cs\Map;

use cs\Core\Box;
use cs\Core\Player;
use cs\Core\Point;

class DefaultMap extends BoxMap
{
    public function __construct()
    {
        $attackers = [];
        $defenders = [];
        $scale = Player::playerBoundingRadius;
        $boxHeight = Player::boxHeightCrouchCover;

        foreach ([3, 9, 15, 21, 27] as $x) {
            $attackers[] = new Point($x * $scale, 0, 6 * $scale);
            $defenders[] = new Point($x * $scale, 0, 26 * $scale);

            $this->addBox(new Box(new Point(($x - 1) * $scale, 0, 7 * $scale), 3 * $scale, $boxHeight, $scale));
            $this->addBox(new Box(new Point(($x) * $scale, $boxHeight, 7 * $scale), 1 * $scale, $boxHeight, $scale));
        }

        $this->setAttackersSpawnPositions($attackers);
        $this->setDefendersSpawnPositions($defenders);
    }

}
