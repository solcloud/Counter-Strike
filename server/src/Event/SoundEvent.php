<?php

namespace cs\Event;

use cs\Core\Item;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\SolidSurface;
use cs\Enum\SoundType;

class SoundEvent extends TickEvent
{

    private ?Item $item = null;
    private ?Player $player = null;
    private ?SolidSurface $surface = null;

    public function __construct(private Point $position, private SoundType $type)
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

    public function serialize(): array
    {
        return [
            'position' => $this->position->toArray(),
            'item'     => $this->item?->getId(),
            'player'   => $this->player?->getId(),
            'surface'  => $this->surface?->getHitAntiForce(),
            'type'     => $this->type->value,
        ];
    }

}
