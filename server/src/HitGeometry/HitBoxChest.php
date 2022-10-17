<?php

namespace cs\HitGeometry;

use cs\Core\Player;
use cs\Core\Point;

class HitBoxChest extends SphereGroupHitBox
{
    public function __construct()
    {
        parent::__construct(function (Player $player): Point {
            return (new Point())->addY($player->getBodyHeight());
        });

        $base = new Point(0, -10, -6);
        $this->addHitBox(new Point(0, -26, 0), 30);
        $this->createLeftLimb($base->clone()->addX(-22));
        $this->createRightLimb($base->clone()->addX(22));
    }

    private function createLeftLimb(Point $start): void
    {
        $this->addHitBox($start->clone(), 12);
        $this->addHitBox($start->addX(-12)->addY(-1)->addZ(6)->clone(), 8);
        $this->addHitBox($start->addX(-2)->addY(-6)->addZ(8)->clone(), 6);
        $this->addHitBox($start->addX(-1)->addY(-2)->addZ(6)->clone(), 4);
    }

    private function createRightLimb(Point $start): void
    {
        $this->addHitBox($start->clone(), 12);
        $this->addHitBox($start->addX(12)->addY(1)->addZ(14)->clone(), 8);
        $this->addHitBox($start->addX(-4)->addY(-2)->addZ(10)->clone(), 6);
        $this->addHitBox($start->addX(-3)->addY(-3)->addZ(4)->clone(), 4);
    }

}
