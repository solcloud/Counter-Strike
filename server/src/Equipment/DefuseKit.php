<?php

namespace cs\Equipment;

use cs\Core\Item;
use cs\Enum\InventorySlot;
use cs\Enum\ItemType;

class DefuseKit extends BaseEquipment
{
    protected int $price = 400;

    public function getType(): ItemType
    {
        return ItemType::TYPE_DEFUSE_KIT;
    }

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_KIT;
    }

    #[\Override]
    public function canBeEquipped(): bool
    {
        return false;
    }

    #[\Override]
    public function isUserDroppable(): bool
    {
        return false;
    }

    #[\Override]
    public function canPurchaseMultipleTime(Item $newSlotItem): bool
    {
        return false;
    }

}
