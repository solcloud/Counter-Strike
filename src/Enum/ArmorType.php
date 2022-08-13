<?php

namespace cs\Enum;

enum ArmorType
{

    case BODY;
    case BODY_AND_HEAD;
    case NONE;

    public function hasArmorHead(): bool
    {
        return ($this === self::BODY_AND_HEAD);
    }

    public function hasArmor(): bool
    {
        return ($this === self::BODY);
    }

}
