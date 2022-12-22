<?php

namespace cs\Net;

class Client
{

    public function __construct(
        private PlayerControl $playerControl,
        public                readonly string $ip,
        public                readonly int $port
    )
    {
    }

    public function getPlayerControl(): PlayerControl
    {
        return $this->playerControl;
    }

}
