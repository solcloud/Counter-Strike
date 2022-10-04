<?php

namespace cs\Core;

class Ramp
{
    /** @var Box[] */
    private array $boxes = [];

    public function __construct(
        Point   $lowerLeftPoint,
        Point2D $direction,
        public  readonly int $stepCount,
        public  readonly int $stepWidth,
        bool    $stairsGrowingUp = true,
        public  readonly int $stepDepth = 20,
        public  readonly int $stepHeight = 20,
    )
    {
        if (
            ($direction->x <> 0 && $direction->y <> 0)
            ||
            ($direction->x === 0 && $direction->y === 0)
        ) {
            throw new GameException("Invalid direction given");
        }

        $heightSum = $stairsGrowingUp ? $stepHeight : $stepHeight * $stepCount;
        if ($direction->x <> 0) {
            $depth = $stepWidth;
            $width = $stepDepth;
        } else {
            $depth = $stepDepth;
            $width = $stepWidth;
        }

        $point = $stairsGrowingUp ? $lowerLeftPoint->clone() : $lowerLeftPoint->clone()->addY(-$heightSum);
        for ($step = 0; $step < $stepCount; $step++) {
            // todo use only walls and floors for fewer walls and floors or use box $sides param
            $this->boxes[] = new Box($point->clone(), $width, $heightSum, $depth);

            $heightSum = $stairsGrowingUp ? $heightSum + $stepHeight : $heightSum - $stepHeight;
            if ($direction->x <> 0) {
                $point->addX(($direction->x > 0 ? 1 : -1) * $stepDepth);
            } else {
                $point->addZ(($direction->y > 0 ? 1 : -1) * $stepDepth);
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
