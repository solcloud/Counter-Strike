<?php

namespace cs\HitGeometry;

use cs\Core\Player;
use cs\Core\Point;

class HitBoxBody extends SphereGroupHitBox
{

    public function __construct(private int $maxRadius)
    {
        parent::__construct(function (Player $player): Point {
            return (new Point())->addY($player->getBodyHeight());
        });

        $bodyRadius = max(1, (int)round($this->maxRadius * .9));
        $this->addHitBox(new Point(0, -$bodyRadius, 0), $bodyRadius);
    }

}
