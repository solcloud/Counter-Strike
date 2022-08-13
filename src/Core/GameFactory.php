<?php

namespace cs\Core;

class GameFactory
{

    public static function createDefault5v5Competitive(): Game
    {
        $properties = new GameProperty();

        return new Game($properties);
    }

    public static function createTest(): Game
    {
        $properties = new GameProperty();
        $properties->randomize_spawn_position = false;
        $properties->max_rounds = 4;
        $properties->freeze_time_sec = 0;
        $properties->half_time_freeze_sec = 0;
        $properties->round_end_cool_down_sec = 0;

        return new Game($properties);
    }

    /**
     * @codeCoverageIgnore
     */
    public static function createDebug(): Game
    {
        $properties = new GameProperty();
        $properties->start_money = 6123;
        $properties->max_rounds = 22;
        $properties->freeze_time_sec = 0;
        $properties->half_time_freeze_sec = 0;
        $properties->round_time_ms = 30123;
        $properties->randomize_spawn_position = false;

        return new Game($properties);
    }

}
