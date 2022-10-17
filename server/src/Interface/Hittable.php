<?php

namespace cs\Interface;

use cs\Core\Player;

interface Hittable
{

    public function getHitAntiForce(): int;

    public function getMoneyAward(): int;

    public function playerWasKilled(): bool;

    public function getPlayer(): ?Player;

    public function wasHeadShot(): bool;

}
