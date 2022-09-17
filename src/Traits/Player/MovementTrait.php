<?php

namespace cs\Traits\Player;

use cs\Core\Floor;
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

            $this->position = $this->processMovement($this->moveX, $this->moveZ, $this->position);
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
        if ($this->isJumping()) {
            $speed *= static::jumpMovementSlowDown;
        } elseif ($this->isFlying()) {
            $speed *= 0.8;
        }

        return (int)ceil($speed);
    }

    private function processMovement(int $moveX, int $moveZ, Point $current): Point
    {
        $distanceTarget = $this->getMoveSpeed();
        $angle = $this->getSight()->getRotationHorizontal();

        if ($moveX <> 0 && $moveZ <> 0) { // diagonal move
            if ($moveZ === 1) {
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

        $looseFloor = false;
        $orig = $current->clone();
        $target = $orig->clone();
        $candidate = $target->clone();
        for ($i = 1; $i <= $distanceTarget; $i++) {
            [$x, $z] = Util::horizontalMovementXZ($angle, $i);
            $candidate->setX($orig->x + $x)->setZ($orig->z + $z);
            if ($candidate->equals($target)) {
                continue;
            }
            if (!$this->canMoveTo($target, $candidate, $angle)) {
                if ($candidate->x <> $orig->x + $x || $candidate->z <> $orig->z + $z) { // if move is possible in one axis at least
                    $target->setFrom($candidate);
                }
                break;
            }

            $target->setFrom($candidate);
            if ($this->activeFloor && !$this->world->isOnFloor($this->activeFloor, $target, $this->getBoundingRadius())) {
                $this->setActiveFloor(null);
            }
            if (!$looseFloor && !$this->activeFloor && !$this->isJumping()) { // do initial (one-shot) gravity bump
                $newY = $this->calculateGravity($target, 1);
                $candidate->setY($newY);
                $target->setY($newY);
                $looseFloor = true;
            }
        }

        return $target;
    }

    private function canMoveTo(Point $start, Point $candidate, int $angle): bool
    {
        $radius = $this->playerBoundingRadius;
        if ($this->collisionWithPlayer($candidate, $radius)) {
            return false;
        }

        $xWall = null;
        if ($start->x <> $candidate->x) {
            $xGrowing = ($start->x < $candidate->x);
            $baseX = $candidate->clone()->addX($xGrowing ? $radius : -$radius);
            $xWall = $this->world->checkXSideWallCollision($baseX, $candidate->z - $radius, $candidate->z + $radius);
        }
        $zWall = null;
        if ($start->z <> $candidate->z) {
            $zGrowing = ($start->z < $candidate->z);
            $baseZ = $candidate->clone()->addZ($zGrowing ? $radius : -$radius);
            $zWall = $this->world->checkZSideWallCollision($baseZ, $candidate->x - $radius, $candidate->x + $radius);
        }
        if (!$xWall && !$zWall) {
            return true;
        }

        // Try step over low height wall
        $floor = null;
        if ($zWall) {
            $floor = $this->canStepOverWall($zWall, $candidate);
        }
        if ($xWall && !$floor) {
            $floor = $this->canStepOverWall($xWall, $candidate);
        }
        if ($floor && false === $this->collisionWithPlayer($candidate, $radius)) {
            $candidate->setY($floor->getY());
            $this->setActiveFloor($floor);
            return true;
        }

        // Tall walls everywhere
        if ($xWall && $zWall) {
            return false;
        }

        // If moving in 90s angles against wall we stop
        if ($angle % 90 === 0) {
            return false;
        }

        // Try to move 1 unit in one axis at least if possible
        if (isset($zGrowing) && !$xWall) {
            $offset = ($zGrowing ? -1 : 1);
            $oneSideCandidate = $candidate->clone()->addZ($offset);
            if ($oneSideCandidate->equals($start)) {
                $oneSideCandidate->addX($angle > 180 ? -1 : 1);
                $xWall = $this->world->checkXSideWallCollision($oneSideCandidate, $candidate->z + $offset - $radius, $candidate->z + $offset + $radius);
                if ($xWall) {
                    return false;
                }
            }

            $zWall = $this->world->checkZSideWallCollision($oneSideCandidate, $candidate->x + $offset - $radius, $candidate->x + $offset + $radius);
            if (!$zWall && !$this->collisionWithPlayer($oneSideCandidate, $radius)) {
                $candidate->setFrom($oneSideCandidate);
            }
            return false;
        }
        if (isset($xGrowing) && !$zWall) {
            $offset = ($xGrowing ? -1 : 1);
            $oneSideCandidate = $candidate->clone()->addX($offset);
            if ($oneSideCandidate->equals($start)) {
                $oneSideCandidate->addZ(($angle > 270 || $angle < 90) ? 1 : -1);
                $zWall = $this->world->checkZSideWallCollision($oneSideCandidate, $candidate->x + $offset - $radius, $candidate->x + $offset + $radius);
                if ($zWall) {
                    return false;
                }
            }

            $xWall = $this->world->checkXSideWallCollision($oneSideCandidate, $candidate->z + $offset - $radius, $candidate->z + $offset + $radius);
            if (!$xWall && !$this->collisionWithPlayer($oneSideCandidate, $radius)) {
                $candidate->setFrom($oneSideCandidate);
            }
            return false;
        }

        return false;
    }

    private function collisionWithPlayer(Point $candidate, int $radius): bool
    {
        return $this->world->isCollisionWithOtherPlayers($this->getId(), $candidate, $radius, $this->getHeadHeight());
    }

    private function canStepOverWall(Wall $wall, Point $candidate): ?Floor
    {
        if ($this->isFlying()) {
            return null;
        }

        if ($wall->getCeiling() <= $candidate->y + static::obstacleOvercomeHeight) {
            return $this->world->findFloor($candidate->clone()->setY($wall->getCeiling()), $this->getBoundingRadius());
        }

        return null;
    }

}
