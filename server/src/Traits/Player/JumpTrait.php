<?php

namespace cs\Traits\Player;

use cs\Core\Setting;
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
            $event->maxYPosition = $this->position->y + Setting::playerJumpHeight();
            $this->addEvent($event, $this->eventIdJump);
            return;
        }

        $event = new JumpEvent(function (JumpEvent $jumpEvent): void {
            $targetYPosition = min($jumpEvent->maxYPosition, $this->position->y + Setting::jumpDistancePerTick());
            $candidate = $this->position->clone();
            for ($y = $this->position->y + 1; $y <= $targetYPosition; $y++) {
                $floorCandidate = $this->world->findFloor($candidate->setY($y), $this->playerBoundingRadius);
                if ($floorCandidate) {
                    $targetYPosition = $y - 1;
                    break;
                }
                if ($this->world->isCollisionWithOtherPlayers($this->id, $candidate, $this->playerBoundingRadius, $this->headHeight)) {
                    $targetYPosition = $y - 1;
                    break;
                }
            }

            if ($this->position->y !== $targetYPosition) {
                $this->setActiveFloor(null);
                $this->position->setY($targetYPosition);
            }
        }, Setting::tickCountJump());
        $event->maxYPosition = $this->position->y + Setting::playerJumpHeight();

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
