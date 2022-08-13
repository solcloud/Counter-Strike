<?php

namespace cs\Core;

use Exception;

class GameException extends Exception
{
    /**
     * @codeCoverageIgnore
     */
    public static function notImplementedYet(string $msg = ''): never
    {
        throw new self("Not implemented yet! " . $msg);
    }

}
