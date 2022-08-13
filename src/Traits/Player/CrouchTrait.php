<?php

namespace cs\Traits\Player;

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
            $event->onComplete[] = function () use ($directionDown): void {
                $this->headHeight = $directionDown ? self::headHeightCrouch : self::headHeightStand; // TODO crouch ceil hit boxes
            };
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
                // TODO crouch ceil hit boxes
                $this->headHeight += $event->moveOffset;
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
