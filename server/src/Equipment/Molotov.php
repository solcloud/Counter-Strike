<?php

namespace cs\Equipment;

use cs\Enum\InventorySlot;

class Molotov extends Grenade
{

    protected int $price = 400;

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_GRENADE_MOLOTOV;
    }


}
