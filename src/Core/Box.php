<?php

namespace cs\Core;

class Box extends SolidSurface
{

    /**
     * @var Wall[]
     */
    private array $walls = [];
    /**
     * @var Floor[]
     */
    private array $floors = [];

    public function __construct(private Point $lowerLeftPoint, public readonly int $widthX, public readonly int $heightY, public readonly int $depthZ)
    {
        $this->floors[] = new Floor($lowerLeftPoint->clone(), $widthX, $depthZ);
        $this->floors[] = new Floor($lowerLeftPoint->clone()->addY($heightY), $widthX, $depthZ);

        $this->walls[] = new Wall($lowerLeftPoint->clone(), true, $widthX, $heightY);
        $this->walls[] = new Wall($lowerLeftPoint->clone()->addZ($depthZ), true, $widthX, $heightY);

        $this->walls[] = new Wall($lowerLeftPoint->clone(), false, $depthZ, $heightY);
        $this->walls[] = new Wall($lowerLeftPoint->clone()->addX($widthX), false, $depthZ, $heightY);
    }

    /**
     * @return Floor[]
     */
    public function getFloors(): array
    {
        return $this->floors;
    }

    /**
     * @return Wall[]
     */
    public function getWalls(): array
    {
        return $this->walls;
    }

    public function getBase(): Point
    {
        return $this->lowerLeftPoint;
    }

}
