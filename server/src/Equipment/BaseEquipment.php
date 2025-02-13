<?php

namespace cs\Equipment;

use cs\Core\Item;

abstract class BaseEquipment extends Item
{

    #[\Override]
    public function getMaxBuyCount(): int
    {
        return 1;
    }

}
