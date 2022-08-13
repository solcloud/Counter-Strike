<?php

namespace cs\Equipment;

use cs\Core\Item;

abstract class BaseEquipment extends Item
{
    public const movementSlowDownFactor = 0.9;

    public function getMaxBuyCount(): int
    {
        return 1;
    }

}
