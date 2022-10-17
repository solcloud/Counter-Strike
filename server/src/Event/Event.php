<?php

namespace cs\Event;

use Closure;
use cs\Core\Util;
use cs\Interface\NetSerializable;

abstract class Event implements NetSerializable
{
    protected int $tickCount = 0;
    public int $customId = 0;
    /** @var Closure[] */
    public array $onComplete = [];

    abstract public function process(int $tick): void;

    public function timeMsToTick(int $timeMs): int
    {
        return Util::millisecondsToFrames($timeMs);
    }

    public function reset(): void
    {
        $this->tickCount = 0;
        $this->onComplete = [];
    }

    public function getCode(): int
    {
        return EventList::map[get_class($this)] ?? 0;
    }

    public function serialize(): array
    {
        return [
            'class' => get_class($this),
        ];
    }

}
