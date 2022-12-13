<?php

namespace cs\Core;

abstract class Plane extends SolidSurface
{
    private const PLANE_WALL_BANG_EDGE_MARGIN = 8;

    protected Point2D $point2DStart;
    protected Point2D $point2DEnd;

    public function __construct(private Point $start, private Point $end, private string $axis2d)
    {
        $this->point2DStart = $this->start->to2D($axis2d);
        $this->point2DEnd = $this->end->to2D($axis2d);
    }

    public function getHitAntiForce(Point $point): int
    {
        if ($this->point2DStart->x <= 0 || $this->point2DStart->y <= 0) { // World boundary, cannot penetrate
            return 999999;
        }

        $hit = $point->to2D($this->axis2d);
        if ($hit->x - $this->point2DStart->x <= self::PLANE_WALL_BANG_EDGE_MARGIN || $this->point2DEnd->x - $hit->x <= self::PLANE_WALL_BANG_EDGE_MARGIN) {
            return 10;
        }
        if ($hit->y - $this->point2DStart->y <= self::PLANE_WALL_BANG_EDGE_MARGIN || $this->point2DEnd->y - $hit->y <= self::PLANE_WALL_BANG_EDGE_MARGIN) {
            return 10;
        }

        return parent::getHitAntiForce($point);
    }

    public function intersect(Point $point, int $radius = 0): bool
    {
        return Collision::circleWithRect(
            $point->{$this->axis2d[0]}, $point->{$this->axis2d[1]}, $radius,
            $this->point2DStart->x, $this->point2DEnd->x, $this->point2DStart->y, $this->point2DEnd->y
        );
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
        return sprintf("%s(\n start%s\n end%s\n axis: %s\n)", get_class($this), $this->getStart(), $this->getEnd(), $this->axis2d);
    }

    /**
     * @return array<string,string|array<string,int>>
     */
    public function toArray(): array
    {
        return [
            's' => $this->start->toArray(),
            'e' => $this->end->toArray(),
            'p' => $this->axis2d,
        ];
    }

    /**
     * @param array{s: array{x: int, y: int, z: int}, e: array{x: int, y: int, z: int}, p: string} $data
     */
    public abstract static function fromArray(array $data): self;

    public function getPlane(): string
    {
        return $this->axis2d;
    }

}
