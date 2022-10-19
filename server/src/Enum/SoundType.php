<?php

namespace cs\Enum;

enum SoundType: int
{

    case PLAYER_GROUND_TOUCH = 0;
    case PLAYER_STEP = 1;
    case ITEM_ATTACK = 2;
    case ITEM_ATTACK2 = 3;
    case ITEM_DROP = 4;
    case ITEM_BUY = 5;
    case BULLET_HIT = 6;
    case PLAYER_DEAD = 7;
    case ITEM_RELOAD = 8;
    case BULLET_HIT_HEADSHOT = 9;
    case ATTACK_NO_AMMO = 10;
    case BOMB_PLANTED = 11;

}
