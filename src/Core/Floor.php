<?php

namespace cs\Core;

class Floor extends Plane
{
    public function __construct(Point $start, int $width = 1, int $depth = 1)
    {
        parent::__construct($start, new Point($start->getX() + $width, $start->getY(), $start->getZ() + $depth));
    }

    public function getY(): int
    {
        return $this->getStart()->getY();
    }

    public function intersect(Point $point): bool
    {
        if ($this->getStart()->x <= $point->x && $this->getEnd()->x >= $point->x) {
            if ($this->getStart()->z <= $point->z && $this->getEnd()->z >= $point->z) {
                return true;
            }
        }

        return false;
    }

}
