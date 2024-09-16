<?php

namespace cs\Traits\Player;

use cs\Core\Inventory;
use cs\Core\Item;
use cs\Enum\BuyMenuItem;
use cs\Enum\InventorySlot;
use cs\Enum\SoundType;
use cs\Event\SoundEvent;

trait InventoryTrait
{

    public function equip(InventorySlot $slot): bool
    {
        if ($slot === InventorySlot::SLOT_KEVLAR || $slot === InventorySlot::SLOT_KIT) {
            return false;
        }

        $event = $this->inventory->equip($slot);
        if ($event) {
            $this->addEvent($event, $this->eventIdPrimary);
            return true;
        }

        return false;
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

    public function dropItemFromSlot(int $slot): bool
    {
        if (!$this->inventory->has($slot)) {
            return false;
        }
        $item = $this->inventory->getItems()[$slot];
        if (!$item->isUserDroppable()) {
            return false;
        }

        $this->inventory->removeSlot($slot);
        $this->world->dropItem($this, $item);
        return true;
    }

    public function dropEquippedItem(): ?Item
    {
        $item = $this->inventory->removeEquipped();
        if (null === $item) {
            return null;
        }

        $this->world->dropItem($this, $item);
        $this->equip($this->getEquippedItem()->getSlot());
        return $item;
    }

    public function buyItem(BuyMenuItem $item): bool
    {
        if (!$this->world->canBuy($this)) {
            return false;
        }

        $equipEvent = $this->inventory->purchase($this, $item);
        if ($equipEvent) {
            $this->addEvent($equipEvent, $this->eventIdPrimary);
            $soundEvent = new SoundEvent($this->getSightPositionClone(), SoundType::ITEM_BUY);
            $this->world->makeSound($soundEvent->setPlayer($this));
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
