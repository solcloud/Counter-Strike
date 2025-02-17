<?php

namespace cs\Event;

use cs\Core\GameException;

abstract class NoTickEvent extends Event
{

    public final function process(int $tick): void
    {
        GameException::invalid(get_class($this)); // @codeCoverageIgnore
    }

}
