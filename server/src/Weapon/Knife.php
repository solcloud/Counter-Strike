<?php

namespace cs\Weapon;

use cs\Core\Bullet;
use cs\Core\Util;
use cs\Enum\ArmorType;
use cs\Enum\HitBoxType;
use cs\Enum\InventorySlot;
use cs\Enum\ItemType;
use cs\Event\AttackResult;
use cs\Interface\Attackable;
use cs\Interface\AttackEnable;

final class Knife extends BaseWeapon implements AttackEnable
{
    public const int killAward = 1500;
    public const int stabMaxDistance = 140;
    public const int equipReadyTimeMs = 500;
    private bool $primaryAttack = true;
    private int $lastAttackTick = 0;

    public function getType(): ItemType
    {
        return ItemType::TYPE_KNIFE;
    }

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_KNIFE;
    }

    #[\Override]
    public function isUserDroppable(): bool
    {
        return false;
    }

    #[\Override]
    public function canAttack(int $tickId): bool
    {
        if (!$this->equipped) {
            return false;
        }
        return ($this->lastAttackTick === 0 || $this->lastAttackTick + Util::millisecondsToFrames($this->primaryAttack ? 400 : 1000) <= $tickId);
    }

    public function attack(Attackable $event): ?AttackResult
    {
        $this->primaryAttack = true;
        if (!$this->canAttack($event->getTickId())) {
            return null;
        }

        $this->lastAttackTick = $event->getTickId();
        return $event->fire();
    }

    public function attackSecondary(Attackable $event): ?AttackResult
    {
        $this->primaryAttack = false;
        if (!$this->canAttack($event->getTickId())) {
            return null;
        }

        $this->lastAttackTick = $event->getTickId();
        return $event->fire();
    }

    public function createBullet(): Bullet
    {
        return new Bullet($this, self::stabMaxDistance);
    }

    public function getDamageValue(HitBoxType $hitBox, ArmorType $armor): int
    {
        if ($hitBox === HitBoxType::BACK) {
            if ($this->primaryAttack) {
                return $armor->hasArmorBody() ? 76 : 90;
            }
            return $armor->hasArmorBody() ? 153 : 180;
        }

        if ($hitBox === HitBoxType::HEAD) {
            if ($this->primaryAttack) {
                return $armor->hasArmorHead() ? 34 : 40;
            }
            return $armor->hasArmorHead() ? 55 : 65;
        }

        if ($this->primaryAttack) {
            return $armor->hasArmorBody() ? 34 : 40;
        }
        return $armor->hasArmorBody() ? 55 : 65;
    }

    public function getKillAward(): int
    {
        return self::killAward;
    }

}
