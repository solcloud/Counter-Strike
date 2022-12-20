<?php

namespace cs\Interface;

use cs\Core\Player;
use cs\Core\Point;

interface Hittable
{

    public function getHitAntiForce(Point $point): int;

    public function getMoneyAward(): int;

    public function playerWasKilled(): bool;

    public function getPlayer(): ?Player;

    public function wasHeadShot(): bool;

    public function getDamage(): int;

}
