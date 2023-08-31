<?php

namespace cs\Core;

use cs\Enum\ItemType;

class DropItem
{
    private int $radius;
    private int $height;

    public function __construct(private string $id, private Item $item, private Point $position)
    {
        $this->radius = ($item->getType() === ItemType::TYPE_WEAPON_PRIMARY ? 30 : ($item->getType() === ItemType::TYPE_WEAPON_SECONDARY ? 20 : 10));
        $this->height = 6;
    }

    public function getPosition(): Point
    {
        return $this->position;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function getBoundingRadius(): int
    {
        return $this->radius;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getId(): string
    {
        return $this->id;
    }

}
