<?php

namespace cs\Interface;

use cs\Enum\ArmorType;
use cs\Enum\HitBoxType;
use cs\Event\AttackEvent;
use cs\Event\AttackResult;

interface AttackEnable
{
    public function attack(AttackEvent $event): ?AttackResult;

    public function attackSecondary(AttackEvent $event): ?AttackResult;

    public function getDamageValue(HitBoxType $hitBox, ArmorType $armor): int;

    public function getKillAward(): int;

    public function getId(): int;

}
