<?php

namespace cs\Core;

use cs\Interface\HitIntersect;

class HitBoxBody implements HitIntersect
{

    public function __construct(
        private Point $bottom,
        private int   $radius,
    )
    {
        if ($this->radius <= 0) {
            throw new GameException("Radius needs to be bigger than zero");
        }
    }

    public function intersect(Player $player, Bullet $bullet): bool
    {
        $pp = $player->getPositionImmutable();
        $bp = $bullet->getPosition();
        $height = $player->getBodyHeight();

        if ($bp->x < $pp->x + $this->bottom->x - $this->radius) {
            return false;
        }
        if ($bp->x > $pp->x + $this->bottom->x + $this->radius) {
            return false;
        }

        if ($bp->y < $pp->y + $this->bottom->y) {
            return false;
        }
        if ($bp->y > $pp->y + $this->bottom->y + $height) {
            return false;
        }

        if ($bp->z < $pp->z + $this->bottom->z - $this->radius) {
            return false;
        }
        if ($bp->z > $pp->z + $this->bottom->z + $this->radius) {
            return false;
        }

        return true;
    }

}
