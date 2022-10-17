<?php

namespace cs\Net;

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
        $this->warmupWaitSecRemains = $this->warmupWaitSec;
    }

}
