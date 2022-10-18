<?php

namespace cs\Weapon;

use cs\Enum\ArmorType;
use cs\Enum\BuyMenuItem;
use cs\Enum\HitBoxType;

final class PistolP250 extends AmmoBasedWeapon
{

    public const reloadTimeMs = 2200;
    public const equipReadyTimeMs = 400;
    public const magazineCapacity = 13;
    public const reserveAmmo = 26;
    public const killAward = 300;
    public const fireRateMs = 35;
    public const damage = 61;
    public const range = 5123;

    protected bool $isWeaponPrimary = false;
    protected int $price = 300;
    protected int $ammo = self::magazineCapacity;

    public function getId(): int
    {
        return BuyMenuItem::PISTOL_P250->value;
    }

    public function getName(): string
    {
        return 'P-250';
    }

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
