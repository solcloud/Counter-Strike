<?php

namespace cs\Event;

use Closure;
use cs\Core\Player;

final class CrouchEvent extends TickEvent
{
    public readonly int $moveOffset;

    public function __construct(public bool $directionDown, Closure $callback)
    {
        parent::__construct($callback, Player::tickCountCrouch);
        $this->moveOffset = (Player::headHeightStand - Player::headHeightCrouch) / $this->maxTickCount;
    }

}
