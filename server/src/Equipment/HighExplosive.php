<?php

namespace cs\Equipment;

use cs\Enum\InventorySlot;

class HighExplosive extends Grenade
{

    protected const int DAMAGE = 20;
    protected const int MAX_BLAST_RADIUS = 400;
    protected const int MAX_BLAST_RADIUS_SQUARED = self::MAX_BLAST_RADIUS * self::MAX_BLAST_RADIUS;

    protected int $price = 300;

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_GRENADE_HE;
    }

    public function getMaxBlastRadius(): int
    {
        return self::MAX_BLAST_RADIUS;
    }

    public function calculateDamage(int $distanceSquared, bool $harArmor): int
    {
        $distanceSquared = max(1, $distanceSquared);
        if ($distanceSquared >= self::MAX_BLAST_RADIUS_SQUARED) {
            return 0; // @codeCoverageIgnore
        }

        $damage = self::DAMAGE * (1 - ($distanceSquared / self::MAX_BLAST_RADIUS_SQUARED));
        return (int) ceil($harArmor ? $damage * 0.3 : $damage);
    }

}
