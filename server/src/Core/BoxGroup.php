<?php

namespace cs\Core;

class BoxGroup
{
    public readonly Point $boundaryMin;
    public readonly Point $boundaryMax;

    /** @param list<Box> $boxes */
    public function __construct(private array $boxes = [])
    {
        $this->boundaryMin = new Point(PHP_INT_MAX, PHP_INT_MAX, PHP_INT_MAX);
        $this->boundaryMax = new Point(PHP_INT_MIN, PHP_INT_MIN, PHP_INT_MIN);

        foreach ($this->boxes as $box) {
            $this->add($box);
        }
    }

    public function add(Box $box): void
    {
        $boxMin = $box->getBase();
        $this->boundaryMin->set(
            min($this->boundaryMin->x, $boxMin->x),
            min($this->boundaryMin->y, $boxMin->y),
            min($this->boundaryMin->z, $boxMin->z),
        );
        $this->boundaryMax->set(
            max($this->boundaryMax->x, $boxMin->x + $box->widthX),
            max($this->boundaryMax->y, $boxMin->y + $box->heightY),
            max($this->boundaryMax->z, $boxMin->z + $box->depthZ),
        );
        $this->boxes[] = $box;
    }

    public function contains(Point $point): bool
    {
        if ([] === $this->boxes || !Collision::pointWithBoxBoundary($point, $this->boundaryMin, $this->boundaryMax)) {
            return false;
        }

        foreach ($this->boxes as $box) {
            if (Collision::pointWithBox($point, $box)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<array<string,int>> */
    public function toArray(): array
    {
        return array_map(fn(Box $box) => $box->toArray(), $this->boxes);
    }

    /** @param list<array<string,int>> $data */
    public static function fromArray(array $data): self
    {
        return new self(array_map(fn(array $boxData): Box => Box::fromArray($boxData), $data));
    }

}
