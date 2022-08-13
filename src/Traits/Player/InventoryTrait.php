<?php

namespace cs\Traits\Player;

use cs\Core\Inventory;
use cs\Core\Item;
use cs\Enum\BuyMenuItem;
use cs\Enum\InventorySlot;

trait InventoryTrait
{

    public function equip(InventorySlot $slot): void
    {
        $event = $this->inventory->equip($slot);
        if ($event) {
            $this->addEvent($event, $this->eventIdPrimary);
        }
    }

    public function equipKnife(): void
    {
        $this->equip(InventorySlot::SLOT_KNIFE);
    }

    public function equipPrimaryWeapon(): void
    {
        $this->equip(InventorySlot::SLOT_PRIMARY);
    }

    public function equipSecondaryWeapon(): void
    {
        $this->equip(InventorySlot::SLOT_SECONDARY);
    }

    public function getEquippedItem(): Item
    {
        return $this->inventory->getEquipped();
    }

    public function dropEquippedItem(): void
    {
        $this->inventory->dropEquipped();
    }

    public function buyItem(BuyMenuItem $item): bool
    {
        #if (canBuy()) // todo: check for buy menu area, buy time
        $equipEvent = $this->inventory->purchase($item);
        if ($equipEvent) {
            $this->addEvent($equipEvent, $this->eventIdPrimary);
            return true;
        }

        return false;
    }

    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    public function getMoney(): int
    {
        return $this->inventory->getDollars();
    }

}
