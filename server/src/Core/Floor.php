<?php

namespace cs\Core;

class Floor extends Plane
{

    public function __construct(Point $start, public readonly int $width = 1, public readonly int $depth = 1)
    {
        if ($width <= 0 || $depth <= 0) {
            throw new GameException("Width and depth cannot be lower than or equal zero");
        }
        parent::__construct($start, new Point($start->x + $width, $start->y, $start->z + $depth), 'xz');
    }

    public function getY(): int
    {
        return $this->getStart()->y;
    }

    public static function fromArray(array $data): self
    {
        $start = new Point($data['s']['x'], $data['s']['y'], $data['s']['z']);
        $end = new Point($data['e']['x'], $data['e']['y'], $data['e']['z']);

        return new self($start, $end->x - $start->x, $end->z - $start->z);
    }

}
