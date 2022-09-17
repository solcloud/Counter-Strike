<?php

namespace cs\Map;

use cs\Core\Box;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Point2D;
use cs\Core\Ramp;

class DefaultMap extends BoxMap
{
    public function __construct()
    {
        $attackers = [];
        $defenders = [];
        $scale = (int)ceil(Player::playerBoundingRadius * 1.8);
        $scaleHalf = (int)ceil(Player::playerBoundingRadius * 1.8 / 2);
        $radiusHalf = Player::playerBoundingRadius / 2;
        $boxHeight = Player::boxHeightCrouchCover;

        $this->addBox(new Box(new Point(), 31 * $scale, 5 * Player::headHeightStand, 31 * $scale));
        foreach ([3, 9, 15, 21, 27] as $x) {
            $attackers[] = new Point($x * $scale + $radiusHalf, 0, 6 * $scale);
            $this->addBox(new Box(new Point(($x - 1) * $scale, 0, 7 * $scale), 3 * $scale, $boxHeight, $scale));
            $this->addBox(new Box(new Point(($x) * $scale, $boxHeight, 7 * $scale), 1 * $scale, $boxHeight, $scale));

            $defenders[] = new Point($x * $scale + $radiusHalf, 0, 28 * $scale + $scaleHalf);
            $this->addBox(new Box(new Point(($x - 1) * $scale, 0, 27 * $scale), 3 * $scale, $boxHeight, $scale));
            $this->addBox(new Box(new Point(($x) * $scale, $boxHeight, 27 * $scale), 1 * $scale, $boxHeight, $scale));
        }
        $this->addBox(new Box(new Point(15 * $scale, 0, 0 * $scale), $scale, Player::headHeightCrouch, $scale));
        $this->addBox(new Box(new Point(15 * $scale, 0, 30 * $scale), $scale, Player::headHeightCrouch, $scale));
        $ramp = new Ramp(new Point(5 * $scale, 0, 0 * $scale), new Point2D(1, 0), 20, $scale);
        foreach ($ramp->getBoxes() as $box) {
            $this->addBox($box);
        }

        $this->setAttackersSpawnPositions($attackers);
        $this->setDefendersSpawnPositions($defenders);
    }

}
