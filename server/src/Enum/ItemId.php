<?php

namespace cs\Enum;

use cs\Core\SolidSurface;
use cs\Equipment;
use cs\Weapon;

class ItemId
{

    public const int SOLID_SURFACE = 0;
    public const int BOMB = 50;

    /** @var array<string,int> */
    public static array $map = [
        SolidSurface::class => self::SOLID_SURFACE,

        Weapon\Knife::class       => 1,
        Weapon\PistolGlock::class => 2,
        Weapon\PistolP250::class  => 3,
        Weapon\PistolUsp::class   => 4,
        Weapon\RifleAk::class     => 5,
        Weapon\RifleM4A4::class   => 6,
        Weapon\RifleAWP::class    => 7,

        Equipment\Decoy::class         => 30,
        Equipment\Flashbang::class     => 31,
        Equipment\HighExplosive::class => 32,
        Equipment\Incendiary::class    => 33,
        Equipment\Kevlar::class        => 34,
        Equipment\Molotov::class       => 35,
        Equipment\Smoke::class         => 36,

        Equipment\Bomb::class      => self::BOMB,
        Equipment\DefuseKit::class => 51,
    ];

}
