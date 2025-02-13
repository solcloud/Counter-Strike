<?php

namespace cs\Net;

final readonly class Client
{

    public function __construct(
        public PlayerControl $playerControl,
        public string        $ip,
        public int           $port
    )
    {
    }

}
