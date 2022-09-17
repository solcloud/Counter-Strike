<?php

namespace cs\Core;

class Wall extends Plane
{

    public function __construct(Point $start, private bool $widthOnXAxis = true, int $width = 1, int $height = 20 * Player::headHeightStand)
    {
        if ($width <= 0 || $height <= 0) {
            throw new GameException("Width and height cannot be lower than or equal zero");
        }

        if ($widthOnXAxis) {
            parent::__construct($start, new Point($start->getX() + $width, $start->getY() + $height, $start->getZ()), 'xy');
        } else {
            parent::__construct($start, new Point($start->getX(), $start->getY() + $height, $start->getZ() + $width), 'zy');
        }
    }

    public function getBase(): int
    {
        return ($this->widthOnXAxis ? $this->getStart()->z : $this->getStart()->x);
    }

    public function getOther(): int
    {
        return ($this->widthOnXAxis ? $this->getStart()->x : $this->getStart()->z);
    }

    public function isWidthOnXAxis(): bool
    {
        return $this->widthOnXAxis;
    }

    public function getCeiling(): int
    {
        return $this->getEnd()->y;
    }

}
