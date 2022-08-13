<?php

namespace cs\Traits\Player;

use cs\Core\GameException;
use cs\Core\Point;
use cs\Core\Util;
use cs\Core\Wall;
use cs\Event\PlayerMovementEvent;

trait MovementTrait
{

    private int $moveX = 0;
    private int $moveZ = 0;
    private bool $isWalking = false;

    public function speedRun(): void
    {
        $this->isWalking = false;
    }

    public function speedWalk(): void
    {
        $this->isWalking = true;
    }

    public function isWalking(): bool
    {
        return $this->isWalking;
    }

    public function isRunning(): bool
    {
        return !$this->isWalking;
    }

    public function getPositionImmutable(): Point
    {
        return $this->position->clone();
    }

    public function setPosition(Point $newPosition): void
    {
        $this->position = $newPosition->clone();
        $this->moveX = $this->moveZ = 0;
        $this->setActiveFloor($this->world->findFloor($this->position));
    }

    public function moveForward(): void
    {
        $this->moveZ = 1;
    }

    public function moveRight(): void
    {
        $this->moveX = 1;
    }

    public function moveLeft(): void
    {
        $this->moveX = -1;
    }

    public function moveBackward(): void
    {
        $this->moveZ = -1;
    }

    protected function createMovementEvent(): PlayerMovementEvent
    {
        return new PlayerMovementEvent(function (): void {
            if ($this->moveX === 0 && $this->moveZ === 0) {
                return;
            }

            $this->position = $this->processMovement();
            $this->moveX = $this->moveZ = 0;
        });
    }

    private function getMoveSpeed(): int
    {
        if ($this->isCrouching()) {
            $speed = static::speedMoveCrouch;
        } elseif ($this->isWalking()) {
            $speed = static::speedMoveWalk;
        } elseif ($this->isRunning()) {
            $speed = static::speedMove;
        } else {
            throw new GameException("Wat doing?");
        }

        $speed *= $this->getEquippedItem()::movementSlowDownFactor;
        if ($this->isFlying()) {
            $speed *= 0.8;
        }

        return (int)ceil($speed);
    }

    private function processMovement(): Point
    {
        $moveX = $this->moveX;
        $moveZ = $this->moveZ;
        $distanceTarget = $this->getMoveSpeed();
        $angle = $this->getSight()->getRotationHorizontal();

        if ($moveX <> 0 && $moveZ <> 0) { // diagonal move
            if ($moveZ > 0) {
                $angle += $moveX * 45;
            } else {
                $angle += $moveX * (45 * 3);
            }
        } else { // single direction move
            if ($moveZ === -1) {
                $angle += 180;
            } elseif ($moveX === 1) {
                $angle += 90;
            } elseif ($moveX === -1) {
                $angle += -90;
            }
        }

        $target = $this->position->clone();
        $candidate = $target->clone();
        for ($i = 1; $i <= $distanceTarget; $i++) {
            [$x, $z] = Util::horizontalMovementXZ($angle, $i);
            $candidate->setX($this->position->x + $x)->setZ($this->position->z + $z);

            // TODO try move in one direction at least, and also collision with other players
            $wall = $this->checkWall($target, $candidate, $this->playerBoundingRadius);
            if ($wall && !$this->canStepOverWallSideEffect($wall, $candidate)) {
                break;
            }
            $target->setX($candidate->x)->setZ($candidate->z);
        }

        $target->setY($candidate->y); // side effect
        return $target;
    }

    private function checkWall(Point $oldCenter, Point $newCenter, int $radius): ?Wall
    {
        $baseX = $newCenter->clone()->addX(($oldCenter->x > $newCenter->x ? -$radius : $radius));
        $baseZ = $newCenter->clone()->addZ(($oldCenter->z > $newCenter->z ? -$radius : $radius));

        $xWall = $this->world->checkXSideWallCollision($baseX, $newCenter->z - $radius, $newCenter->z + $radius);
        if ($xWall) {
            return $xWall;
        }

        $zWall = $this->world->checkZSideWallCollision($baseZ, $newCenter->x - $radius, $newCenter->x + $radius);
        if ($zWall) {
            return $zWall;
        }

        return null;
    }

    private function canStepOverWallSideEffect(Wall $wall, Point $candidate): bool
    {
        if ($this->isFlying()) {
            return false;
        }

        if ($wall->getCeiling() <= $candidate->y + static::obstacleOvercomeHeight) { // wall we can step on
            $candidate->setY($wall->getCeiling()); // TODO: side effect
            $floorCandidate = $this->world->findFloor($candidate);
            if ($floorCandidate) {
                $this->setActiveFloor($floorCandidate);
                return true;
            }
        }

        return false;
    }

}
