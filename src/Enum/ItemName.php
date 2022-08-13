<?php

namespace cs\Enum;

use cs\Weapon\Knife;
use cs\Weapon\PistolGlock;
use cs\Weapon\PistolP250;
use cs\Weapon\PistolUsp;
use cs\Weapon\RifleAk;
use cs\Weapon\RifleM4A4;

final class ItemName
{

    public const map = [
        Knife::class       => 'Knife',
        PistolGlock::class => 'Glock',
        PistolP250::class  => 'P-250',
        PistolUsp::class   => 'USP',
        RifleAk::class     => 'AK-47',
        RifleM4A4::class   => 'M4-A4',
    ];

}
