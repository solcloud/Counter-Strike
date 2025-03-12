<?php

namespace cs\Traits\Player;

use cs\Core\Setting;
use cs\Event\CrouchEvent;

trait CrouchTrait
{
    private function createCrouchEvent(bool $directionDown): void
    {
        if (isset($this->eventsCache[$this->eventIdCrouch])) {
            /** @var CrouchEvent $event */
            $event = $this->eventsCache[$this->eventIdCrouch];
            $event->reset();
            $event->directionDown = $directionDown;
            $this->addEvent($event, $this->eventIdCrouch);
            return;
        }

        $event = new CrouchEvent($directionDown, function (CrouchEvent $event): void {
            $headHeightCrouch = Setting::playerHeadHeightCrouch();
            if ($event->directionDown) {
                $this->headHeight -= $event->moveOffset;
                if ($this->headHeight < $headHeightCrouch) {
                    $this->headHeight = $headHeightCrouch;
                }
                return;
            }

            $targetHeadHeight = min(Setting::playerHeadHeightStand(), $this->headHeight + $event->moveOffset);
            $candidate = $this->position->clone();
            for ($h = $this->headHeight + 1; $h <= $targetHeadHeight; $h++) {
                $candidate->setY($this->position->y + $h);
                if ($this->world->findFloorSquare($candidate, $this->getBoundingRadius())) {
                    $event->restartTimer();
                    break;
                }
                if ($this->world->isCollisionWithOtherPlayers($this->getId(), $candidate, $this->getBoundingRadius(), 2)) {
                    $event->restartTimer();
                    $this->headHeight = $headHeightCrouch;
                    break;
                }
                $this->headHeight = $h;
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

    public function canCrouch(): bool
    {
        return (!isset($this->events[$this->eventIdCrouch]));
    }

}
