<?php

namespace cs\Interface;

use cs\Core\Bullet;
use cs\Core\Player;

interface HitIntersect
{
    public function intersect(Player $player, Bullet $bullet): bool;

}
