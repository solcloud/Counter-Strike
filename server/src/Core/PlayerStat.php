<?php

namespace cs\Core;

class PlayerStat
{
    private int $kills = 0;
    private int $killsHeadshot = 0;
    private int $deaths = 0;
    private int $damage = 0;

    public function __construct(private Player $player)
    {
    }

    public function addKill(bool $wasHeadshot): void
    {
        $this->kills++;
        if ($wasHeadshot) {
            $this->killsHeadshot++;
        }
    }

    public function addDeath(): void
    {
        $this->deaths++;
    }

    public function addDamage(int $damage): void
    {
        $this->damage += min($damage, 100);
    }

    public function removeKill(): void
    {
        $this->kills--;
    }

    public function getKills(): int
    {
        return $this->kills;
    }

    public function getHeadshotKills(): int
    {
        return $this->killsHeadshot;
    }

    public function getDeaths(): int
    {
        return $this->deaths;
    }

    public function getDamage(): int
    {
        return $this->damage;
    }

    public function isAttacker(): bool
    {
        return $this->player->isPlayingOnAttackerSide();
    }

    /**
     * @return array<string,int>
     */
    public function toArray(): array
    {
        return [
            'id'     => $this->player->getId(),
            'kills'  => $this->kills,
            'deaths' => $this->deaths,
            'damage' => $this->damage,
        ];
    }

}
