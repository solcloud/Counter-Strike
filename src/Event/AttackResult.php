<?php

namespace cs\Event;

use cs\Core\Bullet;
use cs\Interface\Hittable;

final class AttackResult
{
    private int $moneyAward = 0;

    public function __construct(private Bullet $bullet)
    {
    }

    /** @var Hittable[] */
    private array $hits = [];

    public function addHit(Hittable $hit): void
    {
        $this->hits[] = $hit;
        $this->moneyAward += $hit->getMoneyAward();
    }

    /**
     * @return Hittable[]
     */
    public function getHits(): array
    {
        return $this->hits;
    }

    public function getBullet(): Bullet
    {
        return $this->bullet;
    }

    public function getMoneyAward(): int
    {
        return $this->moneyAward;
    }

}
