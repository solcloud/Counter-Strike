<?php

namespace Test\Simulation;

use cs\Core\Game;
use cs\Core\GameState;
use cs\Event\Event;
use Test\BaseTest;

abstract class SimulationTester extends BaseTest
{

    public function __construct()
    {
        parent::__construct(get_class($this));
    }

    public function onGameStart(Game $game): void
    {
        // empty hook
    }

    public function onTickStart(GameState $state, int $tick): void
    {
        // empty hook
    }

    public function onTickEnd(GameState $state, int $tick): void
    {
        // empty hook
    }

    /**
     * @param Event[] $events
     */
    public function onEvents(array $events): void
    {
        // empty hook
    }

    public function onGameEnd(Game $game): void
    {
        // empty hook
    }

}
