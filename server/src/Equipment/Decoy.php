<?php

namespace cs\Equipment;

use cs\Enum\BuyMenuItem;
use cs\Enum\InventorySlot;
use cs\Enum\ItemType;

class Decoy extends BaseEquipment
{

    protected int $price = 50;

    public function getId(): int
    {
        return BuyMenuItem::GRENADE_DECOY->value;
    }

    public function getType(): ItemType
    {
        return ItemType::TYPE_GRENADE;
    }

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_GRENADE_DECOY;
    }


}
