<?php

namespace cs\Core;

use cs\Interface\Hittable;

abstract class SolidSurface implements Hittable
{

    public abstract function getHitAntiForce(Point $point): int;

    public function getMoneyAward(): int
    {
        return 0;
    }

    public function playerWasKilled(): bool
    {
        return false;
    }

    public function getPlayer(): ?Player
    {
        return null;
    }

    public function wasHeadShot(): bool
    {
        return false;
    }

    public function getDamage(): int
    {
        return 0;
    }

    public abstract function getPlane(): string;

}
