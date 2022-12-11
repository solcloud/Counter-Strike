<?php

namespace cs\Weapon;

use cs\Enum\ArmorType;
use cs\Enum\HitBoxType;

final class RifleAk extends AmmoBasedWeapon
{

    public const reloadTimeMs = 2400;
    public const equipReadyTimeMs = 800;
    public const magazineCapacity = 30;
    public const reserveAmmo = 90;
    public const killAward = 300;
    public const runningSpeed = 215;
    public const fireRateMs = 100;
    public const damage = 36;
    public const armorPenetration = 77;
    public const recoilResetMs = 800;
    public const recoilPattern = [
        [0,0],
        [+0.10,+0.19],
        [-0.01,+0.70],
        [+0.06,+1.50],
        [+0.09,+2.36],
        [-0.23,+3.26],
        [-0.46,+4.03],
        [-0.82,+4.54],
        [-0.36,+4.95],
        [+0.76,+4.82],
        [+1.39,+5.06],
        [+1.03,+5.28],
        [+1.47,+5.32],
        [+2.19,+5.07],
        [+2.26,+5.21],
        [+1.25,+5.43],
        [+0.62,+5.57],
        [+0.24,+5.73],
        [-0.47,+5.65],
        [-1.41,+5.42],
        [-0.90,+5.56],
        [-1.03,+5.57],
        [-0.80,+5.73],
        [-0.56,+5.78],
        [-1.09,+5.71],
        [-1.29,+5.85],
        [-0.72,+5.87],
        [+0.17,+5.80],
        [+1.44,+5.22],
        [+1.83,+5.23],
    ];

    protected bool $isWeaponPrimary = true;
    protected int $price = 2700;
    protected int $ammo = self::magazineCapacity;

    public function getDamageValue(HitBoxType $hitBox, ArmorType $armor): int
    {
        return match ($hitBox) {
            HitBoxType::HEAD => $armor->hasArmorHead() ? 111 : 143,
            HitBoxType::CHEST, HitBoxType::BACK => $armor->hasArmor() ? 27 : 35,
            HitBoxType::STOMACH => $armor->hasArmor() ? 34 : 44,
            HitBoxType::LEG => 26,
        };
    }

}
