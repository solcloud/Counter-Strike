<?php

namespace Test;

use Closure;
use cs\Core\Game;

/**
 * @deprecated OG test only
 */
class TestGame extends Game
{
    private int $tickMax = 1;
    private ?Closure $onTickCallback = null;
    private ?Closure $onEventsCallback = null;

    public function setTickMax(int $tickMax): void
    {
        $this->tickMax = $tickMax;
    }

    public function start(): void
    {
        for ($tickId = 0; $tickId < $this->tickMax; $tickId++) {
            if ($this->onTickCallback) {
                call_user_func($this->onTickCallback, $this->getState());
            }
            $this->tick($tickId);
            $events = $this->consumeTickEvents();
            if ($this->onEventsCallback && $events !== []) {
                call_user_func($this->onEventsCallback, $events);
            }
        }
    }

    /**
     * @param Closure $callback function(GameState $state):void {}
     * @deprecated
     */
    public function onTick(Closure $callback): void
    {
        $this->onTickCallback = $callback;
    }


    /**
     * @param Closure $callback function(array $events):void {}
     */
    public function onEvents(Closure $callback): void
    {
        $this->onEventsCallback = $callback;
    }

}

