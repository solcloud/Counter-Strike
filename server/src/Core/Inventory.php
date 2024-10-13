<?php

namespace cs\Core;

use cs\Enum\ArmorType;
use cs\Enum\BuyMenuItem;
use cs\Enum\InventorySlot;
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
        $this->store = new BuyMenu($isAttackerSide, []);
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
            $this->lastEquippedGrenadeSlots = InventorySlot::getGrenadeSlotIds();
        } else {
            foreach ($this->items as $item) {
                $item->reset();
            }
            if (!isset($this->items[InventorySlot::SLOT_SECONDARY->value])) {
                $this->items[InventorySlot::SLOT_SECONDARY->value] = ($isAttackerSide ? new PistolGlock(true) : new PistolUsp(true));
            }
        }

        if ($this->has(InventorySlot::SLOT_BOMB->value)) {
            $this->removeBomb();
        }
        $this->store->reset($isAttackerSide, $this->items);
    }

    private function updateEquippedSlot(Item $item): int
    {
        $this->tryRemoveLastEquippedGrenade($item);
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
        $bomb = $this->items[InventorySlot::SLOT_BOMB->value] ?? null;
        if ($bomb) {
            unset($this->items[InventorySlot::SLOT_BOMB->value]);
            return InventorySlot::from($this->updateEquippedSlot($bomb));
        }

        GameException::invalid('You do not have bomb!'); // @codeCoverageIgnore
    }

    public function getEquipped(): Item
    {
        return $this->items[$this->equippedSlot];
    }

    private function tryRemoveLastEquippedGrenade(Item $item): void
    {
        if ($item instanceof Grenade) {
            $index = array_search($item->getSlot()->value, $this->lastEquippedGrenadeSlots, true);
            if (is_int($index)) {
                unset($this->lastEquippedGrenadeSlots[$index]);
            }
        }
    }

    public function removeEquipped(): ?Item
    {
        if (!$this->getEquipped()->isUserDroppable()) {
            return null;
        }

        $item = $this->items[$this->equippedSlot];
        if ($item->getQuantity() === 1) {
            unset($this->items[$this->equippedSlot]);
            $this->updateEquippedSlot($item);
            $item->unEquip();

            return $item;
        }

        $item->decrementQuantity();
        return $item->clone();
    }

    public function removeSlot(int $slot): void
    {
        $item = $this->items[$slot] ?? false;
        if (!$item) {
            return; // @codeCoverageIgnore
        }

        if ($this->equippedSlot === $slot) {
            $this->removeEquipped();
            return;
        }

        unset($this->items[$slot]);
        $this->updateEquippedSlot($item);
    }

    public function canBuy(Item $item): bool
    {
        $alreadyHave = $this->items[$item->getSlot()->value] ?? null;
        if ($item->getPrice($alreadyHave) > $this->dollars) {
            return false;
        }

        if ($alreadyHave) {
            return $alreadyHave->canPurchaseMultipleTime($item);
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
            if ($alreadyHave->getQuantity() < $item->getMaxQuantity()) {
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

        $this->store->confirmPurchase($item);
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
            $this->tryRemoveLastEquippedGrenade($item);
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
