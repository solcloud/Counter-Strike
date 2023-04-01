<?php

namespace cs\Weapon;

use cs\Enum\ArmorType;
use cs\Enum\HitBoxType;

final class RifleM4A4 extends AmmoBasedWeapon
{

    public const reloadTimeMs = 3100;
    public const equipReadyTimeMs = 800;
    public const magazineCapacity = 30;
    public const reserveAmmo = 90;
    public const killAward = 300;
    public const runningSpeed = 215;
    public const fireRateMs = 90;
    public const damage = 33;
    public const armorPenetration = 67;
    public const recoilResetMs = 310;
    public const recoilPattern = [
        [0, 0], // fixme better pattern
        [+0.09, +0.17],
        [-0.02, +0.72],
        [+0.04, +1.53],
        [+0.07, +2.32],
        [-0.24, +3.29],
        [-0.47, +4.01],
        [-0.84, +4.55],
        [-0.32, +4.96],
        [+0.74, +4.82],
        [+1.39, +5.08],
        [+1.05, +5.26],
        [+1.49, +5.33],
        [+2.17, +5.09],
        [+2.25, +5.26],
        [+1.26, +5.41],
        [+0.64, +5.53],
        [+0.23, +5.72],
        [-0.45, +5.64],
        [-1.42, +5.42],
        [-0.91, +5.57],
        [-1.04, +5.55],
        [-0.83, +5.71],
        [-0.55, +5.79],
        [-1.06, +5.72],
        [-1.28, +5.83],
        [-0.74, +5.89],
        [+0.19, +5.83],
        [+1.42, +5.23],
        [+1.84, +5.21],
    ];

    protected bool $isWeaponPrimary = true;
    protected int $price = 3100;
    protected int $ammo = self::magazineCapacity;

    public function getDamageValue(HitBoxType $hitBox, ArmorType $armor): int
    {
        return match ($hitBox) {
            HitBoxType::HEAD => $armor->hasArmorHead() ? 92 : 131,
            HitBoxType::CHEST, HitBoxType::BACK => $armor->hasArmor() ? 92 : 131,
            HitBoxType::STOMACH => $armor->hasArmor() ? 28 : 41,
            HitBoxType::LEG => 24,
        };
    }

}
