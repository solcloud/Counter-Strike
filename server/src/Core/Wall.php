<?php

namespace cs\Core;

class Wall extends Plane
{

    public function __construct(
        Point        $start,
        private bool $widthOnXAxis = true,
        public       readonly int $width = 1,
        public       readonly int $height = 3800
    )
    {
        if ($width <= 0 || $height <= 0) {
            throw new GameException("Width and height cannot be lower than or equal zero");
        }

        if ($widthOnXAxis) {
            parent::__construct($start, new Point($start->x + $width, $start->y + $height, $start->z), 'xy');
            $this->setNormal(0, 0);
        } else {
            parent::__construct($start, new Point($start->x, $start->y + $height, $start->z + $width), 'zy');
            $this->setNormal(90, 0);
        }
    }

    public function getBase(): int
    {
        return ($this->widthOnXAxis ? $this->getStart()->z : $this->getStart()->x);
    }

    public function isWidthOnXAxis(): bool
    {
        return $this->widthOnXAxis;
    }

    public function getFloor(): int
    {
        return $this->point2DStart->y;
    }

    public function getCeiling(): int
    {
        return $this->point2DEnd->y;
    }

    public static function fromArray(array $data): self
    {
        $start = new Point($data['s']['x'], $data['s']['y'], $data['s']['z']);
        $end = new Point($data['e']['x'], $data['e']['y'], $data['e']['z']);
        $axis = $data['p'];
        $widthOnXAxis = true;
        $width = $end->x - $start->x;
        if ($axis === 'zy') {
            $widthOnXAxis = false;
            $width = $end->z - $start->z;
        }

        return new self($start, $widthOnXAxis, $width, $end->y - $start->y);
    }

}
