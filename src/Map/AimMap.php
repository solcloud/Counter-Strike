<?php

namespace cs\Map;

use cs\Core\Box;
use cs\Core\Point;
use cs\Core\Setting;
use cs\Core\Util;

class AimMap extends BoxMap
{

    public function __construct()
    {
        $this->addBox(new Box(new Point(), 2000, Setting::playerHeadHeightStand() * 4, 2000));
    }

    public function getWalls(): array
    {
        return $this->getBoxes()[0]->getWalls();
    }

    public function getFloors(): array
    {
        return $this->getBoxes()[0]->getFloors();
    }

    public function getBuyArea(bool $forAttackers): Box
    {
        return $this->getBoxes()[0];
    }

    public function getSpawnPositionAttacker(): array
    {
        return [new Point(rand(950, 1050), 0, rand(950, 1050))];
    }

    public function getSpawnPositionDefender(): array
    {
        [$x, $z] = Util::rotatePointY(rand(0, 359), rand(300, 800), rand(300, 800), 1000, 1000);
        return [new Point($x, 0, $z)];
    }

    public function getSpawnRotationAttacker(): int
    {
        return rand(0, 359);
    }

    public function getSpawnRotationDefender(): int
    {
        return rand(0, 359);
    }

}
