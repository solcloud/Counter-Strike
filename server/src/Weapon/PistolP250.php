<?php

namespace cs\Weapon;

use cs\Enum\ArmorType;
use cs\Enum\HitBoxType;

final class PistolP250 extends AmmoBasedWeapon
{

    public const int reloadTimeMs = 2200;
    public const int equipReadyTimeMs = 400;
    public const int magazineCapacity = 13;
    public const int reserveAmmo = 26;
    public const int killAward = 300;
    public const int fireRateMs = 150;
    public const int damage = 130;
    public const int rangeMaxDamage = 3000;
    public const int recoilResetMs = 400;
    public const array recoilPattern = [
        [0, 0],
        [-0.12, +0.21],
        [+0.40, +0.46],
        [+0.10, +0.51],
        [+0.28, +0.71],
        [+0.32, +1.01],
        [+0.12, +1.42],
        [-0.24, +1.61],
        [-0.34, +1.84],
        [+0.48, +1.91],
        [+0.58, +2.11],
        [-0.88, +2.49],
        [-1.28, +2.99],
    ];

    protected bool $isWeaponPrimary = false;
    protected int $price = 300;
    protected int $ammo = self::magazineCapacity;

    public function getDamageValue(HitBoxType $hitBox, ArmorType $armor): int
    {
        return match ($hitBox) {
            HitBoxType::HEAD => $armor->hasArmorHead() ? 96 : 151,
            HitBoxType::CHEST, HitBoxType::BACK => $armor->hasArmorBody() ? 24 : 37,
            HitBoxType::STOMACH => $armor->hasArmorBody() ? 30 : 47,
            HitBoxType::LEG => 28,
        };
    }

}
