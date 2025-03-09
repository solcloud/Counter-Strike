<?php

namespace cs\Traits\Player;

use cs\Core\Setting;
use cs\Event\Event;
use cs\Event\JumpEvent;

trait JumpTrait
{
    public function jump(): void
    {
        if (!$this->canJump()) {
            return;
        }

        if (!isset($this->eventsCache[$this->eventIdJump])) {
            $this->eventsCache[$this->eventIdJump] = new JumpEvent(function (Event $jumpEvent): void {
                assert($jumpEvent instanceof JumpEvent);
                $targetYPosition = min($jumpEvent->maxYPosition, $this->position->y + Setting::jumpDistancePerTick());
                $candidate = $this->position->clone()->addY($this->headHeight);
                for ($y = $this->position->y; $y < $targetYPosition; $y++) {
                    $candidate->addY(1);
                    $floorCandidate = $this->world->findFloorSquare($candidate, $this->playerBoundingRadius);
                    if ($floorCandidate) {
                        $this->removeEvent($this->eventIdJump);
                        break;
                    }
                    if ($this->world->isCollisionWithOtherPlayers($this->id, $candidate, $this->playerBoundingRadius, 2)) {
                        $this->removeEvent($this->eventIdJump);
                        break;
                    }
                }

                if ($this->position->y !== $y) {
                    $this->setActiveFloor(null);
                    $this->position->setY($y);
                }
            }, Setting::tickCountJump());
        }

        /** @var JumpEvent $event */
        $event = $this->eventsCache[$this->eventIdJump];
        $event->reset();
        $event->maxYPosition = $this->position->y + Setting::playerJumpHeight();
        $this->addEvent($event, $this->eventIdJump);
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
