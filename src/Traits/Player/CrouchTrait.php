<?php

namespace cs\Traits\Player;

use cs\Core\Point;
use cs\Event\CrouchEvent;

trait CrouchTrait
{
    protected function createCrouchEvent(bool $directionDown): void
    {
        if (isset($this->eventsCache[$this->eventIdCrouch])) {
            /** @var CrouchEvent $event */
            $event = $this->eventsCache[$this->eventIdCrouch];
            $event->directionDown = $directionDown;
            $event->reset();
            $this->addEvent($event, $this->eventIdCrouch);
            return;
        }

        $event = new CrouchEvent($directionDown, function (CrouchEvent $event): void {
            if ($event->directionDown) {
                $this->headHeight -= $event->moveOffset;
                if ($this->headHeight < self::headHeightCrouch) {
                    $this->headHeight = self::headHeightCrouch;
                }
            } else {
                $targetHeadHeight = $this->headHeight + $event->moveOffset;
                $candidate = new Point($this->position->x, $this->position->y, $this->position->z);
                for ($h = $this->headHeight + 1; $h <= min($targetHeadHeight, self::headHeightStand); $h++) {
                    $floorCandidate = $this->world->findFloor($candidate->addY($h), $this->getBoundingRadius());
                    if ($floorCandidate) {
                        break;
                    }
                    $this->headHeight = $h;
                }
                if ($this->headHeight > self::headHeightStand) {
                    $this->headHeight = self::headHeightStand;
                }
            }
        });

        $this->addEvent($event, $this->eventIdCrouch);
        $this->eventsCache[$this->eventIdCrouch] = $event;
    }

    public function stand(): void
    {
        if ($this->getHeadHeight() === self::headHeightStand) {
            return;
        }

        $this->createCrouchEvent(false);
    }

    public function crouch(): void
    {
        if (!$this->canCrouch()) {
            return;
        }

        $this->createCrouchEvent(true);
    }

    public function isCrouching(): bool
    {
        return (isset($this->events[$this->eventIdCrouch]));
    }

    public function canCrouch(): bool
    {
        return !($this->isCrouching());
    }

}
