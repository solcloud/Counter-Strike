<?php

namespace cs\Equipment;

use cs\Core\Item;
use cs\Enum\ArmorType;
use cs\Enum\InventorySlot;
use cs\Enum\ItemType;

class Kevlar extends BaseEquipment
{

    private int $armor = 100;
    private ArmorType $type;

    public function __construct(private bool $bodyPlusHelmet)
    {
        parent::__construct(true);
        $this->type = $bodyPlusHelmet ? ArmorType::BODY_AND_HEAD : ArmorType::BODY;
    }

    public function getArmor(): int
    {
        return $this->armor;
    }

    public function repairArmor(): void
    {
        $this->armor = 100;
    }

    public function lowerArmor(int $armorDamage): void
    {
        assert($armorDamage >= 0);
        $this->armor -= $armorDamage;
        if ($this->armor <= 0) {
            $this->armor = 0;
            $this->type = ArmorType::NONE;
        }
    }

    public function getArmorType(): ArmorType
    {
        return $this->type;
    }

    public function getType(): ItemType
    {
        return ItemType::TYPE_KEVLAR;
    }

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_KEVLAR;
    }

    public function canBeEquipped(): bool
    {
        return false;
    }

    public function isUserDroppable(): bool
    {
        return false;
    }

    public function getMaxBuyCount(): int
    {
        return 5;
    }

    public function canPurchaseMultipleTime(Item $newSlotItem): bool
    {
        /** @var self $newSlotItem */
        if ($this->armor < 100) {
            return true;
        }
        return ($this->type === ArmorType::BODY && $newSlotItem->type === ArmorType::BODY_AND_HEAD);
    }

    public function getPrice(?Item $alreadyHaveSlotItem = null): int
    {
        /** @var ?self $alreadyHaveSlotItem */
        if ($alreadyHaveSlotItem && $this->type === ArmorType::BODY_AND_HEAD && $alreadyHaveSlotItem->type === ArmorType::BODY && $alreadyHaveSlotItem->armor === 100) {
            return 350;
        }
        if ($alreadyHaveSlotItem && $alreadyHaveSlotItem->type === ArmorType::BODY_AND_HEAD) {
            return 650;
        }
        return $this->bodyPlusHelmet ? 1000 : 650;
    }

}
