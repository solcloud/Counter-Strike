<?php

namespace cs\Core;

use cs\Interface\AttackEnable;

class Bullet
{

    private Point $origin;
    private Point $position;
    private int $originPlayerId;
    private bool $originPlayerIsAttacker;
    private int $distanceTraveled;
    private int $damage = 1;
    /** @var array<int,true> [playerId => true] */
    private array $playerSkipIds = [];

    public function __construct(private readonly AttackEnable $item, public readonly int $distanceMax = 1)
    {
        $this->distanceTraveled = Setting::playerHeadRadius(); // shooting from center of player head so lets start on head edge
    }

    public function setProperties(int $damage): void
    {
        $this->damage = $damage;
    }

    public function setOriginPlayer(int $playerId, bool $attackerSide, Point $origin): void
    {
        $this->originPlayerId = $playerId;
        $this->originPlayerIsAttacker = $attackerSide;
        $this->origin = $origin;
        $this->position = $origin->clone();
        $this->addPlayerIdSkip($playerId);
    }

    public function getPosition(): Point
    {
        return $this->position;
    }

    public function getOrigin(): Point
    {
        return $this->origin;
    }

    public function lowerDamage(int $amount): void
    {
        assert($amount >= 0);
        $this->damage -= $amount;
    }

    public function getDamage(): int
    {
        return $this->damage;
    }

    public function isActive(): bool
    {
        return ($this->damage > 0 && $this->distanceTraveled < $this->distanceMax);
    }

    public function move(Point $point): void
    {
        $this->position->setFrom($point);
    }

    public function incrementDistance(): int
    {
        return ++$this->distanceTraveled;
    }

    public function getDistanceTraveled(): int
    {
        return $this->distanceTraveled;
    }

    public function getOriginPlayerId(): int
    {
        return $this->originPlayerId;
    }

    public function isOriginPlayerAttackerSide(): bool
    {
        return $this->originPlayerIsAttacker;
    }

    public function getShootItem(): AttackEnable
    {
        return $this->item;
    }

    /**
     * @return array<int,true> [playerId => true]
     */
    public function getPlayerSkipIds(): array
    {
        return $this->playerSkipIds;
    }

    public function addPlayerIdSkip(int $playerId): void
    {
        $this->playerSkipIds[$playerId] = true;
    }

}
