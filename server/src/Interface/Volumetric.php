<?php

namespace cs\Interface;

interface Volumetric
{
    public function getSpawnAreaMetersSquared(): int;

    public function getMaxTimeMs(): int;

    public function getMaxAreaMetersSquared(): int;
}
