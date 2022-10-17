<?php

namespace cs\HitGeometry;

use cs\Core\Player;
use cs\Core\Point;

class HitBoxHead extends SphereGroupHitBox
{

    public function __construct(int $radius)
    {
        parent::__construct(function (Player $player): Point {
            return (new Point())->addY($player->getSightHeight());
        });

        $this->addHitBox(new Point(), $radius);
    }

}
