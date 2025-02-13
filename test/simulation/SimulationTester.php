<?php

namespace Test\Simulation;

use cs\Core\Game;
use cs\Core\GameState;
use cs\Core\Point;
use cs\Event\Event;
use PHPUnit\Framework\Assert;

abstract class SimulationTester extends Assert
{

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

    public function assertPositionSame(Point $expected, Point $actual, string $extraMsg = ''): void
    {
        self::assertTrue($expected->equals($actual), "Expected: {$expected} <> {$actual} actual." . $extraMsg);
    }

}
