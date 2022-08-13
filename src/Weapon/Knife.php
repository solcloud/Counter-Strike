<?php

namespace cs\Weapon;

use cs\Core\Bullet;
use cs\Enum\ArmorType;
use cs\Enum\HitBoxType;
use cs\Enum\InventorySlot;
use cs\Enum\ItemType;
use cs\Event\AttackEvent;
use cs\Event\AttackResult;
use cs\Interface\AttackEnable;

final class Knife extends BaseWeapon implements AttackEnable
{
    public const stabMaxDistance = 30;
    public const equipReadyTimeMs = 500;
    private bool $primaryAttack = true;

    public function getType(): ItemType
    {
        return ItemType::TYPE_KNIFE;
    }

    public function getSlot(): InventorySlot
    {
        return InventorySlot::SLOT_KNIFE;
    }

    public function attack(AttackEvent $event): ?AttackResult
    {
        if (!$this->equipped) {
            return null;
        }

        $this->primaryAttack = true;
        $event->setItem($this);
        return $event->process();
    }

    public function attackSecondary(AttackEvent $event): ?AttackResult
    {
        if (!$this->equipped) {
            return null;
        }

        $this->primaryAttack = false;
        $event->setItem($this);
        return $event->process();
    }

    public function createBullet(): Bullet
    {
        return new Bullet($this, self::stabMaxDistance);
    }

    public function getDamageValue(HitBoxType $hitBox, ArmorType $armor): int
    {
        if ($hitBox === HitBoxType::BACK) {
            if ($this->primaryAttack) {
                return $armor->hasArmor() ? 76 : 90;
            }
            return $armor->hasArmor() ? 153 : 180;
        }

        if ($hitBox === HitBoxType::HEAD) {
            if ($this->primaryAttack) {
                return $armor->hasArmorHead() ? 34 : 40;
            }
            return $armor->hasArmorHead() ? 55 : 65;
        }

        if ($this->primaryAttack) {
            return $armor->hasArmor() ? 34 : 40;
        }
        return $armor->hasArmor() ? 55 : 65;
    }

    public function getKillAward(): int
    {
        return 1500;
    }

}
