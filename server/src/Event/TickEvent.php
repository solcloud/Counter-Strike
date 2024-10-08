<?php

namespace cs\Event;

use Closure;

class TickEvent extends Event
{

    /** @param ?Closure(static,int):void $callback */
    public function __construct(protected ?Closure $callback = null, protected int $maxTickCount = 1)
    {
    }

    final public function process(int $tick): void
    {
        $this->tickCount++;
        if ($this->callback) {
            call_user_func($this->callback, $this, $tick);
        }
        if ($this->onComplete !== [] && ($this->maxTickCount === 0 || $this->tickCount === $this->maxTickCount)) {
            $this->runOnCompleteHooks();
        }
    }

}
