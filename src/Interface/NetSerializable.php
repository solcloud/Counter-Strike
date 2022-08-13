<?php

namespace cs\Interface;

interface NetSerializable
{

    /**
     * @return array<string,mixed>
     */
    public function serialize(): array;

    public function getCode(): int;

}
