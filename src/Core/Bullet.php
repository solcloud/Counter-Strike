<?php

namespace cs\Core;

use cs\Interface\AttackEnable;

class Bullet
{

    private Point $position;
    private int $originPlayerId;
    private bool $originPlayerIsAttacker;
    private int $distanceTraveled = 0;
    private int $damage = 1;
    private int $damageArmor = 1;

    public function __construct(private AttackEnable $item, private int $distanceMax = 1)
    {
    }

    public function setProperties(int $damage = 1, int $damageArmor = 1): void
    {
        $this->damage = $damage;
        $this->damageArmor = $damageArmor;
    }

    public function setOriginPlayer(int $playerId, bool $attackerSide): void
    {
        $this->originPlayerId = $playerId;
        $this->originPlayerIsAttacker = $attackerSide;
    }

    public function getPosition(): Point
    {
        return $this->position;
    }

    public function lowerDamage(int $amount): void
    {
        $this->damage -= abs($amount);
    }

    public function getDamage(): int
    {
        return $this->damage;
    }

    public function getDamageArmor(): int
    {
        return $this->damageArmor;
    }

    public function isActive(): bool
    {
        return ($this->damage > 0 && $this->distanceTraveled < $this->distanceMax);
    }

    public function move(Point $point): void
    {
        $this->position = $point;
    }

    public function incrementDistance(): void
    {
        $this->distanceTraveled++;
    }

    public function getDistanceTraveled(): int
    {
        return $this->distanceTraveled;
    }

    public function getDistanceMax(): int
    {
        return $this->distanceMax;
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

}