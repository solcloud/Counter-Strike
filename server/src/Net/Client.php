<?php

namespace cs\Net;

final class Client
{

    public function __construct(
        public readonly PlayerControl $playerControl,
        public readonly string        $ip,
        public readonly int           $port
    )
    {
    }

}
