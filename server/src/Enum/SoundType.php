<?php

namespace cs\Enum;

enum SoundType: int
{

    case PLAYER_GROUND_TOUCH = 0;
    case PLAYER_STEP = 1;
    case ITEM_ATTACK = 2;
    case ITEM_ATTACK2 = 3;
    case FLAME_SPAWN = 4;
    case ITEM_BUY = 5;
    case BULLET_HIT = 6;
    case PLAYER_DEAD = 7;
    case ITEM_RELOAD = 8;
    case BULLET_HIT_HEADSHOT = 9;
    case ATTACK_NO_AMMO = 10;
    case BOMB_PLANTED = 11;
    case BOMB_PLANTING = 12;
    case BOMB_EXPLODED = 13;
    case BOMB_DEFUSING = 14;
    case BOMB_DEFUSED = 15;
    case ITEM_PICKUP = 16;
    case GRENADE_LAND = 17;
    case GRENADE_BOUNCE = 18;
    case GRENADE_AIR = 19;
    case ITEM_DROP_AIR = 20;
    case ITEM_DROP_LAND = 21;
    case FLAME_EXTINGUISH = 22;

}
