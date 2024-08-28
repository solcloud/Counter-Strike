<?php

namespace cs\HitGeometry;

use cs\Core\Player;
use cs\Core\Point;

class HitBoxBack extends SphereGroupHitBox
{
    private Point $centerPoint;

    public function __construct()
    {
        $this->centerPoint = new Point();
        parent::__construct(function (Player $player): Point {
            return $this->centerPoint->setScalar(0)->addY($player->getHeadHeight());
        });

        $this->createBackLeft();
        $this->createBackRight();
    }

    private function createBackLeft(): void
    {
        $this->addHitBox(new Point(-9, -71, -4), 6);
        $this->addHitBox(new Point(-9, -63, -4), 6);
        $this->addHitBox(new Point(-9, -56, -6), 6);
        $this->addHitBox(new Point(-9, -49, -7), 6);
        $this->addHitBox(new Point(-9, -43, -8), 6);
        $this->addHitBox(new Point(-9, -36, -7), 6);
        $this->addHitBox(new Point(-9, -32, -5), 4);
        $this->addHitBox(new Point(-3, -78, -7), 6);
        $this->addHitBox(new Point(-3, -71, -6), 6);
        $this->addHitBox(new Point(-3, -63, -5), 6);
        $this->addHitBox(new Point(-3, -56, -7), 6);
        $this->addHitBox(new Point(-3, -49, -8), 6);
        $this->addHitBox(new Point(-3, -43, -9), 6);
        $this->addHitBox(new Point(-3, -36, -8), 6);
        $this->addHitBox(new Point(-3, -31, -7), 4);
        $this->addHitBox(new Point(-9, -78, -4), 6);
    }

    private function createBackRight(): void
    {
        $this->addHitBox(new Point(9, -71, -4), 6);
        $this->addHitBox(new Point(9, -63, -4), 6);
        $this->addHitBox(new Point(9, -56, -6), 6);
        $this->addHitBox(new Point(9, -49, -7), 6);
        $this->addHitBox(new Point(9, -43, -8), 6);
        $this->addHitBox(new Point(9, -36, -7), 6);
        $this->addHitBox(new Point(9, -32, -5), 4);
        $this->addHitBox(new Point(3, -78, -7), 6);
        $this->addHitBox(new Point(3, -71, -6), 6);
        $this->addHitBox(new Point(3, -63, -5), 6);
        $this->addHitBox(new Point(3, -56, -7), 6);
        $this->addHitBox(new Point(3, -49, -8), 6);
        $this->addHitBox(new Point(3, -43, -9), 6);
        $this->addHitBox(new Point(3, -36, -8), 6);
        $this->addHitBox(new Point(3, -31, -7), 4);
        $this->addHitBox(new Point(9, -78, -4), 6);
    }

}
