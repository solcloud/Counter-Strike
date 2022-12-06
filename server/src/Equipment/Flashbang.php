<?php

namespace cs\Equipment;

use cs\Core\Item;
use cs\Enum\InventorySlot;
use cs\Enum\ItemType;

class Flashbang extends BaseEquipment
{

    private int $quantity = 1;
    protected int $price = 200;

    public function getType(): ItemType
    {
        return ItemType::TYPE_GRENADE;
    }

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_GRENADE_FLASH;
    }

    public function canPurchaseMultipleTime(Item $newItem): bool
    {
        return ($this->getQuantity() < 2);
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getMaxQuantity(): int
    {
        return 2;
    }

    public function getMaxBuyCount(): int
    {
        return 2;
    }

    public function incrementQuantity(): void
    {
        $this->quantity++;
    }


}
