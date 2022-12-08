<?php

namespace cs\Event;

use cs\Core\Bullet;
use cs\Interface\Hittable;

final class AttackResult
{
    /** @var Hittable[] */
    private array $hits = [];
    private int $moneyAward = 0;
    private bool $somePlayersWasHit = false;

    public function __construct(private Bullet $bullet)
    {
    }

    public function addHit(Hittable $hit): void
    {
        $this->hits[] = $hit;
        $this->moneyAward += $hit->getMoneyAward();
        if ($hit->getPlayer() !== null) {
            $this->somePlayersWasHit = true;
        }
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

    public function somePlayersWasHit(): bool
    {
        return $this->somePlayersWasHit;
    }

}
