<?php

namespace cs\Net;

use cs\Core\Util;

class ServerSetting
{
    public int $warmupWaitSecRemains;

    public function __construct(
        public readonly int $playersMax,
        public readonly int $tickMs = 20,
        public readonly string $attackerCode = 'acode',
        public readonly string $defenderCode = 'dcode',
        public readonly bool $warmupInstantStart = true,
        public readonly int $warmupWaitSec = 60,
    )
    {
        if ($this->tickMs > 0) {
            Util::$TICK_RATE = $tickMs;
        }
        $this->warmupWaitSecRemains = $this->warmupWaitSec;
    }

}
