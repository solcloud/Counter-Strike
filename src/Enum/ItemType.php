<?php

namespace cs\Enum;

enum ItemType: int
{
    case TYPE_KNIFE = 0;
    case TYPE_WEAPON_PRIMARY = 1;
    case TYPE_WEAPON_SECONDARY = 2;
    case TYPE_BOMB = 5;
    case TYPE_DEFUSE_KIT = 6;
    case TYPE_GRENADE = 7;

}
