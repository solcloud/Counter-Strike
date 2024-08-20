<?php

namespace cs\Equipment;

use cs\Core\Item;
use cs\Enum\InventorySlot;

class Flashbang extends Grenade
{

    private int $quantity = 1;
    protected int $price = 200;

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_GRENADE_FLASH;
    }

    public function canPurchaseMultipleTime(Item $newSlotItem): bool
    {
        return ($this->getQuantity() < 2);
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function clone(): static
    {
        $clone = clone $this;
        $clone->quantity = 1;
        return $clone;
    }

    public function getMaxQuantity(): int
    {
        return 2;
    }

    public function getMaxBuyCount(): int
    {
        return 2;
    }

    public function decrementQuantity(): void
    {
        $this->quantity--;
    }

    public function incrementQuantity(): void
    {
        $this->quantity++;
    }


}
