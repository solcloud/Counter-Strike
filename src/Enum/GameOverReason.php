<?php

namespace cs\Enum;

enum GameOverReason: int
{

    case REASON_NOT_ALL_PLAYERS_CONNECTED = 1;
    case ATTACKERS_WINS = 2;
    case DEFENDERS_WINS = 3;
    case TIE = 4;
    case ATTACKERS_SURRENDER = 5;
    case DEFENDERS_SURRENDER = 6;
    case SERVER_ERROR = 9;

}
