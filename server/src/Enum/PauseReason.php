<?php

namespace cs\Enum;

enum PauseReason: int
{
    case FREEZE_TIME = 1;
    case TIMEOUT_ATTACKERS = 2;
    case TIMEOUT_DEFENDERS = 3;
    case HALF_TIME = 4;

}
