<?php

namespace cs\Equipment;

use cs\Enum\InventorySlot;

class HighExplosive extends Grenade
{

    protected int $price = 300;

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_GRENADE_HE;
    }


}
