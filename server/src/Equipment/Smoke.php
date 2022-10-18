<?php

namespace cs\Equipment;

use cs\Enum\BuyMenuItem;
use cs\Enum\InventorySlot;
use cs\Enum\ItemType;

class Smoke extends BaseEquipment
{

    protected int $price = 300;

    public function getId(): int
    {
        return BuyMenuItem::GRENADE_SMOKE->value;
    }

    public function getType(): ItemType
    {
        return ItemType::TYPE_GRENADE;
    }

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_GRENADE_SMOKE;
    }


}
