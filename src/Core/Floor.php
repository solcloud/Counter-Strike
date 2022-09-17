<?php

namespace cs\Core;

class Floor extends Plane
{

    public function __construct(Point $start, int $width = 1, int $depth = 1)
    {
        parent::__construct($start, new Point($start->getX() + $width, $start->getY(), $start->getZ() + $depth), 'xz');
    }

    public function getY(): int
    {
        return $this->getStart()->getY();
    }

}
