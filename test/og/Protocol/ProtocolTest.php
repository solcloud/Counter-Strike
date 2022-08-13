<?php

namespace Test\Protocol;

use cs\Core\Game;
use cs\Core\GameProperty;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Enum\Color;
use cs\Net\PlayerControl;
use cs\Net\Protocol;
use cs\Net\ProtocolReader;
use cs\Net\ProtocolWriter;
use Test\BaseTestCase;

class ProtocolTest extends BaseTestCase
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
                ['lookAt', -45, 124],
                ['right'],
            ],
            $protocol->parsePlayerControlCommands(implode(
                    $protocol::separator,
                    [
                        "forward", "left", "equip 42", "lookAt -45 124", "right",
                    ]
                )
            )
        );

        $this->assertSame([], $protocol->parsePlayerControlCommands("invalidMethod"));
        $this->assertSame([], $protocol->parsePlayerControlCommands(" move"));
        $this->assertSame([], $protocol->parsePlayerControlCommands("equip knife"));
        $this->assertSame([], $protocol->parsePlayerControlCommands("lookAt 1 one"));
        $this->assertSame([], $protocol->parsePlayerControlCommands("lookAt 1"));
    }

}
