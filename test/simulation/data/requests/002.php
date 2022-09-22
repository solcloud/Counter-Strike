<?php

use cs\Core\Game;
use cs\Core\GameState;
use cs\Core\Point;
use Test\Simulation\SimulationTester;

return new class extends SimulationTester {

    public function onTickEnd(GameState $state, int $tick): void
    {
        $this->assertPositionNotSame(new Point(1169, 0, 2435), $state->getPlayer(1)->getPositionImmutable(), "Tick: $tick");
        $this->assertPositionNotSame(new Point(1157, 0, 2435), $state->getPlayer(1)->getPositionImmutable(), "Tick: $tick");
        $this->assertPositionNotSame(new Point(1163, 0, 2435), $state->getPlayer(1)->getPositionImmutable(), "Tick: $tick");
    }

    public function onGameEnd(Game $game): void
    {
        $this->assertPositionSame(new Point(1334, 79, 2435), $game->getPlayer(1)->getPositionImmutable());
    }

};
