<?php

namespace cs\Core;

class Floor extends Plane
{

    public function __construct(Point $start, int $width = 1, int $depth = 1)
    {
        parent::__construct($start, new Point($start->x + $width, $start->y, $start->z + $depth), 'xz');
    }

    public function getY(): int
    {
        return $this->getStart()->getY();
    }

    public static function fromArray(array $data): self
    {
        $start = new Point($data['s']['x'], $data['s']['y'], $data['s']['z']);
        $end = new Point($data['e']['x'], $data['e']['y'], $data['e']['z']);

        return new self($start, $end->x - $start->x, $end->z - $start->z);
    }

}
