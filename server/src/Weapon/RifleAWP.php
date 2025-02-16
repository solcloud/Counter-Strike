<?php

namespace cs\Weapon;

use cs\Enum\ArmorType;
use cs\Enum\HitBoxType;
use cs\Interface\ScopeItem;

class RifleAWP extends AmmoBasedWeapon implements ScopeItem
{

    public const int reloadTimeMs = 3700;
    public const int equipReadyTimeMs = 1100;
    public const int magazineCapacity = 5;
    public const int reserveAmmo = 30;
    public const int killAward = 100;
    public const int fireRateMs = 1463;
    public const int damage = 350;
    public const int armorPenetration = 90;

    protected bool $isWeaponPrimary = true;
    protected int $price = 4750;
    protected int $ammo = self::magazineCapacity;

    public function getDamageValue(HitBoxType $hitBox, ArmorType $armor): int
    {
        return match ($hitBox) {
            HitBoxType::HEAD => $armor->hasArmorHead() ? 448 : 459,
            HitBoxType::CHEST, HitBoxType::BACK => $armor->hasArmorBody() ? 112 : 115,
            HitBoxType::STOMACH => $armor->hasArmorBody() ? 140 : 143,
            HitBoxType::LEG => 85,
        };
    }

    #[\Override]
    protected function getSpreadOffsets(): array
    {
        if ($this->scopeLevel === 0) {
            return [rand(30, 60) / (rand(0, 1) === 0 ? -10 : +10), rand(20, 50) / (rand(0, 1) === 0 ? +10 : -10)];
        }

        return [0.0, 0.0];
    }

    public function scope(): void
    {
        $this->scopeLevel++;
        if ($this->scopeLevel > 2) {
            $this->scopeLevel = 0;
        }
    }

    public function isScopedIn(): bool
    {
        return ($this->scopeLevel !== 0);
    }

}
