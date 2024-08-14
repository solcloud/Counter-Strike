<?php

namespace cs\Core;

class Point
{

    public function __construct(public int $x = 0, public int $y = 0, public int $z = 0)
    {
    }

    public function equals(self $point): bool
    {
        return ($this->x === $point->x && $this->y === $point->y && $this->z === $point->z);
    }

    public function addX(int $amount): self
    {
        $this->x += $amount;
        return $this;
    }

    public function setX(int $int): self
    {
        $this->x = $int;
        return $this;
    }

    public function addY(int $amount): self
    {
        $this->y += $amount;
        return $this;
    }

    public function setY(int $int): self
    {
        $this->y = $int;
        return $this;
    }

    public function addZ(int $amount): self
    {
        $this->z += $amount;
        return $this;
    }

    public function setZ(int $int): self
    {
        $this->z = $int;
        return $this;
    }

    public function add(Point $other): self
    {
        $this->x += $other->x;
        $this->y += $other->y;
        $this->z += $other->z;
        return $this;
    }

    public function addPart(int $x, int $y, int $z): self
    {
        $this->x += $x;
        $this->y += $y;
        $this->z += $z;
        return $this;
    }

    public function set(int $x, int $y, int $z): self
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        return $this;
    }

    public function __toString(): string
    {
        return "Point({$this->x},{$this->y},{$this->z})";
    }

    public function clone(): self
    {
        return new self($this->x, $this->y, $this->z);
    }

    public function setFrom(self $point): void
    {
        $this->x = $point->x;
        $this->y = $point->y;
        $this->z = $point->z;
    }

    /**
     * @param int[] $xyz
     */
    public function setFromArray(array $xyz): void
    {
        $this->x = $xyz[0];
        $this->y = $xyz[1];
        $this->z = $xyz[2];
    }

    /**
     * @param int[] $xyz
     */
    public function addFromArray(array $xyz): void
    {
        $this->x += $xyz[0];
        $this->y += $xyz[1];
        $this->z += $xyz[2];
    }

    public function hash(): string
    {
        return "{$this->x},{$this->y},{$this->z}";
    }

    public function to2D(string $XYaxis): Point2D
    {
        return new Point2D($this->{$XYaxis[0]}, $this->{$XYaxis[1]});
    }

    /**
     * @param array{x: int, y: int, z: int} $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['x'], $data['y'], $data['z']);
    }

    /**
     * @return array{x: int, y: int, z: int}
     */
    public function toArray(): array
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
            'z' => $this->z,
        ];
    }

    /**
     * @return int[]
     */
    public function toFlatArray(): array
    {
        return [$this->x, $this->y, $this->z];
    }

}
