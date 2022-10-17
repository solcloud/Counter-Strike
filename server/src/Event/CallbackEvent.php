<?php

namespace cs\Event;

use Closure;

class CallbackEvent extends Event
{
    public function __construct(private Closure $callback)
    {
    }

    final public function process(int $tick): void
    {
        call_user_func($this->callback, $this, $tick);
    }

}
