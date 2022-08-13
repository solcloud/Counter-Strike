<?php

namespace cs\Core;

abstract class Plane extends SolidSurface
{

    public function __construct(private Point $start, private Point $end)
    {
    }

    public abstract function intersect(Point $point): bool;

    public function getStart(): Point
    {
        return $this->start;
    }

    public function getEnd(): Point
    {
        return $this->end;
    }

}
