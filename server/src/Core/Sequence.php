<?php

namespace cs\Core;

final class Sequence
{
    private static int $value = 0;

    public static function next(): string
    {
        return 'id-' . ++self::$value;
    }
}
