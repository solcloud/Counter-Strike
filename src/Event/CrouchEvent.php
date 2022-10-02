<?php

namespace cs\Event;

use Closure;
use cs\Core\Action;

final class CrouchEvent extends TickEvent
{
    public readonly int $moveOffset;

    public function __construct(public bool $directionDown, Closure $callback)
    {
        parent::__construct($callback, Action::tickCountCrouch());
        $this->moveOffset = Action::crouchDistancePerTick();
    }

}
