<?php

namespace cs\Weapon;

use cs\Enum\ArmorType;
use cs\Enum\HitBoxType;

final class PistolGlock extends AmmoBasedWeapon
{

    public const reloadTimeMs = 2300;
    public const equipReadyTimeMs = 400;
    public const magazineCapacity = 12;
    public const reserveAmmo = 120;
    public const killAward = 300;
    public const fireRateMs = 35;
    public const damage = 61;
    public const range = 5123;

    protected bool $isWeaponPrimary = false;
    protected int $price = 200;
    protected int $ammo = self::magazineCapacity;


    public function getDamageValue(HitBoxType $hitBox, ArmorType $armor): int
    {
        return match ($hitBox) {
            HitBoxType::HEAD => $armor->hasArmorHead() ? 56 : 119,
            HitBoxType::CHEST, HitBoxType::BACK => $armor->hasArmor() ? 14 : 29,
            HitBoxType::STOMACH => $armor->hasArmor() ? 17 : 37,
            HitBoxType::LEG => 22,
        };
    }

}
