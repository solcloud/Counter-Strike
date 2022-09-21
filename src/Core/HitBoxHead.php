<?php

namespace cs\Core;

use cs\Interface\HitIntersect;

class HitBoxHead implements HitIntersect
{

    public function __construct(private int $radius)
    {
        if ($this->radius <= 0) {
            throw new GameException("Radius needs to be bigger than zero");
        }
    }

    public function intersect(Player $player, Bullet $bullet): bool
    {
        $point = $player->getPositionImmutable()->addY($player->getSightHeight());
        return Collision::pointWithSphere($bullet->getPosition(), $point, $this->radius);
    }

}
