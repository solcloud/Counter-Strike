<?php

namespace cs\Core;

final class Column
{

    public bool $active = true;
    public readonly Point $highestPoint;
    public readonly Point $boundaryMin;
    public readonly Point $boundaryMax;

    public function __construct(public readonly Point $center, public readonly int $radius, public readonly int $height)
    {
        $this->highestPoint = $this->center->clone()->addY($this->height);
        $this->boundaryMin = $this->center->clone()->addX(-$this->radius)->addZ(-$this->radius);
        $this->boundaryMax = $this->center->clone()->addX($this->radius)->addZ($this->radius)->addY($this->height);
    }

}
