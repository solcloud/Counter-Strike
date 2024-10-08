<?php

namespace cs\Event;

use Closure;

class CallbackEvent extends Event
{
    /** @param Closure(static,int):void $callback */
    public function __construct(private readonly Closure $callback)
    {
    }

    final public function process(int $tick): void
    {
        call_user_func($this->callback, $this, $tick);
    }

}
