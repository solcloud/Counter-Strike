<?php

namespace cs\Core;

use cs\Interface\Hittable;

abstract class SolidSurface implements Hittable
{
    protected int $hitAntiForce = 25123;

    public function getHitAntiForce(Point $point): int
    {
        return $this->hitAntiForce;
    }

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

}
