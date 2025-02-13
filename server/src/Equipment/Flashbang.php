<?php

namespace cs\Equipment;

use cs\Core\Item;
use cs\Enum\InventorySlot;

class Flashbang extends Grenade
{

    /** @var positive-int */
    private int $quantity = 1;
    protected int $price = 200;

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_GRENADE_FLASH;
    }

    #[\Override]
    public function canPurchaseMultipleTime(Item $newSlotItem): bool
    {
        return ($this->getQuantity() < 2);
    }

    #[\Override]
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

    #[\Override]
    public function getMaxQuantity(): int
    {
        return 2;
    }

    #[\Override]
    public function getMaxBuyCount(): int
    {
        return 2;
    }

    #[\Override]
    public function decrementQuantity(): void
    {
        assert($this->quantity > 1);
        $this->quantity--;
    }

    #[\Override]
    public function incrementQuantity(): void
    {
        $this->quantity++;
    }


}
