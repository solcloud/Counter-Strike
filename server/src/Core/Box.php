<?php

namespace cs\Core;

class Box
{

    /**
     * @var Wall[]
     */
    private array $walls = [];
    /**
     * @var Floor[]
     */
    private array $floors = [];

    public const SIDE_FRONT = 0xF00000;
    public const SIDE_BACK = 0x0F0000;
    public const SIDE_LEFT = 0x00F000;
    public const SIDE_RIGHT = 0x000F00;
    public const SIDE_TOP = 0x0000F0;
    public const SIDE_BOTTOM = 0x00000F;
    public const SIDE_ALL = 0xFFFFFF;

    public function __construct(
        private Point $lowerLeftPoint,
        public        readonly int $widthX,
        public        readonly int $heightY,
        public        readonly int $depthZ,
        int           $sides = self::SIDE_ALL,
        bool          $penetrable = true,
    )
    {
        if ($sides & self::SIDE_BOTTOM) {
            $this->floors[] = new Floor($lowerLeftPoint->clone(), $widthX, $depthZ);
        }
        if ($sides & self::SIDE_TOP) {
            $this->floors[] = new Floor($lowerLeftPoint->clone()->addY($heightY), $widthX, $depthZ);
        }

        if ($sides & self::SIDE_FRONT) {
            $this->walls[] = new Wall($lowerLeftPoint->clone(), true, $widthX, $heightY);
        }
        if ($sides & self::SIDE_BACK) {
            $this->walls[] = new Wall($lowerLeftPoint->clone()->addZ($depthZ), true, $widthX, $heightY);
        }

        if ($sides & self::SIDE_LEFT) {
            $this->walls[] = new Wall($lowerLeftPoint->clone(), false, $depthZ, $heightY);
        }
        if ($sides & self::SIDE_RIGHT) {
            $this->walls[] = new Wall($lowerLeftPoint->clone()->addX($widthX), false, $depthZ, $heightY);
        }

        if ($this->floors === [] && $this->walls === []) {
            throw new GameException("Choose at least one box side");
        }

        foreach ($this->floors as $plane) {
            $plane->setPenetrable($penetrable);
        }
        foreach ($this->walls as $plane) {
            $plane->setPenetrable($penetrable);
        }
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

    /**
     * @return array<string,int>
     */
    public function toArray(): array
    {
        return [
            'width'  => $this->widthX,
            'height' => $this->heightY,
            'depth'  => $this->depthZ,
            'x'      => $this->lowerLeftPoint->x,
            'y'      => $this->lowerLeftPoint->y,
            'z'      => $this->lowerLeftPoint->z,
        ];
    }

    /**
     * @param array<string,int> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(new Point($data['x'], $data['y'], $data['z']), $data['width'], $data['height'], $data['depth']);
    }

}
