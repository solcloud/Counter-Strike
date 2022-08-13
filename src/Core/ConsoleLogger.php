<?php

namespace cs\Core;

use Psr\Log\AbstractLogger;
use Stringable;

class ConsoleLogger extends AbstractLogger
{

    public function log(mixed $level, Stringable|string $message, array $context = []): void // @phpstan-ignore-line
    {
        if (!is_string($level)) {
            $level = 'unknown';
        }
        printf("[%s] %s [%s]\n", date('Y-m-d H:i:s'), $message, $level);
    }

}
