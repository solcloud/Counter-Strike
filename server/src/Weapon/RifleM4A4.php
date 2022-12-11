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
    public const recoilResetMs = 700;
    public const recoilPattern = [
        [0, 0],
        [-0.60, -1.21],
        [+0.49, -0.6],
        [-1.74, +0.4],
        [-0.11, +1.86],
        [+0.77, +4.46],
        [-2.26, +2.73],
        [+0.78, +5.64],
        [-0.66, +4.75],
        [+1.26, +4.02],
        [+2.09, +5.26],
        [-0.87, +6.18],
        [+1.57, +4.02],
        [+1.19, +6.07],
        [+1.46, +3.51],
        [+2.15, +3.93],
        [+0.02, +6.27],
        [-1.26, +5.13],
        [-0.17, +4.25],
        [-2.31, +6.72],
        [-2.60, +6.36],
        [-1.43, +6.87],
        [+0.60, +4.83],
        [-1.56, +6.48],
        [+0.31, +5.01],
        [-1.99, +5.65],
        [-0.92, +7.47],
        [-0.43, +5.02],
        [+1.54, +6.52],
        [+3.63, +4.23],
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
