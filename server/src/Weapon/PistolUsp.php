<?php

namespace cs\Weapon;

use cs\Enum\ArmorType;
use cs\Enum\BuyMenuItem;
use cs\Enum\HitBoxType;

final class PistolUsp extends AmmoBasedWeapon
{

    public const reloadTimeMs = 2200;
    public const equipReadyTimeMs = 400;
    public const magazineCapacity = 12;
    public const reserveAmmo = 24;
    public const killAward = 300;
    public const fireRateMs = 10;
    public const damage = 116;
    public const range = 5123;

    protected bool $isWeaponPrimary = false;
    protected int $price = 200;
    protected int $ammo = self::magazineCapacity;

    public function getId(): int
    {
        return BuyMenuItem::PISTOL_USP->value;
    }

    public function getName(): string
    {
        return 'USP';
    }

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
