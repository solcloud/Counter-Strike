<?php

namespace cs\Core;

use cs\Enum\RampDirection;

class Ramp
{
    /** @var Box[] */
    private array $boxes = [];

    public function __construct(
        Point   $lowerLeftPoint,
        RampDirection $direction,
        public  readonly int $stepCount,
        public  readonly int $stepWidth,
        bool    $stairsGrowingUp = true,
        public  readonly int $stepDepth = 20,
        public  readonly int $stepHeight = 20,
    )
    {
        $heightSum = $stairsGrowingUp ? $stepHeight : $stepHeight * $stepCount;
        if ($direction->isOnXAxis()) {
            $depth = $stepWidth;
            $width = $stepDepth;
        } else {
            $depth = $stepDepth;
            $width = $stepWidth;
        }

        $point = $stairsGrowingUp ? $lowerLeftPoint->clone() : $lowerLeftPoint->clone()->addY(-$heightSum);
        for ($step = 0; $step < $stepCount; $step++) {
            $this->boxes[] = new Box($point->clone(), $width, $heightSum, $depth); // fixme: use smallest amount of just walls and floors instead of box

            $heightSum = $stairsGrowingUp ? $heightSum + $stepHeight : $heightSum - $stepHeight;
            $amount = ($direction->growToPositive() ? 1 : -1) * $stepDepth;
            if ($direction->isOnXAxis()) {
                $point->addX($amount);
            } else {
                $point->addZ($amount);
            }
        }
    }

    /**
     * @return Box[]
     */
    public function getBoxes(): array // fixme: migrate to walls and floors
    {
        return $this->boxes;
    }

}
