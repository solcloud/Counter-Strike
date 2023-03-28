<?php

namespace cs\Event;

use Closure;

class TimeoutEvent extends Event
{
    protected int $tickCountTimeout = 0;
    protected ?Closure $callback = null;

    public function __construct(?Closure $callback, protected int $timeoutMs)
    {
        $this->callback = $callback;
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
            foreach ($this->onComplete as $func) {
                call_user_func($func, $this);
            }
        }
    }

}
