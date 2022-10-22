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
    public const range = 250123;

    protected bool $isWeaponPrimary = true;
    protected int $price = 2700;
    protected int $ammo = self::magazineCapacity;

    public function getName(): string
    {
        return 'AK-47';
    }

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
