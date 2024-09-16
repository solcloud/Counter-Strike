<?php

namespace cs\Core;

use Exception;

/**
 * @codeCoverageIgnore
 * @infection-ignore-all
 */
class GameException extends Exception
{
    public static function notImplementedYet(string $msg = ''): never
    {
        throw new self("Not implemented yet! " . $msg);
    }

}
