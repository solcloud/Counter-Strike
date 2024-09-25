<?php

namespace cs\Interface;

use cs\Core\Bullet;
use cs\Enum\ArmorType;
use cs\Enum\HitBoxType;
use cs\Enum\ItemType;
use cs\Event\AttackResult;

interface AttackEnable {

    public function attack(Attackable $event): ?AttackResult;

    public function attackSecondary(Attackable $event): ?AttackResult;

    public function getDamageValue(HitBoxType $hitBox, ArmorType $armor): int;

    public function getKillAward(): int;

    public function getType(): ItemType;

    public function getId(): int;

    public function createBullet(): Bullet;

}
