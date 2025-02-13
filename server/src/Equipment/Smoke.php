<?php

namespace cs\Equipment;

use cs\Enum\InventorySlot;
use cs\Interface\Volumetric;

class Smoke extends Grenade implements Volumetric
{
    public const int MAX_HEIGHT = 350;
    public const int MAX_CORNER_HEIGHT = 270;
    public const int MAX_TIME_MS = 18_000;
    protected int $price = 300;

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_GRENADE_SMOKE;
    }

    public function getSpawnAreaMetersSquared(): int
    {
        return 120;
    }

    public function getMaxTimeMs(): int
    {
        return self::MAX_TIME_MS;
    }

    public function getMaxAreaMetersSquared(): int
    {
        return 210_000;
    }
}
