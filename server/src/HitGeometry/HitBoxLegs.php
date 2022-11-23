<?php

namespace cs\HitGeometry;

use cs\Core\Point;

class HitBoxLegs extends SphereGroupHitBox
{

    public function __construct(int $maxY)
    {
        parent::__construct();
        $base = new Point(0, 0, 4);
        $this->createLeftLimb($base->clone()->addX(-20), $maxY);
        $this->createRightLimb($base->clone()->addX(20), $maxY);
    }

    private function createLeftLimb(Point $start, int $maxY): void
    {
        $this->addHitBox($start->addY(14)->clone(), 14);
        $this->addHitBox($start->addX(-4)->addY(20)->addZ(-6)->clone(), 10);
        $this->addHitBox($start->addX(-4)->setY($maxY + 4)->addZ(4)->clone(), 14);
    }

    private function createRightLimb(Point $start, int $maxY): void
    {
        $this->addHitBox($start->addY(14)->clone(), 14);
        $this->addHitBox($start->addX(4)->addY(20)->addZ(-6)->clone(), 10);
        $this->addHitBox($start->addX(4)->setY($maxY + 4)->addZ(4)->clone(), 14);
    }

}
