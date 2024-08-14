<?php

namespace cs\Core;

final class Flame
{

    public readonly Point $highestPoint;

    public function __construct(public readonly Point $center, public readonly int $radius, public readonly int $height)
    {
        $this->highestPoint = $this->center->clone()->addY($this->height);
    }

}
