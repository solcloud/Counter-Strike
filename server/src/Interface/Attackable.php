<?php

namespace cs\Interface;

use cs\Event\AttackResult;

interface Attackable
{

    public function fire(): AttackResult;

    public function applyRecoil(float $offsetHorizontal, float $offsetVertical): void;

    public function getTickId(): int;

}
