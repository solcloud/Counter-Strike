<?php

namespace cs\Equipment;

use cs\Enum\ArmorType;
use cs\Enum\InventorySlot;
use cs\Enum\ItemType;

class Kevlar extends BaseEquipment
{

    public function __construct(private bool $bodyPlusHelmet)
    {
        parent::__construct(true);
    }

    public function getPrice(): int
    {
        return $this->bodyPlusHelmet ? 1000 : 650;
    }

    public function getArmorType(): ArmorType
    {
        return $this->bodyPlusHelmet ? ArmorType::BODY_AND_HEAD : ArmorType::BODY;
    }

    public function getType(): ItemType
    {
        return ItemType::TYPE_KEVLAR;
    }

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_KEVLAR;
    }

    public function isUserDroppable(): bool
    {
        return false;
    }

    public function canPurchaseMultipleTime(): bool
    {
        return false;
    }

}
