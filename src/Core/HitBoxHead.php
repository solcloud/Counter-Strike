<?php

namespace cs\Core;

use cs\Interface\HitIntersect;

class HitBoxHead implements HitIntersect
{

    public function __construct(
        private int $radius,
        private int $cutoffTop = 0,
        private int $cutoffBottom = 0
    )
    {
        if ($this->radius <= 0) {
            throw new GameException("Radius needs to be bigger than zero");
        }
        if ($this->cutoffTop >= $this->radius || $this->cutoffBottom >= $this->radius) {
            throw new GameException("Cutoff needs to be smaller than radius");
        }
    }

    public function intersect(Player $player, Bullet $bullet): bool
    {
        $center = new Point(0, $player->getHeadHeight() - $this->radius, 0);
        $pp = $player->getPositionImmutable();
        $bp = $bullet->getPosition();

        if ($bp->x < $pp->x + $center->x - $this->radius) {
            return false;
        }
        if ($bp->x > $pp->x + $center->x + $this->radius) {
            return false;
        }

        if ($bp->y < $pp->y + $center->y - $this->radius + $this->cutoffBottom) {
            return false;
        }
        if ($bp->y > $pp->y + $center->y + $this->radius - $this->cutoffTop) {
            return false;
        }

        if ($bp->z < $pp->z + $center->z - $this->radius) {
            return false;
        }
        if ($bp->z > $pp->z + $center->z + $this->radius) {
            return false;
        }

        return true;
    }

}
