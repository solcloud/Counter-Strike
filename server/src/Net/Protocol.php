<?php

namespace cs\Net;

use cs\Core\Game;
use cs\Core\Player;

abstract class Protocol
{

    /** @var array<string,positive-int> [methodName => maxCallCountPerTick] */
    public const array playerControlMethods = [
        'attack'   => 1,
        'attack2'  => 1,
        'backward' => 1,
        'buy'      => 9,
        'crouch'   => 1,
        'drop'     => 1,
        'equip'    => 1,
        'forward'  => 1,
        'jump'     => 1,
        'left'     => 1,
        'look'     => 1,
        'reload'   => 1,
        'right'    => 1,
        'run'      => 1,
        'stand'    => 1,
        'use'      => 1,
        'walk'     => 1,
    ];

    /** @var array<string,non-negative-int> [methodName => paramCount] */
    public const array playerControlMethodParamCount = [
        'attack'   => 0,
        'attack2'  => 0,
        'backward' => 0,
        'buy'      => 1,
        'crouch'   => 0,
        'drop'     => 0,
        'equip'    => 1,
        'forward'  => 0,
        'jump'     => 0,
        'left'     => 0,
        'look'     => 2,
        'reload'   => 0,
        'right'    => 0,
        'run'      => 0,
        'stand'    => 0,
        'use'      => 0,
        'walk'     => 0,
    ];

    /** @var array<string,array<int,bool>> [methodName => [paramNumber => true]] */
    public const array methodParamFloat = [
        'look' => [
            1 => true,
            2 => true,
        ],
    ];

    /** @var array<string,non-negative-int> [methodName => methodNumber] */
    public const array playerMethodByName = [
        'attack'   => 0,
        'attack2'  => 1,
        'backward' => 2,
        'buy'      => 3,
        'crouch'   => 4,
        'drop'     => 5,
        'equip'    => 6,
        'forward'  => 7,
        'jump'     => 8,
        'left'     => 9,
        'look'     => 10,
        'reload'   => 11,
        'right'    => 12,
        'run'      => 13,
        'stand'    => 14,
        'use'      => 17,
        'walk'     => 18,
    ];

    /** @var array<non-negative-int,string> [methodNumber => methodName] */
    public const array playerMethodByNumber = [
        0  => 'attack',
        1  => 'attack2',
        2  => 'backward',
        3  => 'buy',
        4  => 'crouch',
        5  => 'drop',
        6  => 'equip',
        7  => 'forward',
        8  => 'jump',
        9  => 'left',
        10 => 'look',
        11 => 'reload',
        12 => 'right',
        13 => 'run',
        14 => 'stand',
        17 => 'use',
        18 => 'walk',
    ];

    public abstract function serializeGameSetting(Player $player, ServerSetting $setting, Game $game): string;

    public abstract function serializeGameState(Game $game): string;

    /** @return positive-int */
    public abstract function getRequestMaxSizeBytes(): int;

    /**
     * @return array<int,array<string|int|float>>
     */
    public abstract function parsePlayerControlCommands(string $msg): array;

}
