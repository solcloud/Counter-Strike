<?php

namespace cs\Core;

/**
 * @property-read int $x
 * @property-read int $y
 * @property-read int $z
 */
class Point
{

    public function __construct(private int $x = 0, private int $y = 0, private int $z = 0)
    {
    }

    public function __get(string $name): int
    {
        if ($name === 'x') {
            return $this->getX();
        }
        if ($name === 'y') {
            return $this->getY();
        }
        if ($name === 'z') {
            return $this->getZ();
        }

        throw new GameException("Invalid field '{$name}' given");
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function getZ(): int
    {
        return $this->z;
    }

    public function isOrigin(): bool
    {
        return ($this->x === 0 && $this->y === 0 && $this->z === 0);
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
        return $this->addX($other->x)->addY($other->y)->addZ($other->z);
    }

    public function addPart(int $x, int $y, int $z): self
    {
        return $this->addX($x)->addY($y)->addZ($z);
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
        $this->setX($point->x);
        $this->setY($point->y);
        $this->setZ($point->z);
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
            "x" => $this->getX(),
            "y" => $this->getY(),
            "z" => $this->getZ(),
        ];
    }

}
