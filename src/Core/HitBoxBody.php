<?php

namespace cs\Core;

use cs\Interface\HitIntersect;

class HitBoxBody implements HitIntersect
{

    public function __construct(private Point $bottom, private int $radius)
    {
        if ($this->radius <= 0) {
            throw new GameException("Radius needs to be bigger than zero");
        }
    }

    public function intersect(Player $player, Bullet $bullet): bool
    {
        $point = $player->getPositionImmutable()->add($this->bottom);
        return Collision::pointWithCylinder($bullet->getPosition(), $point, $this->radius, $player->getBodyHeight());
    }

}
