<?php

use cs\Core\Game;
use cs\Event\KillEvent;
use Test\Simulation\SimulationTester;

return new class extends SimulationTester {

    private int $killEvents = 0;

    public function onEvents(array $events): void
    {
        foreach ($events as $event) {
            if ($event instanceof KillEvent) {
                $this->killEvents++;
                $this->assertTrue($event->wasHeadShot());
            }
        }
    }

    public function onGameEnd(Game $game): void
    {
        $this->assertSame(2, $this->killEvents);
        $this->assertSame(3, $game->getRoundNumber());
        $this->assertSame(2, $game->getScore()->getScoreAttackers());
        $this->assertSame(0, $game->getScore()->getScoreDefenders());
    }

};
