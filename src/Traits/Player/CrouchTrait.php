<?php

namespace cs\Traits\Player;

use cs\Core\Action;
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
                if ($this->headHeight < Action::playerHeadHeightCrouch()) {
                    $this->headHeight = Action::playerHeadHeightCrouch();
                }
            } else {
                $targetHeadHeight = $this->headHeight + $event->moveOffset;
                $candidate = $this->position->clone();
                for ($h = $this->headHeight + 1; $h <= min($targetHeadHeight, Action::playerHeadHeightStand()); $h++) {
                    $floorCandidate = $this->world->findFloor($candidate->addY($h), $this->getBoundingRadius());
                    if ($floorCandidate) {
                        break;
                    }
                    $this->headHeight = $h;
                }
                if ($this->headHeight > Action::playerHeadHeightStand()) {
                    $this->headHeight = Action::playerHeadHeightStand();
                }
            }
        });

        $this->addEvent($event, $this->eventIdCrouch);
        $this->eventsCache[$this->eventIdCrouch] = $event;
    }

    public function stand(): void
    {
        if ($this->getHeadHeight() === Action::playerHeadHeightStand()) {
            return;
        }

        $this->createCrouchEvent(false);
    }

    public function crouch(): void
    {
        if ($this->getHeadHeight() === Action::playerHeadHeightCrouch()) {
            return;
        }
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
