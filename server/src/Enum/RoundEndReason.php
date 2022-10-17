<?php

namespace cs\Enum;

enum RoundEndReason: int
{

    case ALL_ENEMIES_ELIMINATED = 0;
    case TIME_RUNS_OUT = 1;
    case BOMB_DEFUSED = 2;
    case BOMB_EXPLODED = 3;

}
