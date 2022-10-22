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
    public const range = 150123;

    protected bool $isWeaponPrimary = true;
    protected int $price = 3100;
    protected int $ammo = self::magazineCapacity;

    public function getName(): string
    {
        return 'M4-A4';
    }

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
