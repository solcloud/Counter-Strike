<?php

namespace cs\Weapon;

use cs\Core\Item;

abstract class BaseWeapon extends Item
{

    public const int magazineCapacity = 0;
    public const int reserveAmmo = 0;
    public const int killAward = 0;
    public const int runningSpeed = 0;
    public const int reloadTimeMs = 0;
    public const int fireRateMs = 0;
    public const int recoilResetMs = 0;
    public const int damage = 0;
    public const int armorPenetration = 0;
    public const int range = 5123;
    public const int rangeMaxDamage = 5123;
    /** @var list<array{float,float}> */
    public const array recoilPattern = [];

}
