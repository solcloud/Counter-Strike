<?php

namespace cs\Event;

use cs\Core\Item;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\SolidSurface;
use cs\Enum\SoundType;

final class SoundEvent extends TickEvent
{

    private ?Item $item = null;
    private ?Player $player = null;
    private ?SolidSurface $surface = null;
    /** @var array<string,mixed> */
    private array $extra = [];

    public function __construct(public readonly Point $position, public readonly SoundType $type)
    {
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;
        return $this;
    }

    public function setPlayer(?Player $player): self
    {
        $this->player = $player;
        return $this;
    }

    public function setSurface(?SolidSurface $surface): self
    {
        $this->surface = $surface;
        return $this;
    }

    public function addExtra(string $key, mixed $value): self
    {
        $this->extra[$key] = $value;
        return $this;
    }

    public function serialize(): array
    {
        return [
            'position' => $this->position->toArray(),
            'item'     => $this->item?->toArray(),
            'player'   => $this->player?->getId(),
            'surface'  => $this->surface?->serialize($this->position),
            'type'     => $this->type->value,
            'extra'    => $this->extra,
        ];
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

}
