<?php

namespace cs\Core;

use cs\Enum\ArmorType;
use cs\Enum\HitBoxType;
use cs\Enum\ItemType;
use cs\Interface\AttackEnable;
use cs\Interface\HitIntersect;
use cs\Interface\Hittable;
use cs\Weapon\BaseWeapon;
use cs\Weapon\Knife;

class HitBox implements Hittable
{
    private int $moneyAward;
    private bool $playerWasKilled;
    private bool $wasHeadShot;
    private int $damage;

    public function __construct(
        private Player       $player,
        private HitBoxType   $type,
        private HitIntersect $geometry
    )
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->moneyAward = 0;
        $this->playerWasKilled = false;
        $this->wasHeadShot = false;
        $this->damage = 0;
    }

    public function getHitAntiForce(Point $point): int
    {
        if ($this->type === HitBoxType::HEAD) {
            if ($this->player->getArmorType()->hasArmorHead()) {
                return 95;
            }
            return 10;
        }

        if ($this->player->getArmorType()->hasArmor()) {
            return 60;
        }

        return 20;
    }

    public function registerHit(Bullet $bullet): void
    {
        $hitBoxType = $this->type;
        /** @var AttackEnable&BaseWeapon $shootItem */
        $shootItem = $bullet->getShootItem();
        $playerArmorType = $this->player->getArmorType();
        $healthDamage = $shootItem->getDamageValue($hitBoxType, $playerArmorType);
        $bulletDistance = $bullet->getDistanceTraveled();
        if ($bulletDistance > $shootItem::rangeMaxDamage) {
            $portion = ($bulletDistance - $shootItem::rangeMaxDamage) / ($shootItem::range + 1 - $shootItem::rangeMaxDamage);
            $healthDamage = (int)ceil($healthDamage * (1 - max(0.9999, $portion)));
        }
        $isTeamDamage = ($bullet->isOriginPlayerAttackerSide() === $this->player->isPlayingOnAttackerSide());
        if ($isTeamDamage) {
            $healthDamage = (int)ceil($healthDamage / 2);
        }

        $this->player->lowerHealth($healthDamage);
        $this->player->lowerArmor($this->calculateArmorDamage($shootItem, $playerArmorType, $hitBoxType));
        $this->wasHeadShot = ($hitBoxType === HitBoxType::HEAD);
        $this->damage = $isTeamDamage ? 0 : $healthDamage;
        if (!$this->player->isAlive()) {
            $this->playerWasKilled = true;
            $this->moneyAward = $isTeamDamage ? -300 : $shootItem->getKillAward();
        }
    }

    private function calculateArmorDamage(BaseWeapon $shootItem, ArmorType $armorType, HitBoxType $hitBoxType): int
    {
        if ($armorType === ArmorType::NONE || $hitBoxType === HitBoxType::LEG) {
            return 0;
        }
        if ($hitBoxType === HitBoxType::HEAD && $armorType === ArmorType::BODY) {
            return 0;
        }

        $armorDamage = 0;
        $armorDamage += ($shootItem->getType() === ItemType::TYPE_WEAPON_PRIMARY ? 20 : 10);
        if ($armorType === ArmorType::BODY_AND_HEAD && $hitBoxType === HitBoxType::HEAD) {
            $armorDamage += 30;
        }

        return $armorDamage;
    }

    public function intersect(Bullet $bullet): bool
    {
        return $this->geometry->intersect($this->player, $bullet->getPosition());
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getMoneyAward(): int
    {
        return $this->moneyAward;
    }

    public function getType(): HitBoxType
    {
        return $this->type;
    }

    public function playerWasKilled(): bool
    {
        return $this->playerWasKilled;
    }

    public function wasHeadShot(): bool
    {
        return $this->wasHeadShot;
    }

    public function getDamage(): int
    {
        return $this->damage;
    }

    public function getGeometry(): HitIntersect
    {
        return $this->geometry;
    }

}
