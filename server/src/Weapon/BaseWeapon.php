<?php

namespace cs\Weapon;

use cs\Core\Item;

abstract class BaseWeapon extends Item
{

    public const magazineCapacity = 0;
    public const reserveAmmo = 0;
    public const killAward = 0;
    public const runningSpeed = 0;
    public const reloadTimeMs = 0;
    public const fireRateMs = 0;
    public const recoilResetMs = 0;
    public const damage = 0;
    public const armorPenetration = 0;
    public const range = 5123;
    public const rangeMaxDamage = 5123;
    public const recoilPattern = [];

}
