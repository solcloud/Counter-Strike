<?php

use cs\Core\Game;
use cs\Core\GameState;
use cs\Core\Point;
use Test\Simulation\SimulationTester;

return new class extends SimulationTester {

    public function onTickEnd(GameState $state, int $tick): void
    {
        $pp = $state->getPlayer(1)->getPositionImmutable();
        if ($pp->y === 0 && $pp->x > 1156 && $pp->x < 1270 && $pp->z < 80) {
            $this->fail("Inside Box, tick: $tick");
        }
        $this->assertPositionNotSame(new Point(1245, 0, 50), $state->getPlayer(1)->getPositionImmutable(), "Tick: $tick");
    }

    public function onGameEnd(Game $game): void
    {
        $this->assertPositionSame(new Point(1483, 0, 45), $game->getPlayer(1)->getPositionImmutable());
    }

};
