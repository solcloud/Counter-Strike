<?php

namespace cs\HitGeometry;

use cs\Core\GameException;
use cs\Core\Point;
use cs\Core\Setting;

class HitBoxHead extends SphereGroupHitBox
{

    public function __construct()
    {
        parent::__construct(true);

        $hitboxes = [
            [new Point(0, -8, 1), 8],
            [new Point(0, -9, 4), 8],
            [new Point(0, -15, 7), 7],
            [new Point(0, -14, 1), 7],
            [new Point(0, -20, 0), 5],
            [new Point(0, -20, 3), 5],
            [new Point(0, -20, 9), 4],
        ];

        $maxRadius = Setting::playerHeadRadius();
        foreach ($hitboxes as $hitbox) {
            $point = $hitbox[0];
            $radius = $hitbox[1];

            if (abs($point->y) > 2 * $maxRadius) {
                GameException::invalid($point->hash()); // @codeCoverageIgnore
            }
            if ($radius > $maxRadius) {
                GameException::invalid($point->hash()); // @codeCoverageIgnore
            }

            $this->addHitBox($point, $radius);
        }
    }

}
