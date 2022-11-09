<?php

namespace cs\Core;

use cs\Enum\ItemType;

class DropItem
{
    private Point $position;
    private int $radius;
    private int $height;

    public function __construct(private Item $item)
    {
        $this->radius = ($item->getType() === ItemType::TYPE_WEAPON_PRIMARY ? 30 : ($item->getType() === ItemType::TYPE_WEAPON_SECONDARY ? 20 : 10));
        $this->height = 10;
    }

    public function calculateDropPosition(Player $player, World $world, Item $item): ?Point
    {
        $playerId = $player->getId();
        $playerRadius = Setting::playerBoundingRadius();
        $start = $player->getPositionImmutable()->addY($player->getSightHeight());
        $horizontalAngle = $player->getSight()->getRotationHorizontal();
        $verticalAngle = $player->getSight()->getRotationVertical();
        $distance = (($player->isMoving() || $player->isJumping()) ? 10 * $playerRadius : 4 * $playerRadius) + $this->radius;

        $candidate = $start->clone();
        $lastCandidate = $candidate->clone();
        for ($t = $playerRadius; $t <= $distance; $t++) {
            [$x, $y, $z] = Util::movementXYZ($horizontalAngle, $verticalAngle, $t);
            $candidate->set($start->x + $x, $start->y + $y, $start->z + $z);
            if ($candidate->equals($lastCandidate)) {
                continue;
            }

            $collisionPlayer = $world->isCollisionWithOtherPlayers($playerId, $candidate, $this->radius, $this->height);
            if ($collisionPlayer && $collisionPlayer->getInventory()->pickup($item)) {
                return null;
            }
            if ($world->isWallOrFloorCollision($lastCandidate, $candidate, $this->radius)) {
                break;
            }

            $lastCandidate->setFrom($candidate);
        }

        while (true) {
            $floorCandidate = $world->findFloor($lastCandidate, $this->radius);
            if ($floorCandidate) {
                $this->position = $lastCandidate;
                return $lastCandidate;
            }
            $collisionPlayer = $world->isCollisionWithOtherPlayers(-1, $lastCandidate, $this->radius, $this->height);
            if ($collisionPlayer && $collisionPlayer->getInventory()->pickup($item)) {
                return null;
            }
            $lastCandidate->addY(-1);
        }
    }

    public function getPosition(): Point
    {
        return $this->position;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function getBoundingRadius(): int
    {
        return $this->radius;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

}
