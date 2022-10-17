<?php

namespace cs\Interface;

use cs\Core\Player;
use cs\Core\Point;

interface HitIntersect
{
    public function intersect(Player $player, Point $point): bool;

}
