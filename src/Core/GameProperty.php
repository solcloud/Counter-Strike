<?php

namespace cs\Core;

class GameProperty
{

    const START_MONEY = 'start_money';
    public int $start_money = 800;
    const RANDOMIZE_SPAWN_POSITION = 'randomize_spawn_position';
    public bool $randomize_spawn_position = true;
    const MAX_ROUNDS = 'max_rounds';
    public int $max_rounds = 30;
    const ROUND_TIME_MS = 'round_time_ms';
    public int $round_time_ms = 115000; // 1:55 min
    const HALF_TIME_FREEZE_SEC = 'half_time_freeze_sec';
    public int $half_time_freeze_sec = 4;
    const FREEZE_TIME_SEC = 'freeze_time_sec';
    public int $freeze_time_sec = 15;
    const ROUND_END_COOL_DOWN_SEC = 'round_end_cool_down_sec';
    public int $round_end_cool_down_sec = 4;
    /** @var int[] */
    public array $loss_bonuses = [1400, 1900, 2400, 2900, 3400];


    public function __set(string $name, mixed $value): void
    {
        throw new GameException("Invalid field '{$name}' given");
    }

    public function __get(string $name): never
    {
        throw new GameException("Invalid field '{$name}' given");
    }

    /**
     * @param array<string,string|int|bool> $params
     */
    public static function fromArray(array $params): self
    {
        $gp = new self();
        foreach ($params as $paramName => $value) {
            $gp->{$paramName} = $value;
        }

        return $gp;
    }

    /**
     * @return array<string,string|int|bool|int[]>
     */
    public function toArray()
    {
        return (array)$this;
    }

}
