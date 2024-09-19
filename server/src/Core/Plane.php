<?php

namespace cs\Core;

abstract class Plane extends SolidSurface
{

    public const MAX_HIT_ANTI_FORCE = 99999;

    protected bool $penetrable = true;
    protected int $hitAntiForce = 25123;
    protected int $hitAntiForceMargin = 10;
    protected int $wallBangEdgeMarginDistance = 8;
    protected Point2D $point2DStart;
    protected Point2D $point2DEnd;

    public function __construct(private Point $start, private Point $end, private string $axis2d)
    {
        $this->point2DStart = $this->start->to2D($axis2d);
        $this->point2DEnd = $this->end->to2D($axis2d);
    }

    public function setPenetrable(bool $penetrable): static
    {
        $this->penetrable = $penetrable;
        return $this;
    }

    public function setHitAntiForce(int $hitAntiForceBody, int $hitAntiForceMargin, int $wallBangEdgeMarginDistance): void
    {
        $this->hitAntiForce = max(0, $hitAntiForceBody);
        $this->hitAntiForceMargin = max(0, $hitAntiForceMargin);
        $this->wallBangEdgeMarginDistance = max(0, $wallBangEdgeMarginDistance);
    }

    public function getHitAntiForce(Point $point): int
    {
        if (!$this->penetrable) {
            return self::MAX_HIT_ANTI_FORCE;
        }

        $hit = $point->to2D($this->axis2d);
        if ($hit->x < $this->point2DStart->x || $hit->x > $this->point2DEnd->x
            || $hit->y < $this->point2DStart->y || $hit->y > $this->point2DEnd->y) {
            throw new GameException("Hit '{$hit}' ({$point}) out of plane boundary '{$this}'");
        }

        $margin = $this->wallBangEdgeMarginDistance;
        if ($hit->x - $this->point2DStart->x <= $margin || $this->point2DEnd->x - $hit->x <= $margin) {
            return $this->hitAntiForceMargin;
        }
        if ($hit->y - $this->point2DStart->y <= $margin || $this->point2DEnd->y - $hit->y <= $margin) {
            return $this->hitAntiForceMargin;
        }

        return $this->hitAntiForce;
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
