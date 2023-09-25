<?php

namespace cs\Weapon;

use cs\Enum\ArmorType;
use cs\Enum\HitBoxType;

class RifleAWP extends AmmoBasedWeapon
{

    public const reloadTimeMs = 3700;
    public const equipReadyTimeMs = 1100;
    public const magazineCapacity = 5;
    public const reserveAmmo = 30;
    public const killAward = 100;
    public const fireRateMs = 1463;
    public const damage = 20;
    public const armorPenetration = 90;

    protected bool $isWeaponPrimary = true;
    protected int $price = 4750;
    protected int $ammo = self::magazineCapacity;

    public function getDamageValue(HitBoxType $hitBox, ArmorType $armor): int
    {
        return match ($hitBox) {
            HitBoxType::HEAD => $armor->hasArmorHead() ? 448 : 459,
            HitBoxType::CHEST, HitBoxType::BACK => $armor->hasArmor() ? 112 : 115,
            HitBoxType::STOMACH => $armor->hasArmor() ? 140 : 143,
            HitBoxType::LEG => 85,
        };
    }

}
