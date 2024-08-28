<?php

namespace cs\Net;

use cs\Core\Util;

class ServerSetting
{
    public int $warmupWaitSecRemains;

    public function __construct(
        public readonly int $playersMax,
        public readonly int $tickMs = 10,
        public readonly string $attackerCode = 'acode',
        public readonly string $defenderCode = 'dcode',
        public readonly bool $warmupInstantStart = true,
        public readonly int $warmupWaitSec = 60,
    )
    {
        Util::$TICK_RATE = ($tickMs > 0 ? $tickMs : Util::$TICK_RATE); // side effect
        $this->warmupWaitSecRemains = $this->warmupWaitSec;
    }

}
