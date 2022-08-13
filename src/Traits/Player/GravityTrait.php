<?php

namespace cs\Traits\Player;

use cs\Core\Floor;
use cs\Core\Point;
use cs\Event\PlayerGravityEvent;

trait GravityTrait
{

    private int $fallHeight = 0;

    protected function createGravityEvent(): PlayerGravityEvent
    {
        return new PlayerGravityEvent(fn() => $this->processGravity());
    }

    private function processGravity(): void
    {
        if ($this->activeFloor && !$this->world->isOnFloor($this->activeFloor, $this->position)) {
            $this->setActiveFloor(null);
        }
        if (null === $this->activeFloor && !$this->isJumping()) {
            $floorCandidate = $this->world->findFloor($this->position);
            if ($floorCandidate) {
                $this->setActiveFloor($floorCandidate);
            } else {
                $targetYPosition = $this->position->getY() - static::speedFall;
                for ($y = $this->position->getY(); $y >= $targetYPosition; $y--) {
                    $floorCandidate = $this->world->findFloor(new Point($this->position->getX(), $y, $this->position->getZ()));
                    if ($floorCandidate) {
                        $this->setActiveFloor($floorCandidate);
                        $targetYPosition = $floorCandidate->getY();
                        break;
                    }
                }
                $this->position->setY($targetYPosition);
            }
        }
    }

    private function setActiveFloor(?Floor $floor): void
    {
        $this->activeFloor = $floor;
        if ($floor) {
            $this->checkFallDamage($floor->getY());
            $this->removeEvent($this->eventIdJump);
            $this->fallHeight = 0;
        } else {
            $this->fallHeight = $this->position->y;
        }
    }

    private function checkFallDamage(int $floorHeight): void
    {
        $fallHeight = $this->fallHeight - $floorHeight;
        if ($fallHeight < static::fallDamageThreshold) {
            return;
        }

        if ($fallHeight < static::fallDamageThreshold + 15) {
            $this->lowerHealth(10);
        } elseif ($fallHeight < static::fallDamageThreshold + 30) {
            $this->lowerHealth(20);
        } elseif ($fallHeight < static::fallDamageThreshold + 60) {
            $this->lowerHealth(40);
        } elseif ($fallHeight < static::fallDamageThreshold + 90) {
            $this->lowerHealth(60);
        } elseif ($fallHeight < static::fallDamageThreshold + 120) {
            $this->lowerHealth(90);
        } else {
            $this->lowerHealth(999);
        }

        if (!$this->isAlive()) {
            $this->world->playerDiedToFallDamage($this);
        }
    }

    public function isFlying(): bool
    {
        return ($this->activeFloor === null);
    }

}
