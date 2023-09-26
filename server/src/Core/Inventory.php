<?php

namespace cs\Core;

use cs\Enum\ArmorType;
use cs\Enum\BuyMenuItem;
use cs\Enum\InventorySlot;
use cs\Equipment\Flashbang;
use cs\Equipment\Grenade;
use cs\Equipment\Kevlar;
use cs\Event\EquipEvent;
use cs\Weapon\Knife;
use cs\Weapon\PistolGlock;
use cs\Weapon\PistolUsp;

class Inventory
{

    /** @var Item[] [slotId => Item] */
    private array $items = [];
    private int $dollars = 0;
    private int $equippedSlot;
    private int $lastEquippedSlotId;
    /** @var int[] [slotId] */
    private array $lastEquippedGrenadeSlots;
    private BuyMenu $store;

    public function __construct(bool $isAttackerSide)
    {
        $this->reset($isAttackerSide, true);
    }

    public function reset(bool $isAttackerSide, bool $respawn): void
    {
        if ($respawn) {
            $this->items = [
                InventorySlot::SLOT_KNIFE->value     => new Knife(),
                InventorySlot::SLOT_SECONDARY->value => ($isAttackerSide ? new PistolGlock(true) : new PistolUsp(true)),
            ];
            $this->equippedSlot = InventorySlot::SLOT_SECONDARY->value;
            $this->lastEquippedSlotId = InventorySlot::SLOT_KNIFE->value;
            $this->lastEquippedGrenadeSlots = [
                InventorySlot::SLOT_GRENADE_SMOKE->value, InventorySlot::SLOT_GRENADE_MOLOTOV->value, InventorySlot::SLOT_GRENADE_HE->value,
                InventorySlot::SLOT_GRENADE_FLASH->value, InventorySlot::SLOT_GRENADE_DECOY->value,
            ];
        } else {
            foreach ($this->items as $item) {
                $item->reset();
            }
            if (!isset($this->items[InventorySlot::SLOT_SECONDARY->value])) {
                $this->items[InventorySlot::SLOT_SECONDARY->value] = ($isAttackerSide ? new PistolGlock(true) : new PistolUsp(true));
            }
        }

        $this->removeBomb();
        $this->store = new BuyMenu($isAttackerSide, $this->items);
    }

    private function updateEquippedSlot(): int
    {
        if (isset($this->items[$this->equippedSlot])) {
            return $this->equippedSlot;
        }

        if (isset($this->items[$this->lastEquippedSlotId])) {
            $this->equippedSlot = $this->lastEquippedSlotId;
        } else {
            $this->equippedSlot = InventorySlot::SLOT_KNIFE->value;
        }
        return $this->equippedSlot;
    }

    public function removeBomb(): InventorySlot
    {
        unset($this->items[InventorySlot::SLOT_BOMB->value]);
        return InventorySlot::from($this->updateEquippedSlot());
    }

    public function getEquipped(): Item
    {
        return $this->items[$this->equippedSlot];
    }

    public function removeEquipped(): ?Item
    {
        if (!$this->getEquipped()->isUserDroppable()) {
            return null;
        }

        $item = $this->items[$this->equippedSlot];
        if ($item->getQuantity() === 1) {
            unset($this->items[$this->equippedSlot]);
            if ($item instanceof Grenade) {
                unset($this->lastEquippedGrenadeSlots[$this->equippedSlot]);
            }
            $this->updateEquippedSlot();
            $item->unEquip();
        } else {
            $item->decrementQuantity();
        }

        return $item;
    }

    public function removeSlot(int $slot): void
    {
        $item = $this->items[$slot] ?? false;
        if (!$item) {
            return;
        }

        if ($this->equippedSlot === $slot) {
            $this->removeEquipped();
            return;
        }

        unset($this->items[$slot]);
        if ($item instanceof Grenade) {
            unset($this->lastEquippedGrenadeSlots[$slot]);
        }
        $this->updateEquippedSlot();
    }

    public function canBuy(Item $item): bool
    {
        $alreadyHave = $this->items[$item->getSlot()->value] ?? null;
        if ($item->getPrice($alreadyHave) > $this->dollars) {
            return false;
        }

        if ($alreadyHave) {
            return ($alreadyHave->canPurchaseMultipleTime($item));
        }
        return true;
    }

    public function purchase(Player $player, BuyMenuItem $buyCandidate): ?EquipEvent
    {
        $item = $this->store->get($buyCandidate);
        if (!$item || !$this->canBuy($item)) {
            return null;
        }

        $alreadyHave = $this->items[$item->getSlot()->value] ?? null;
        $this->dollars -= $item->getPrice($alreadyHave);
        if ($alreadyHave) {
            if ($alreadyHave instanceof Flashbang) {
                $alreadyHave->incrementQuantity();
                $item = $alreadyHave;
            } elseif ($alreadyHave instanceof Kevlar && $alreadyHave->getArmorType() === ArmorType::BODY_AND_HEAD) {
                $alreadyHave->repairArmor();
                $item = $alreadyHave;
            } elseif ($item->canBeEquipped()) {
                $this->equip($item->getSlot());
                $player->dropEquippedItem();
            }
        }

        $this->store->buy($item);
        $this->items[$item->getSlot()->value] = $item;
        if ($item->canBeEquipped()) {
            return $this->equip($item->getSlot());
        }
        return null;
    }

    public function equip(InventorySlot $slot): ?EquipEvent
    {
        $item = $this->items[$slot->value] ?? false;
        if (!$item) {
            return null;
        }

        $this->items[$this->equippedSlot]->unEquip();
        $this->lastEquippedSlotId = $this->equippedSlot;
        $this->equippedSlot = $slot->value;
        if ($item instanceof Grenade) {
            unset($this->lastEquippedGrenadeSlots[$slot->value]);
            array_unshift($this->lastEquippedGrenadeSlots, $slot->value);
        }
        return $item->equip();
    }

    public function pickup(Item $item): bool
    {
        $haveIt = $this->items[$item->getSlot()->value] ?? false;
        if ($haveIt && $haveIt->getQuantity() === $haveIt->getMaxQuantity()) {
            return false;
        }
        if ($item->getSlot() === InventorySlot::SLOT_KIT && $this->store->forAttackerStore) {
            return false;
        }
        if ($item->getSlot() === InventorySlot::SLOT_BOMB && !$this->store->forAttackerStore) {
            return false;
        }

        if ($haveIt) {
            $haveIt->incrementQuantity();
            return true;
        }
        $this->items[$item->getSlot()->value] = $item;
        return true;
    }

    public function getDollars(): int
    {
        return $this->dollars;
    }

    public function earnMoney(int $amount): void
    {
        $this->dollars += $amount;
        if ($this->dollars < 0) {
            $this->dollars = 0;
        }
        if ($this->dollars > 16000) {
            $this->dollars = 16000;
        }
    }

    /**
     * @return Item[] [slotId => Item]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return array<int,array<string,int>> [slotId => item]
     */
    public function getFilledSlots(): array
    {
        $output = [];
        foreach ($this->items as $key => $item) {
            $output[$key] = $item->toArrayCache;
        }
        return $output;
    }

    /**
     * @return int[]
     */
    public function getLastEquippedGrenadeSlots(): array
    {
        return $this->lastEquippedGrenadeSlots;
    }

    public function getKevlar(): ?Kevlar
    {
        return $this->items[InventorySlot::SLOT_KEVLAR->value] ?? null; // @phpstan-ignore-line
    }

    public function has(int $slotId): bool
    {
        return (isset($this->items[$slotId]));
    }

}
