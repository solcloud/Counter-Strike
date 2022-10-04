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

    public function setItem(?Item $item): void
    {
        $this->item = $item;
    }

    public function setPlayer(?Player $player): void
    {
        $this->player = $player;
    }

    public function setSurface(?SolidSurface $surface): void
    {
        $this->surface = $surface;
    }

    public function serialize(): array
    {
        return [
            'position' => $this->position->toArray(),
            'item'     => $this->item,
            'player'   => $this->player,
            'surface'  => $this->surface,
            'type'     => $this->type->value,
        ];
    }

}
