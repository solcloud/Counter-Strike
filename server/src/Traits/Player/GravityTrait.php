<?php

namespace cs\Traits\Player;

use cs\Core\Floor;
use cs\Core\Point;
use cs\Core\Setting;
use cs\Core\Util;
use cs\Enum\SoundType;
use cs\Event\CallbackEvent;
use cs\Event\SoundEvent;

trait GravityTrait
{

    private int $fallHeight = 0;

    protected function createGravityEvent(): CallbackEvent
    {
        return new CallbackEvent(fn() => $this->processGravity($this->position));
    }

    private function processGravity(Point $point): void
    {
        if ($this->isJumping() || $this->activeFloor || $this->world->isPaused()) {
            return;
        }

        $point->setY($this->calculateGravity($point, Setting::fallAmountPerTick()));
    }

    private function calculateGravity(Point $start, int $amount): int
    {
        $targetYPosition = $start->y - $amount;
        $candidate = $start->clone();
        for ($y = $start->y; $y >= $targetYPosition; $y--) {
            $candidate->y = $y;
            $floorCandidate = $this->world->findFloorSquare($candidate, $this->playerBoundingRadius);
            if (!$floorCandidate) {
                $floorCandidate = $this->world->findPlayersHeadFloor($candidate, $this->playerBoundingRadius);
            }
            if ($floorCandidate) {
                $this->setActiveFloor($floorCandidate);
                $targetYPosition = $y;
                break;
            }
        }

        return $targetYPosition;
    }

    private function setActiveFloor(?Floor $floor): void
    {
        $this->activeFloor = $floor;
        if ($floor) {
            $this->checkFallDamage($floor);
            $this->removeEvent($this->eventIdJump);
            $this->fallHeight = 0;
        } else {
            $this->fallHeight = $this->position->y;
        }
    }

    private function checkFallDamage(Floor $floor): void
    {
        $floorHeight = $floor->getY();
        $fallHeight = $this->fallHeight - $floorHeight;
        if ($fallHeight > 3 * Setting::playerObstacleOvercomeHeight()) {
            $soundEvent = new SoundEvent($this->getPositionClone()->setY($floorHeight), SoundType::PLAYER_GROUND_TOUCH);
            $this->world->makeSound($soundEvent->setPlayer($this));
        }

        $threshold = Setting::playerFallDamageThreshold();
        if ($fallHeight < $threshold) {
            return;
        }

        $this->lowerHealth(Util::mapRange($threshold, 2 * $threshold, 1, 180, $fallHeight));
        if (!$this->isAlive()) {
            $this->world->playerDiedToFallDamage($this);
        }
    }

    public function isFlying(): bool
    {
        return ($this->activeFloor === null || $this->isJumping());
    }

}
