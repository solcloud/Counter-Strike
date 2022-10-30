<?php

namespace cs\Event;

use Closure;
use cs\Core\Point;

final class PlantEvent extends TimeoutEvent
{

    public function __construct(Closure $callback, int $timeoutMs, private Point $position)
    {
        parent::__construct($callback, $timeoutMs);
    }

    public function serialize(): array
    {
        return [
            'timeMs'   => $this->timeoutMs,
            'position' => $this->position->toArray(),
        ];
    }

}
