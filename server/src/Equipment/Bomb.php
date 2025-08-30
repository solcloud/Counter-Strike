<?php

namespace cs\Equipment;

use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Util;
use cs\Enum\InventorySlot;
use cs\Enum\ItemType;

class Bomb extends BaseEquipment
{

    public const int equipReadyTimeMs = 80;
    private Point $position;
    private int $plantTickStart;
    private int $plantTickCountMax;
    private int $defuseTickStart;
    private int $defuseTickCountMax;
    private int $tickToDefuseCount;
    private int $lastBombActionTick = -1;
    private int $lastBombPlayerId = -1;

    public function __construct(int $plantTimeMs, int $defuseTimeMs, private int $maxBlastDistance = 1000, private int $bombActionTickBuffer = 1)
    {
        parent::__construct();
        $this->position = new Point();
        $this->plantTickCountMax = Util::millisecondsToFrames($plantTimeMs);
        $this->plantTickStart = -$this->plantTickCountMax;
        $this->defuseTickCountMax = Util::millisecondsToFrames($defuseTimeMs);
        $this->defuseTickStart = -$this->defuseTickCountMax;
    }

    public function setMaxBlastDistance(int $maxBlastDistance): void
    {
        $this->maxBlastDistance = $maxBlastDistance;
    }

    public function getType(): ItemType
    {
        return ItemType::TYPE_BOMB;
    }

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_BOMB;
    }

    #[\Override]
    public function reset(): void
    {
        parent::reset();
        $this->plantTickStart = 0;
        $this->defuseTickStart = 0;
    }

    #[\Override]
    public function unEquip(): void
    {
        parent::unEquip();
        $this->reset();
    }

    public function tryPlant(Player $player, int $tickId): ?bool
    {
        $planted = false;
        $playerId = $player->getId();
        if ($playerId !== $this->lastBombPlayerId || $this->lastBombActionTick + $this->bombActionTickBuffer < $tickId) {
            $player->stop();
            $this->plantTickStart = $tickId;
            $planted = null;
        }
        $this->lastBombActionTick = $tickId;
        $this->lastBombPlayerId = $playerId;

        if ($this->isPlanted($tickId)) {
            $this->position->setFrom($player->getReferenceToPosition());
            $this->lastBombActionTick = -1;
            $this->lastBombPlayerId = -1;
            $planted = true;
        }
        return $planted;
    }

    public function tryDefuse(Player $player, int $tickId): ?bool
    {
        $defused = false;
        $playerId = $player->getId();
        if ($playerId !== $this->lastBombPlayerId || $this->lastBombActionTick + $this->bombActionTickBuffer < $tickId) {
            $player->stop();
            $this->defuseTickStart = $tickId;
            $this->tickToDefuseCount = $player->hasDefuseKit() ? (int)ceil($this->defuseTickCountMax / 2) : $this->defuseTickCountMax;;
            $defused = null;
        }
        $this->lastBombActionTick = $tickId;
        $this->lastBombPlayerId = $playerId;

        if ($this->isDefused($tickId)) {
            $this->lastBombActionTick = -1;
            $this->lastBombPlayerId = -1;
            $defused = true;
        }
        return $defused;
    }

    private function isPlanted(int $tickId): bool
    {
        return ($tickId - $this->plantTickStart >= $this->plantTickCountMax);
    }

    private function isDefused(int $tickId): bool
    {
        return ($tickId - $this->defuseTickStart >= $this->tickToDefuseCount);
    }

    public function isPlantingOrDefusing(int $playerId, int $tickId): bool
    {
        return ($this->lastBombPlayerId === $playerId && $this->bombActionTickBuffer >= $tickId - $this->lastBombActionTick);
    }

    public function getPosition(): Point
    {
        return $this->position;
    }

    /** @codeCoverageIgnore **/
    public function explodeDamageToPlayer(Player $player): void
    {
        $distanceSquared = Util::distanceSquared($player->getPositionClone(), $this->position);
        $maxDistance = $this->maxBlastDistance * $this->maxBlastDistance;
        if ($distanceSquared > $maxDistance) {
            return;
        }

        if ($distanceSquared > ($maxDistance * .9)) {
            $player->lowerHealth(4);
            $player->lowerArmor(4);
        } elseif ($distanceSquared > ($maxDistance * .8)) {
            $player->lowerHealth(7);
            $player->lowerArmor(7);
        } elseif ($distanceSquared > ($maxDistance * .7)) {
            $player->lowerHealth(12);
            $player->lowerArmor(12);
        } elseif ($distanceSquared > ($maxDistance * .6)) {
            $player->lowerHealth(26);
            $player->lowerArmor(26);
        } elseif ($distanceSquared > ($maxDistance * .5)) {
            $player->lowerHealth(49);
            $player->lowerArmor(49);
        } elseif ($distanceSquared > ($maxDistance * .4)) {
            $player->lowerHealth(61);
            $player->lowerArmor(61);
        } elseif ($distanceSquared > ($maxDistance * .3)) {
            $player->lowerHealth(74);
            $player->lowerArmor(74);
        } elseif ($distanceSquared > ($maxDistance * .2)) {
            $player->lowerHealth(84);
            $player->lowerArmor(84);
        } elseif ($distanceSquared > ($maxDistance * .1)) {
            $player->lowerHealth(92);
            $player->lowerArmor(92);
        } else {
            $player->lowerHealth(500);
            $player->lowerArmor(500);
        }
    }

}
