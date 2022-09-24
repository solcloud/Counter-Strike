<?php

use cs\Core\Game;
use cs\Core\GameState;
use cs\Core\Point;
use Test\Simulation\SimulationTester;

return new class extends SimulationTester {

    public function onTickEnd(GameState $state, int $tick): void
    {
        $pp = $state->getPlayer(1)->getPositionImmutable();
        if ($pp->z > 2435) {
            $this->fail("Outside map, tick: $tick");
        }
        if ($pp->y === 0 && $pp->z === 2435) {
            if ($pp->x === 1169) {
                $this->fail("Inside Box, tick: $tick");
            }
            if ($pp->x === 1157) {
                $this->fail("Inside Box, tick: $tick");
            }
            if ($pp->x === 1163) {
                $this->fail("Inside Box, tick: $tick");
            }
        }
    }

    public function onGameEnd(Game $game): void
    {
        $this->assertPositionSame(new Point(1334, 79, 2435), $game->getPlayer(1)->getPositionImmutable());
    }

};
