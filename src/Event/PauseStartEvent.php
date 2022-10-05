<?php

namespace cs\Event;

use Closure;
use cs\Enum\PauseReason;

final class PauseStartEvent extends TimeoutEvent
{

    public function __construct(private PauseReason $reason, Closure $callback, int $timeoutMs)
    {
        parent::__construct($callback, $timeoutMs);
    }

    public function serialize(): array
    {
        return [
            'reason' => $this->reason->value,
            'ms'     => $this->timeoutMs,
        ];
    }

}
