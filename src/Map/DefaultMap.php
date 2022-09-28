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

        $this->addBox(new Box(new Point(), 43 * $scale, 5 * Player::headHeightStand, 32 * $scale));
        foreach ([5, 13, 21, 29, 37] as $x) {
            $attackers[] = new Point($x * $scale + $scaleHalf, 0, 4 * $scale - $radiusHalf);
            $this->addBox(new Box(new Point(($x - 1) * $scale, 0, 5 * $scale), 3 * $scale, $boxHeight, $scale));
            $this->addBox(new Box(new Point(($x) * $scale, $boxHeight, 5 * $scale), 1 * $scale, $boxHeight, $scale));

            $defenders[] = new Point($x * $scale + $scaleHalf, 0, 28 * $scale + $radiusHalf);
            $this->addBox(new Box(new Point(($x - 1) * $scale, 0, 26 * $scale), 3 * $scale, $boxHeight, $scale));
            $this->addBox(new Box(new Point(($x) * $scale, $boxHeight, 26 * $scale), 1 * $scale, $boxHeight, $scale));
        }

        $stepHeight = 10;
        $stepCount = Player::headHeightCrouch / $stepHeight;
        foreach ([0, 31] as $z) {
            $ramp1 = new Ramp(new Point(19 * $scale, 0, $z * $scale), new Point2D(1, 0), $stepCount, $scale, true, 12, $stepHeight);
            foreach ($ramp1->getBoxes() as $box) {
                $this->addBox($box);
            }
            $this->addBox(new Box(new Point(21 * $scale, 0, $z * $scale), $scale, Player::headHeightCrouch, $scale));
            $ramp2 = new Ramp(new Point(24 * $scale - 20, 0, $z * $scale), new Point2D(-1, 0), $stepCount, $scale, true, 12, $stepHeight);
            foreach ($ramp2->getBoxes() as $box) {
                $this->addBox($box);
            }
        }

        $this->setAttackersSpawnPositions($attackers);
        $this->setDefendersSpawnPositions($defenders);
    }

}
