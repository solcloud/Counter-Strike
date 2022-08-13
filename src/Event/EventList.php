<?php

namespace cs\Event;

final class EventList
{

    public const map = [
        'unknown'                    => 0,
        GameOverEvent::class         => 1,
        PauseStartEvent::class       => 2,
        PauseEndEvent::class         => 3,
        RoundStartEvent::class       => 4,
        RoundEndEvent::class         => 5,
        GameStartEvent::class        => 6,
        RoundEndCoolDownEvent::class => 7,
        KillEvent::class             => 8,
    ];

}
