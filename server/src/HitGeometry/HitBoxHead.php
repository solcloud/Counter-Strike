<?php

namespace cs\HitGeometry;

use cs\Core\Player;
use cs\Core\Point;

class HitBoxHead extends SphereGroupHitBox
{

    public function __construct()
    {
        parent::__construct(function (Player $player): Point {
            return (new Point())->addY($player->getHeadHeight());
        });

        $this->addHitBox(new Point(0, -8, 1), 8);
        $this->addHitBox(new Point(0, -9, 4), 8);
        $this->addHitBox(new Point(0, -15, 7), 7);
        $this->addHitBox(new Point(0, -14, 1), 7);
        $this->addHitBox(new Point(0, -20, 0), 5);
        $this->addHitBox(new Point(0, -20, 3), 5);
        $this->addHitBox(new Point(0, -20, 9), 4);
    }

}
