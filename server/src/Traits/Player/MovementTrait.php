<?php

namespace cs\Traits\Player;

use cs\Core\Floor;
use cs\Core\GameException;
use cs\Core\Point;
use cs\Core\Setting;
use cs\Core\Util;
use cs\Core\Wall;
use cs\Enum\ItemType;
use cs\Enum\SoundType;
use cs\Event\PlayerMovementEvent;
use cs\Event\SoundEvent;

trait MovementTrait
{

    private int $moveX = 0;
    private int $moveZ = 0;
    private ?int $lastAngle = null;
    private bool $isWalking = false;

    public function speedRun(): void
    {
        $this->isWalking = false;
    }

    public function speedWalk(): void
    {
        $this->isWalking = true;
    }

    public function isMoving(): bool
    {
        return ($this->moveX <> 0 || $this->moveZ <> 0);
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

    public function getReferenceToPosition(): Point
    {
        return $this->position;
    }

    public function setPosition(Point $newPosition): void
    {
        $this->position = $newPosition->clone();
        $this->stop();
        $this->setActiveFloor($this->world->findFloor($this->position, $this->getBoundingRadius()));
    }

    public function stop(): void
    {
        $this->moveX = 0;
        $this->moveZ = 0;
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
            if (!$this->isMoving()) {
                return;
            }

            $this->position = $this->processMovement($this->moveX, $this->moveZ, $this->position);
            $this->world->tryPickDropItems($this);
            $this->stop();
        });
    }

    private function getMoveSpeed(): int
    {
        if ($this->isCrouching()) {
            $speed = Setting::moveDistanceCrouchPerTick();
        } elseif ($this->isWalking()) {
            $speed = Setting::moveDistanceWalkPerTick();
        } elseif ($this->isRunning()) {
            $speed = Setting::moveDistancePerTick();
        } else {
            throw new GameException("Wat doing?");
        }

        $equippedItem = $this->getEquippedItem();
        if ($equippedItem->getType() === ItemType::TYPE_WEAPON_PRIMARY) {
            $speed *= Setting::getWeaponPrimarySpeedMultiplier($equippedItem->getId());
        } elseif ($equippedItem->getType() === ItemType::TYPE_WEAPON_SECONDARY) {
            $speed *= Setting::getWeaponSecondarySpeedMultiplier($equippedItem->getId());
        }
        if ($this->isJumping()) {
            $speed *= Setting::jumpMovementSpeedMultiplier();
        } elseif ($this->isFlying()) {
            $speed *= Setting::flyingMovementSpeedMultiplier();
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
        $angle = Util::normalizeAngle($angle);
        $angleInt = Util::nearbyInt($angle);
        if ($this->lastAngle === null) {
            $this->lastAngle = $angleInt;
        }
        if ($this->isFlying()) {
            $delta = Util::smallestDeltaAngle($this->lastAngle, $angleInt);
            if (abs($delta) > 160) {
                $distanceTarget = (int)ceil($distanceTarget * .1);
                $angle = $this->lastAngle + ($delta / 4);
                $angleInt = Util::nearbyInt($angle);
            } elseif (abs($delta) > 60) {
                $angle += ($delta / 4);
                $angleInt = Util::nearbyInt($angle);
                $this->lastAngle = $angleInt;
            }
        } else {
            $this->lastAngle = $angleInt;
        }

        $looseFloor = false;
        $orig = $current->clone();
        $target = $orig->clone();
        $candidate = $target->clone();
        for ($i = 1; $i <= $distanceTarget; $i++) {
            [$x, $z] = Util::movementXZ($angle, $i);
            $candidate->setX($orig->x + $x)->setZ($orig->z + $z);
            if ($candidate->equals($target)) {
                continue;
            }
            if (!$this->canMoveTo($target, $candidate, $angleInt)) {
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

        if ($this->isRunning() && !$this->isCrouching() && !$this->isFlying() && !$orig->equals($target)) {
            $sound = new SoundEvent($target, SoundType::PLAYER_STEP);
            $this->world->makeSound($sound->setPlayer($this));
        }

        return $target;
    }

    private function canMoveTo(Point $start, Point $candidate, int $angle): bool
    {
        $radius = $this->getBoundingRadius();
        if ($this->collisionWithPlayer($candidate, $radius)) {
            return false;
        }

        $xWall = null;
        if ($start->x <> $candidate->x) {
            $xGrowing = ($start->x < $candidate->x);
            $baseX = $candidate->clone()->addX($xGrowing ? $radius : -$radius);
            $xWall = $this->world->checkXSideWallCollision($baseX, $this->getHeadHeight(), $radius);
        }
        $zWall = null;
        if ($start->z <> $candidate->z) {
            $zGrowing = ($start->z < $candidate->z);
            $baseZ = $candidate->clone()->addZ($zGrowing ? $radius : -$radius);
            $zWall = $this->world->checkZSideWallCollision($baseZ, $this->getHeadHeight(), $radius);
        }
        if (!$xWall && !$zWall) {
            return true;
        }

        // Try step over ONE low height wall
        $floor = null;
        if ($zWall && !$xWall) {
            $floor = $this->canStepOverWall($zWall, $candidate);
        } elseif ($xWall && !$zWall) {
            $floor = $this->canStepOverWall($xWall, $candidate);
        }
        if ($floor) {
            $candidateY = $candidate->clone()->setY($floor->getY());
            if (!$this->collisionWithPlayer($candidateY, $radius)) {
                $candidate->setY($floor->getY()); // side effect
                $this->setActiveFloor($floor);
                return true;
            }
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
        if ($xWall === null) { // Try to move in X axis
            $oneSideCandidate = $candidate->clone()->setZ($start->z); // reset to previous Z
            $oneSideCandidate->addX($angle > 180 ? -1 : 1); // try 1 unit in X
            $oneSideCandidateX = $oneSideCandidate->clone()->addX($angle > 180 ? -$radius : $radius);
            $xWall = $this->world->checkXSideWallCollision($oneSideCandidateX, $this->getHeadHeight(), $radius);
            if (!$xWall && !$this->collisionWithPlayer($oneSideCandidate, $radius)) {
                $candidate->setFrom($oneSideCandidate); // side effect for caller
            }
            return false;
        }
        if ($zWall === null) { // Try to move in Z axis
            $oneSideCandidate = $candidate->clone()->setX($start->x); // reset to previous X
            $oneSideCandidate->addZ(($angle > 270 || $angle < 90) ? 1 : -1); // try 1 unit in Z
            $oneSideCandidateZ = $oneSideCandidate->clone()->addZ(($angle > 270 || $angle < 90) ? $radius : -$radius);
            $zWall = $this->world->checkZSideWallCollision($oneSideCandidateZ, $this->getHeadHeight(), $radius);
            if (!$zWall && !$this->collisionWithPlayer($oneSideCandidate, $radius)) {
                $candidate->setFrom($oneSideCandidate); // side effect for caller
            }
            return false;
        }
    }

    private function collisionWithPlayer(Point $candidate, int $radius): bool
    {
        return (null !== $this->world->isCollisionWithOtherPlayers($this->getId(), $candidate, $radius, $this->getHeadHeight()));
    }

    private function canStepOverWall(Wall $wall, Point $candidate): ?Floor
    {
        if ($this->isFlying()) {
            return null;
        }

        if ($wall->getCeiling() <= $candidate->y + Setting::playerObstacleOvercomeHeight()) {
            return $this->world->findFloor($candidate->clone()->setY($wall->getCeiling()), $this->getBoundingRadius());
        }

        return null;
    }

}
