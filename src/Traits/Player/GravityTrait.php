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
        return new PlayerGravityEvent(fn() => $this->processGravity($this->position));
    }

    private function processGravity(Point $point): void
    {
        if ($this->isJumping()) {
            return;
        }
        if ($this->activeFloor && !$this->world->isOnFloor($this->activeFloor, $point, $this->getBoundingRadius())) {
            $this->setActiveFloor(null);
        }
        if (null === $this->activeFloor) {
            $floorCandidate = $this->world->findFloor($point, $this->getFloorBoundingRadius());
            if ($floorCandidate) {
                $this->setActiveFloor($floorCandidate);
            } else {
                $point->setY($this->calculateGravity($point, static::speedFall));
            }
        }
    }

    private function calculateGravity(Point $start, int $amount): int
    {
        $targetYPosition = $start->y - $amount;
        $candidate = new Point($start->x, $start->y, $start->z);
        for ($y = $start->y; $y >= $targetYPosition; $y--) {
            $candidate->setY($y);
            $floorCandidate = $this->world->findFloor($candidate, $this->getFloorBoundingRadius());
            if ($floorCandidate) {
                $this->setActiveFloor($floorCandidate);
                $targetYPosition = $floorCandidate->getY();
                break;
            }
            if ($this->collisionWithPlayer($candidate, $this->playerBoundingRadius)) {
                break;
            }
        }
        return $targetYPosition;
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
        return ($this->activeFloor === null || $this->isJumping());
    }

}
