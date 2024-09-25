<?php

namespace Test\Unit;

use cs\Core\Game;
use cs\Core\GameProperty;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Enum\Color;
use cs\Enum\GameOverReason;
use cs\Event\GameOverEvent;
use cs\Map\TestMap;
use cs\Net\PlayerControl;
use cs\Net\Protocol;
use cs\Net\ServerSetting;
use Test\BaseTest;

class ProtocolTest extends BaseTest
{

    public function testPlayerControlMethods(): void
    {
        $pc = new PlayerControl(new Player(1, Color::GREEN, true), new GameState(new Game(new GameProperty())));
        foreach (Protocol::playerControlMethods as $methodName => $maxCallCount) {
            $this->assertTrue(method_exists($pc, $methodName), "Method: $methodName");
            $this->assertGreaterThan(0, $maxCallCount);
        }
    }

    public function testInvalidCommandsWhenExtendingMaxCallPerTick(): void
    {
        $protocol = new Protocol\TextProtocol();
        $this->assertSame([['attack']], $protocol->parsePlayerControlCommands(implode($protocol::separator, ['attack'])));
        $this->assertSame([], $protocol->parsePlayerControlCommands(implode($protocol::separator, ['attack', 'attack'])));
    }

    public function testTextProtocol(): void
    {
        $protocol = new Protocol\TextProtocol();

        $this->assertSame(
            [
                ['forward'],
                ['left'],
                ['equip', 42],
                ['look', -45.0, 124.0],
                ['right'],
            ],
            $protocol->parsePlayerControlCommands(implode(
                    $protocol::separator,
                    [
                        "forward", "left", "equip 42", "look -45 124", "right",
                    ]
                )
            )
        );

        $this->assertSame([], $protocol->parsePlayerControlCommands("invalidMethod"));
        $this->assertSame([], $protocol->parsePlayerControlCommands(" move"));
        $this->assertSame([], $protocol->parsePlayerControlCommands("equip knife"));
        $this->assertSame([], $protocol->parsePlayerControlCommands("look 1 one"));
        $this->assertSame([], $protocol->parsePlayerControlCommands("look 1"));
    }

    public function testSerialization(): void
    {
        $player = new Player(1, Color::BLUE, true);
        $game = new Game(new GameProperty());
        $game->loadMap(new TestMap());
        $game->addPlayer($player);
        $pp = $player->getPositionClone();
        $player->getSight()->look(12.45, 1.09);
        $protocol = new Protocol\TextProtocol();

        $playerSerializedExpected = [
            'id'          => 1,
            'color'       => 1,
            'money'       => 800,
            'item'        => [
                'id'   => 2,
                'slot' => 2,
            ],
            'canAttack'   => false,
            'canBuy'      => true,
            'canPlant'    => false,
            'slots'       => [
                0 => [
                    'id'   => 1,
                    'slot' => 0,
                ],
                2 => [
                    'id'   => 2,
                    'slot' => 2,
                ],
                3 => [
                    'id'   => 50,
                    'slot' => 3,
                ],
            ],
            'health'      => 100,
            'position'    => [
                'x' => $pp->x,
                'y' => $pp->y,
                'z' => $pp->z,
            ],
            'look'        => [
                'horizontal' => 12.45,
                'vertical'   => 1.09,
            ],
            'isAttacker'  => true,
            'sight'       => $player->getSightHeight(),
            'armor'       => 0,
            'armorType'   => 0,
            'ammo'        => 12,
            'ammoReserve' => 120,
            'isReloading' => false,
            'scopeLevel'  => 0,
        ];
        $this->assertSame($playerSerializedExpected, $player->serialize());

        $event = new GameOverEvent(GameOverReason::REASON_NOT_ALL_PLAYERS_CONNECTED);
        $expected = [
            'players' => [
                $playerSerializedExpected,
            ],
            'events' => [
                [
                    'code' => $event->getCode(),
                    'data' => [
                        'reason' => $event->reason->value,
                    ],
                ],
            ],
        ];
        $actual = $protocol->serialize($game->getPlayers(), [$event]);
        $this->assertSame($expected, json_decode($actual, true));

        $playerJsonSerialized = json_encode($playerSerializedExpected);
        $expectedGameSettingsSerialized = <<<JSON
{
    "events": [
        {
            "code": 6,
            "data": {
                "player": $playerJsonSerialized,
                "playerId": 1,
                "playersCount": 9,
                "setting": {
                    "backtrack_history_tick_count": 0,
                    "bomb_defuse_time_ms": 9960,
                    "bomb_explode_time_ms": 40000,
                    "bomb_plant_time_ms": 3200,
                    "buy_time_sec": 20,
                    "freeze_time_sec": 15,
                    "half_time_freeze_sec": 15,
                    "loss_bonuses": [
                        1400,
                        1900,
                        2400,
                        2900,
                        3400
                    ],
                    "max_rounds": 30,
                    "randomize_spawn_position": true,
                    "round_end_cool_down_sec": 4,
                    "round_time_ms": 115000,
                    "start_money": 800
                },
                "tickMs": 20,
                "warmupSec": 60
            }
        }
    ],
    "players": []
}
JSON;
        $actualGameSettingsSerialized = $protocol->serializeGameSetting($player, new ServerSetting(9, 20), $game);
        $this->assertJsonStringEqualsJsonString($expectedGameSettingsSerialized, $actualGameSettingsSerialized);
    }

}
