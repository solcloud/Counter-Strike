<?php

namespace cs\Core;

class Ramp
{
    /** @var Box[] */
    private array $boxes = [];

    public function __construct(
        Point   $lowerLeftPoint,
        Point2D $growDirection,
        int     $stepCount,
        int     $stepWidth,
        bool    $stairsUp = true,
        int     $stepHeight = Player::obstacleOvercomeHeight,
        int     $stepDepth = Player::obstacleOvercomeHeight
    )
    {
        if (
            ($growDirection->x <> 0 && $growDirection->y <> 0)
            ||
            ($growDirection->x === 0 && $growDirection->y === 0)
        ) {
            throw new GameException("Invalid growDirection given");
        }

        $heightSum = $stepHeight;
        if ($growDirection->x <> 0) {
            $depth = $stepWidth;
            $width = $stepDepth;
        } else {
            $depth = $stepDepth;
            $width = $stepWidth;
        }

        $point = $lowerLeftPoint->clone();
        for ($step = 0; $step < $stepCount; $step++) {
            $this->boxes[] = new Box($point->clone(), $width, $heightSum, $depth);

            $heightSum = $stairsUp ? $heightSum + $stepHeight : $heightSum - $stepHeight;
            if ($growDirection->x <> 0) {
                $point->addX(($growDirection->x > 0 ? 1 : -1) * $stepDepth);
            } else {
                $point->addZ(($growDirection->y > 0 ? 1 : -1) * $stepDepth);
            }
        }
    }

    /**
     * @return Box[]
     */
    public function getBoxes(): array
    {
        return $this->boxes;
    }

}
