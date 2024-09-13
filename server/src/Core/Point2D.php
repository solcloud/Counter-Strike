<?php

namespace cs\Core;

class Point2D
{

    public function __construct(public int $x = 0, public int $y = 0)
    {
    }

    public function add(int $xAmount, int $yAmount): self
    {
        $this->x += $xAmount;
        $this->y += $yAmount;
        return $this;
    }

    public function __toString(): string
    {
        return "Point2D({$this->x},{$this->y})";
    }

    /**
     * @return array<string,int>
     */
    public function toArray(): array
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
        ];
    }

}
