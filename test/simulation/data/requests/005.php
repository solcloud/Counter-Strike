<?php

use cs\Core\Game;
use Test\Simulation\SimulationTester;

return new class extends SimulationTester {

    public function onGameEnd(Game $game): void
    {
        $this->assertLessThanOrEqual(2515, $game->getPlayer(1)->getPositionClone()->z);
    }

};
