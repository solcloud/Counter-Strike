<?php

namespace cs\Core;

use cs\Enum\HitBoxType;
use cs\Interface\HitIntersect;
use cs\Interface\Hittable;
use cs\Weapon\Knife;

class HitBox implements Hittable
{
    private int $moneyAward;
    private bool $playerWasKilled;
    private bool $wasHeadShot;

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

    private function checkBackStab(Bullet $bullet): bool
    {
        $item = $bullet->getShootItem();
        if (!($item instanceof Knife)) {
            return false;
        }

        // TODO do back hit box based on player and bullet angle, or add HitBoxBack geometry
        return false;
    }

    public function registerHit(Bullet $bullet): void
    {
        $type = $this->type;
        if ($type === HitBoxType::CHEST && $this->checkBackStab($bullet)) {
            $type = HitBoxType::BACK;
        }

        $shootItem = $bullet->getShootItem();
        $healthDamage = $shootItem->getDamageValue($type, $this->player->getArmorType());
        $this->player->lowerHealth($healthDamage);

        if (!$this->player->isAlive()) {
            $this->playerWasKilled = true;
            $this->wasHeadShot = ($type === HitBoxType::HEAD);
            if ($bullet->isOriginPlayerAttackerSide() === $this->player->isPlayingOnAttackerSide()) {
                $this->moneyAward = -300; // team kill
            } else {
                $this->moneyAward = $shootItem->getKillAward();
            }
        }
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

    public function getGeometry(): HitIntersect
    {
        return $this->geometry;
    }

}
