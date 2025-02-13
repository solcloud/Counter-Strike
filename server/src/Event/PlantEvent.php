<?php

namespace cs\Event;

use Closure;
use cs\Core\Point;

final class PlantEvent extends TimeoutEvent
{

    /** @param Closure(static,int):void $callback */
    public function __construct(Closure $callback, int $timeoutMs, private Point $position)
    {
        parent::__construct($callback, $timeoutMs);
    }

    #[\Override]
    public function serialize(): array
    {
        return [
            'timeMs'   => $this->timeoutMs,
            'position' => $this->position->toArray(),
        ];
    }

}
