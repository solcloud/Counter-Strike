<?php

namespace cs\Traits\Player;

use cs\Core\Setting;
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
                if ($this->headHeight < Setting::playerHeadHeightCrouch()) {
                    $this->headHeight = Setting::playerHeadHeightCrouch();
                }
            } else {
                $targetHeadHeight = $this->headHeight + $event->moveOffset;
                $candidate = $this->position->clone();
                for ($h = $this->headHeight + 1; $h <= min($targetHeadHeight, Setting::playerHeadHeightStand()); $h++) {
                    $floorCandidate = $this->world->findFloor($candidate->addY($h), $this->getBoundingRadius());
                    if ($floorCandidate) {
                        break;
                    }
                    $this->headHeight = $h;
                }
                if ($this->headHeight > Setting::playerHeadHeightStand()) {
                    $this->headHeight = Setting::playerHeadHeightStand();
                }
            }
        });

        $this->addEvent($event, $this->eventIdCrouch);
        $this->eventsCache[$this->eventIdCrouch] = $event;
    }

    public function stand(): void
    {
        if ($this->getHeadHeight() === Setting::playerHeadHeightStand()) {
            return;
        }

        $this->createCrouchEvent(false);
    }

    public function crouch(): void
    {
        if ($this->getHeadHeight() === Setting::playerHeadHeightCrouch()) {
            return;
        }
        if (!$this->canCrouch()) {
            return;
        }

        $this->createCrouchEvent(true);
    }

    public function isCrouching(): bool
    {
        return ($this->getHeadHeight() !== Setting::playerHeadHeightStand());
    }

    private function canCrouch(): bool
    {
        return (!isset($this->events[$this->eventIdCrouch]));
    }

}
