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

    public function __construct(Point $lowerLeftPoint, int $widthX, int $heightY, int $depthZ)
    {
        $this->floors[] = new Floor($lowerLeftPoint->clone(), $widthX, $depthZ);
        $this->floors[] = new Floor($lowerLeftPoint->clone()->setY($heightY), $widthX, $depthZ);

        $this->walls[] = new Wall($lowerLeftPoint->clone(), true, $widthX, $heightY);
        $this->walls[] = new Wall($lowerLeftPoint->clone()->setZ($depthZ), true, $widthX, $heightY);

        $this->walls[] = new Wall($lowerLeftPoint->clone(), false, $depthZ, $heightY);
        $this->walls[] = new Wall($lowerLeftPoint->clone()->setX($widthX), false, $depthZ, $heightY);
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

}
