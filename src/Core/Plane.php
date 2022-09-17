<?php

namespace cs\Core;

abstract class Plane extends SolidSurface
{
    private Point2D $point2DStart;
    private Point2D $point2DEnd;

    public function __construct(private Point $start, private Point $end, string $axis2d)
    {
        $this->point2DStart = $this->start->to2D($axis2d);
        $this->point2DEnd = $this->end->to2D($axis2d);
    }

    public function intersect(Point2D $point, int $radius = 0): bool
    {
        return Collision::circleWithPlane($point, $radius, $this);
    }

    public function getPoint2DStart(): Point2D
    {
        return $this->point2DStart;
    }

    public function getPoint2DEnd(): Point2D
    {
        return $this->point2DEnd;
    }

    public function getStart(): Point
    {
        return $this->start;
    }

    public function getEnd(): Point
    {
        return $this->end;
    }

    public function __toString(): string
    {
        return sprintf("%s(\n start%s\n end%s\n)", get_class($this), $this->getStart(), $this->getEnd());
    }

    /**
     * @return array<string,array<string,int>>
     */
    public function toArray(): array
    {
        return [
            'start' => $this->start->toArray(),
            'end'   => $this->end->toArray(),
        ];
    }

}
