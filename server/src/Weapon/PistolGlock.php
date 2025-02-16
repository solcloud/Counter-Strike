<?php

namespace cs\Weapon;

use cs\Enum\ArmorType;
use cs\Enum\HitBoxType;

final class PistolGlock extends AmmoBasedWeapon
{

    public const int reloadTimeMs = 2300;
    public const int equipReadyTimeMs = 400;
    public const int magazineCapacity = 12;
    public const int reserveAmmo = 120;
    public const int killAward = 300;
    public const int fireRateMs = 150;
    public const int damage = 110;
    public const int rangeMaxDamage = 2600;
    public const int recoilResetMs = 300;
    public const array recoilPattern = [
        [0, 0],
        [+0.12, +0.19],
        [+0.13, +0.32],
        [+0.24, +0.43],
        [+0.21, +0.64],
        [+0.24, +0.89],
        [+0.12, +1.11],
        [-0.09, +1.25],
        [-0.12, +1.39],
        [+0.16, +1.54],
        [+0.33, +1.85],
        [-0.68, +2.09],
    ];

    protected bool $isWeaponPrimary = false;
    protected int $price = 200;
    protected int $ammo = self::magazineCapacity;

    public function getDamageValue(HitBoxType $hitBox, ArmorType $armor): int
    {
        return match ($hitBox) {
            HitBoxType::HEAD => $armor->hasArmorHead() ? 56 : 119,
            HitBoxType::CHEST, HitBoxType::BACK => $armor->hasArmorBody() ? 14 : 29,
            HitBoxType::STOMACH => $armor->hasArmorBody() ? 17 : 37,
            HitBoxType::LEG => 22,
        };
    }

}
