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
        $item = $this->inventory->dropEquipped();
        if (!$item) {
            return;
        }

        // TODO world drop/pick items
        $sound = new SoundEvent($this->getPositionImmutable(), SoundType::ITEM_DROP);
        $this->world->makeSound($sound->setPlayer($this)->setItem($item));
    }

    public function buyItem(BuyMenuItem $item): bool
    {
        if (!$this->world->canBuy($this)) {
            return false;
        }

        $equipEvent = $this->inventory->purchase($item);
        if ($equipEvent) {
            $this->addEvent($equipEvent, $this->eventIdPrimary);
            $sound = new SoundEvent($this->getPositionImmutable()->addY($this->getSightHeight()), SoundType::ITEM_BUY);
            $this->world->makeSound($sound->setPlayer($this));
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