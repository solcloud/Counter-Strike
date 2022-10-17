<?php

namespace cs\Net;

class Client
{

    public function __construct(
        private PlayerControl $playerControl,
        private string        $ip,
        private int           $port
    )
    {
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getAddress(): string
    {
        return "{$this->getIp()}:{$this->getPort()}";
    }

    public function getPlayerControl(): PlayerControl
    {
        return $this->playerControl;
    }

}
