<?php

namespace cs\Enum;

enum InventorySlot: int
{

    case SLOT_KNIFE = 0;
    case SLOT_PRIMARY = 1;
    case SLOT_SECONDARY = 2;
    case SLOT_BOMB_KIT = 3;

    case SLOT_GRENADE_DECOY = 4;
    case SLOT_GRENADE_MOLOTOV = 5;
    case SLOT_GRENADE_SMOKE = 6;
    case SLOT_GRENADE_FLASH = 7;
    case SLOT_GRENADE_HE = 8;
    case SLOT_TASER = 9;

}
