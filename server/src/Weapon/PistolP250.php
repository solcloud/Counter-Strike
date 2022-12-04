<?php

namespace cs\Weapon;

use cs\Enum\ArmorType;
use cs\Enum\HitBoxType;

final class PistolP250 extends AmmoBasedWeapon
{

    public const reloadTimeMs = 2200;
    public const equipReadyTimeMs = 400;
    public const magazineCapacity = 13;
    public const reserveAmmo = 26;
    public const killAward = 300;
    public const fireRateMs = 150;
    public const damage = 61;
    public const range = 5123;
    public const recoilResetMs = 400;
    public const recoilPattern = [
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
            HitBoxType::CHEST, HitBoxType::BACK => $armor->hasArmor() ? 24 : 37,
            HitBoxType::STOMACH => $armor->hasArmor() ? 30 : 47,
            HitBoxType::LEG => 28,
        };
    }

}
