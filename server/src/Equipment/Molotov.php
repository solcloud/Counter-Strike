<?php

namespace cs\Equipment;

use cs\Enum\InventorySlot;
use cs\Interface\Flammable;

class Molotov extends Grenade implements Flammable
{
    public const int MAX_TIME_MS = 7_000;

    protected int $price = 400;

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_GRENADE_MOLOTOV;
    }

    public function getMaxTimeMs(): int
    {
        return self::MAX_TIME_MS;
    }

    public function getSpawnAreaMetersSquared(): int
    {
        return 100;
    }

    public function getMaxAreaMetersSquared(): int
    {
        return 200_000;
    }

    public function calculateDamage(bool $hasKevlar): int
    {
        return $hasKevlar ? 8 : 4;
    }

}
