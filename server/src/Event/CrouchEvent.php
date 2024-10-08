<?php

namespace cs\Event;

use Closure;
use cs\Core\Setting;

final class CrouchEvent extends TickEvent
{
    public readonly int $moveOffset;

    /** @param Closure(static,int):void $callback */
    public function __construct(public bool $directionDown, Closure $callback)
    {
        parent::__construct($callback, Setting::tickCountCrouch());
        $this->moveOffset = Setting::crouchDistancePerTick();
    }

    public function restartTimer(): void
    {
        $this->tickCount = 0;
    }

}
