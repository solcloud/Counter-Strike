<?php

use cs\Core\Game;
use cs\Core\GameState;
use cs\Core\Point;
use Test\Simulation\SimulationTester;

return new class extends SimulationTester {

    public function onTickEnd(GameState $state, int $tick): void
    {
        $pp = $state->getPlayer(1)->getPositionClone();
        if ($tick === 1) {
            $this->assertPositionSame(new Point(1261, 0, 533), $pp);
        }
        if ($tick === 70) {
            $this->assertPositionSame(new Point(1261, 0, 534), $pp);
        }

        if ($pp->y < 99 && $pp->z > 400 && $pp->z < 480 && $pp->x > 960 && $pp->x < 1200) {
            $this->fail("Inside Box, tick: $tick");
        }
        if ($pp->y < 99 && $pp->z > 400 - 60 && $pp->z < 480 + 60 && $pp->x > 960 - 60 && $pp->x < 1200 + 60) {
            $this->fail("Inside Box, tick: $tick");
        }
        if ($pp->z === 461 && $pp->x === 1181 && $pp->y < 99) {
            $this->fail("Inside Box, tick: $tick");
        }
        if ($pp->z === 523 && $pp->x === 1192 && $pp->y < 99) {
            $this->fail("Inside Box, tick: $tick");
        }
    }

    public function onGameEnd(Game $game): void
    {
        $this->assertPositionSame(new Point(999, 142, 369), $game->getPlayer(1)->getPositionClone());
    }

};
