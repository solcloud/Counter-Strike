<?php

namespace cs\Event;

use cs\Core\Item;
use cs\Core\Player;
use cs\Core\Point;
use cs\Enum\SoundType;

final class SoundEvent extends TickEvent
{

    private ?Item $item = null;
    private ?Player $player = null;
    /** @var array<string,mixed> */
    private array $extra = [];

    public function __construct(public readonly Point $position, public readonly SoundType $type)
    {
        parent::__construct();
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

    public function addExtra(string $key, mixed $value): self
    {
        $this->extra[$key] = $value;
        return $this;
    }

    #[\Override]
    public function serialize(): array
    {
        return [
            'position' => $this->position->toArray(),
            'item'     => $this->item?->toArray(),
            'player'   => $this->player?->getId(),
            'type'     => $this->type->value,
            'extra'    => $this->extra,
        ];
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function getPlayerId(): ?int
    {
        return $this->player?->getId();
    }

}
