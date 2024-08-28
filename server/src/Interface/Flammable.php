<?php

namespace cs\Interface;

interface Flammable extends Volumetric
{

    public function getBoundingRadius(): int;

    public function calculateDamage(bool $hasKevlar): int;

}
