<?php

namespace cs\Event;

use Closure;
use cs\Core\Game;
use cs\Enum\PauseReason;

final class PauseStartEvent extends TimeoutEvent
{

    /** @param Closure(static,int):void $callback */
    public function __construct(private Game $game, private PauseReason $reason, Closure $callback, int $timeoutMs)
    {
        parent::__construct($callback, $timeoutMs);
    }

    public function serialize(): array
    {
        return [
            'score'  => $this->game->getScore()->toArray(),
            'reason' => $this->reason->value,
            'ms'     => $this->timeoutMs,
        ];
    }

}
