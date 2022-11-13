<?php

namespace cs\Equipment;

use cs\Enum\InventorySlot;
use cs\Enum\ItemType;

class DefuseKit extends BaseEquipment
{

    public function getPrice(): int
    {
        return 400;
    }

    public function getType(): ItemType
    {
        return ItemType::TYPE_DEFUSE_KIT;
    }

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_KIT;
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
