<?php

namespace cs\Equipment;

use cs\Enum\InventorySlot;

class Decoy extends Grenade
{

    protected int $price = 50;

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_GRENADE_DECOY;
    }


}
