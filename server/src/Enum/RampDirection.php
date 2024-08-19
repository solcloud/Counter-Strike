<?php

namespace cs\Enum;

enum RampDirection
{

    case GROW_TO_POSITIVE_X;
    case GROW_TO_POSITIVE_Z;
    case GROW_TO_NEGATIVE_X;
    case GROW_TO_NEGATIVE_Z;

    public function isOnXAxis(): bool
    {
        return $this === self::GROW_TO_POSITIVE_X || $this === self::GROW_TO_NEGATIVE_X;
    }

    public function growToPositive(): bool
    {
        return $this === self::GROW_TO_POSITIVE_X || $this === self::GROW_TO_POSITIVE_Z;
    }

}
