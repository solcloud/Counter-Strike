<?php

namespace cs\Core;

class NavigationMesh
{

    /** @var array<string,list<string>> */
    private array $data = [];
    public readonly int $tileSizeHalf;

    public function __construct(public readonly int $tileSize, public readonly int $colliderHeight)
    {
        if ($this->tileSize < 3 || $tileSize % 2 !== 1) {
            throw new GameException('Tile size should be odd and greater than 1.'); // @codeCoverageIgnore
        }

        $this->tileSizeHalf = (int)ceil(($this->tileSize - 1) / 2);
    }

    public function convertToNavMeshNode(Point $point): void
    {
        if ($point->x < 1 || $point->z < 1) {
            throw new GameException('World start from 1'); // @codeCoverageIgnore
        }

        $fmodX = fmod($point->x, $this->tileSize);
        $fmodZ = fmod($point->z, $this->tileSize);

        $x = ((int)floor(($point->x + ($fmodX == 0 ? -1 : +0)) / $this->tileSize) * $this->tileSize) + 1 + $this->tileSizeHalf;
        $point->x = $x;
        $z = ((int)floor(($point->z + ($fmodZ == 0 ? -1 : +0)) / $this->tileSize) * $this->tileSize) + 1 + $this->tileSizeHalf;
        $point->z = $z;
    }

    /** @return list<string> */
    public function getGeneratedNeighbors(string $key): array
    {
        return $this->data[$key] ?? [];
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /** @param array<string,list<string>> $data */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @codeCoverageIgnore
     * @return array<string,list<string>>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /** @codeCoverageIgnore */
    public function serialize(): string
    {
        // @phpstan-ignore-next-line
        return json_encode([
            'a' => $this->tileSize,
            'b' => $this->colliderHeight,
            'data' => $this->data,
        ]);
    }

    /** @codeCoverageIgnore */
    public static function unserialize(string $data): self
    {
        $data = json_decode($data, true);
        $self = new self($data['a'], $data['b']); // @phpstan-ignore-line
        $self->data = $data['data']; // @phpstan-ignore-line

        return $self;
    }
}
