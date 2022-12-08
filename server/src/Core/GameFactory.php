<?php

namespace cs\Core;

class GameFactory
{

    public static function createDefaultCompetitive(): Game
    {
        $properties = new GameProperty();
        //$properties->backtrack_history_tick_count = 1;

        return new Game($properties);
    }

    public static function createDebug(): Game
    {
        $properties = new GameProperty();
        $properties->start_money = 6123;
        $properties->max_rounds = 22;
        $properties->freeze_time_sec = 0;
        $properties->half_time_freeze_sec = 0;
        $properties->round_time_ms = 982123;
        $properties->round_end_cool_down_sec = 0;
        $properties->randomize_spawn_position = false;

        return new Game($properties);
    }

}
