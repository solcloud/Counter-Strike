<?php

namespace cs\Map;

use cs\Core\Box;
use cs\Core\Point;
use cs\Core\Point2D;
use cs\Core\Ramp;
use cs\Core\Setting;

class DefaultMap extends BoxMap
{

    /** @var Box[] */
    private array $buyArea;
    private Box $plantArea;

    public function __construct()
    {
        $y = 0;
        $attackers = [];
        $defenders = [];
        $scale = (int)ceil(Setting::playerBoundingRadius() * 1.8);
        $scaleHalf = (int)ceil(Setting::playerBoundingRadius() * 1.8 / 2);
        $radiusHalf = Setting::playerBoundingRadius() / 2;
        $boxHeight = Setting::playerHeadHeightCrouch() + 2;

        $this->addBox(new Box(new Point(0, $y, 0), 43 * $scale, 5 * Setting::playerHeadHeightStand(), 32 * $scale));
        foreach ([5, 13, 21, 29, 37] as $x) {
            $attackers[] = new Point($x * $scale + $scaleHalf, $y, 4 * $scale - $radiusHalf);
            $this->addBox(new Box(new Point(($x - 1) * $scale, $y, 5 * $scale), 3 * $scale, $boxHeight, $scale));
            $this->addBox(new Box(new Point(($x) * $scale, $y + $boxHeight, 5 * $scale), 1 * $scale, $boxHeight, $scale));

            $defenders[] = new Point($x * $scale + $scaleHalf, $y, 28 * $scale + $radiusHalf);
            $this->addBox(new Box(new Point(($x - 1) * $scale, $y, 26 * $scale), 3 * $scale, $boxHeight, $scale));
            $this->addBox(new Box(new Point(($x) * $scale, $y + $boxHeight, 26 * $scale), 1 * $scale, $boxHeight, $scale));
        }

        $stepHeight = 10;
        $stepCount = Setting::playerHeadHeightCrouch() / $stepHeight;
        foreach ([0, 31] as $z) {
            $ramp1 = new Ramp(new Point(19 * $scale, $y, $z * $scale), new Point2D(1, 0), $stepCount, $scale, true, 12, $stepHeight);
            foreach ($ramp1->getBoxes() as $box) {
                $this->addBox($box);
            }
            $this->addBox(new Box(new Point(21 * $scale, $y, $z * $scale), $scale, Setting::playerHeadHeightCrouch(), $scale));
            $ramp2 = new Ramp(new Point(24 * $scale - 20, $y, $z * $scale), new Point2D(-1, 0), $stepCount, $scale, true, 12, $stepHeight);
            foreach ($ramp2->getBoxes() as $box) {
                $this->addBox($box);
            }
        }

        $this->setAttackersSpawnPositions($attackers);
        $this->setDefendersSpawnPositions($defenders);

        $this->plantArea = new Box(new Point(43 * $scale / 2 - 300, $y, 32 * $scale / 2 - 200), 600, 4, 400);
        $this->buyArea[0] = new Box((new Point(0, $y, 0))->addZ(26 * $scale), 43 * $scale, 2 * Setting::playerHeadHeightStand(), 6 * $scale);
        $this->buyArea[1] = new Box(new Point(0, $y, 0), 43 * $scale, 2 * Setting::playerHeadHeightStand(), 6 * $scale);
    }

    public function getSpawnRotationDefender(): int
    {
        return 180;
    }

    public function getBuyArea(bool $forAttackers): Box
    {
        return $this->buyArea[(int)$forAttackers];
    }

    public function getPlantArea(): Box
    {
        return $this->plantArea;
    }

}
