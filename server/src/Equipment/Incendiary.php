<?php

namespace cs\Equipment;

use cs\Enum\InventorySlot;
use cs\Enum\ItemType;

class Incendiary extends Grenade
{

    protected int $price = 600;

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_GRENADE_MOLOTOV;
    }


}
