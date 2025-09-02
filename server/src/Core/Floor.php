<?php

namespace cs\Core;

class Floor extends Plane
{
    public bool $supportNavmesh = true;

    public function __construct(Point $start, public readonly int $width = 1, public readonly int $depth = 1)
    {
        if ($width <= 0 || $depth <= 0) {
            throw new GameException("Width and depth cannot be lower than or equal zero");
        }
        parent::__construct($start, new Point($start->x + $width, $start->y, $start->z + $depth), 'xz');
        $this->setNormal(0, 90);
    }

    public function getY(): int
    {
        return $this->getStart()->y;
    }

    public function intersect(Point $point, int $radius): bool
    {
        return $this->getY() === $point->y && Collision::planeWithPlane(
            $this->point2DStart, $this->width, $this->depth,
            $point->x - $radius, $point->z - $radius, 2 * $radius, 2 * $radius,
        );
    }

    public static function fromArray(array $data): self
    {
        $start = new Point($data['s']['x'], $data['s']['y'], $data['s']['z']);
        $end = new Point($data['e']['x'], $data['e']['y'], $data['e']['z']);

        return new self($start, $end->x - $start->x, $end->z - $start->z);
    }

}
