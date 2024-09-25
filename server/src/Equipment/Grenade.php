<?php

namespace cs\Equipment;

use cs\Core\Bullet;
use cs\Core\GameException;
use cs\Enum\ArmorType;
use cs\Enum\HitBoxType;
use cs\Enum\ItemType;
use cs\Event\AttackResult;
use cs\Interface\Attackable;
use cs\Interface\AttackEnable;

abstract class Grenade extends BaseEquipment implements AttackEnable
{

    private bool $primaryAttack = true;
    public const equipReadyTimeMs = 100;
    public const boundingRadius = 10;

    public function getType(): ItemType
    {
        return ItemType::TYPE_GRENADE;
    }

    public function attack(Attackable $event): ?AttackResult
    {
        $this->primaryAttack = true;
        return $event->fire();
    }

    public function attackSecondary(Attackable $event): ?AttackResult
    {
        $this->primaryAttack = false;
        return $event->fire();
    }

    public function getDamageValue(HitBoxType $hitBox, ArmorType $armor): int
    {
        GameException::invalid();
    }

    public function getKillAward(): int
    {
        return 300;
    }

    public function getBoundingRadius(): int
    {
        return static::boundingRadius;
    }

    public function getSpeedMultiplier(): float
    {
        return ($this->primaryAttack ? 1.0 : 0.5);
    }

    public function createBullet(): Bullet
    {
        GameException::invalid(get_class($this));
    }

}
