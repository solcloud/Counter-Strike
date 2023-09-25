<?php

namespace Test\Unit;

use cs\Core\Game;
use cs\Core\GameProperty;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Enum\Color;
use cs\Map\TestMap;
use cs\Net\PlayerControl;
use cs\Net\Protocol;
use cs\Net\ProtocolReader;
use cs\Net\ProtocolWriter;
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
                'x' => 0,
                'y' => 0,
                'z' => 0,
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

        $expected = [
            'players' => [
                $playerSerializedExpected,
            ],
            'events'  => [],
        ];
        $actual = $protocol->serialize($game->getPlayers(), []);
        $this->assertSame($expected, json_decode($actual, true));
    }

}
