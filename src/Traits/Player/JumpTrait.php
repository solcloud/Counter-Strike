<?php

namespace cs\Traits\Player;

use cs\Core\Point;
use cs\Event\JumpEvent;

trait JumpTrait
{
    public function jump(): void
    {
        if (!$this->canJump()) {
            return;
        }

        if (isset($this->eventsCache[$this->eventIdJump])) {
            /** @var JumpEvent $event */
            $event = $this->eventsCache[$this->eventIdJump];
            $event->reset();
            $this->addEvent($event, $this->eventIdJump);
            return;
        }

        $event = new JumpEvent(function (): void {
            $this->setActiveFloor(null);
            $targetYPosition = $this->position->y + static::speedJump;
            for ($y = $this->position->y + 1; $y <= $targetYPosition; $y++) {
                $floorCandidate = $this->world->findFloor(new Point($this->position->getX(), $y, $this->position->getZ()), $this->getBoundingRadius());
                if ($floorCandidate) {
                    $targetYPosition = $y - 1;
                    break;
                }
            }

            $this->position->setY($targetYPosition);
        }, static::tickCountJump);

        $this->addEvent($event, $this->eventIdJump);
        $this->eventsCache[$this->eventIdJump] = $event;
    }

    public function isJumping(): bool
    {
        return (isset($this->events[$this->eventIdJump]));
    }

    public function canJump(): bool
    {
        return ($this->activeFloor && !$this->isJumping());
    }

}
