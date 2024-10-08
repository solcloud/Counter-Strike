<?php

namespace cs\Event;

use Closure;

class TimeoutEvent extends Event
{
    protected int $tickCountTimeout;

    /** @param ?Closure(static,int):void $callback */
    public function __construct(protected ?Closure $callback, protected int $timeoutMs)
    {
        $this->tickCountTimeout = $this->timeMsToTick($this->timeoutMs);
    }

    final public function process(int $tick): void
    {
        if ($this->tickCountTimeout > 0 && $this->tickCount++ !== $this->tickCountTimeout) {
            return;
        }

        if ($this->callback) {
            call_user_func($this->callback, $this, $tick);
        }
        if ($this->onComplete !== []) {
            $this->runOnCompleteHooks();
        }
    }

}
