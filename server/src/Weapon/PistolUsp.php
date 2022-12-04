<?php

namespace cs\Weapon;

use cs\Enum\ArmorType;
use cs\Enum\HitBoxType;

final class PistolUsp extends AmmoBasedWeapon
{

    public const reloadTimeMs = 2200;
    public const equipReadyTimeMs = 400;
    public const magazineCapacity = 12;
    public const reserveAmmo = 24;
    public const killAward = 300;
    public const fireRateMs = 170;
    public const damage = 116;
    public const range = 5123;
    public const recoilResetMs = 300;
    public const recoilPattern = [
        [0, 0],
        [-0.02, +0.12],
        [+0.10, +0.36],
        [+0.20, +0.41],
        [+0.18, +0.61],
        [+0.12, +0.91],
        [+0.02, +1.01],
        [-0.04, +1.21],
        [-0.14, +1.34],
        [+0.18, +1.51],
        [+0.38, +1.81],
        [-0.58, +1.99],
    ];

    protected bool $isWeaponPrimary = false;
    protected int $price = 200;
    protected int $ammo = self::magazineCapacity;

    public function getDamageValue(HitBoxType $hitBox, ArmorType $armor): int
    {
        return match ($hitBox) {
            HitBoxType::HEAD => $armor->hasArmorHead() ? 70 : 140,
            HitBoxType::CHEST, HitBoxType::BACK => $armor->hasArmor() ? 17 : 34,
            HitBoxType::STOMACH => $armor->hasArmor() ? 22 : 43,
            HitBoxType::LEG => 26,
        };
    }

}
