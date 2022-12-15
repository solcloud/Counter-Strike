<?php

use cs\Core\Game;
use cs\Core\GameState;
use cs\Core\Point;
use Test\Simulation\SimulationTester;

return new class extends SimulationTester {

    public function onTickEnd(GameState $state, int $tick): void
    {
        $pp = $state->getPlayer(1)->getPositionClone();
        if ($pp->y === 0 && $pp->x > 1156 && $pp->x < 1270 && $pp->z < 80) {
            $this->fail("Inside Box, tick: $tick");
        }
        if ($pp->y === 0 && $pp->x === 1245 && $pp->z === 50) {
            $this->fail("Inside Box, tick: $tick");
        }
    }

    public function onGameEnd(Game $game): void
    {
        $this->assertPositionSame(new Point(1461, 0, 45), $game->getPlayer(1)->getPositionClone());
    }

};
