<?php

namespace cs\Net;

interface NetConnector
{

    /**
     * @throws NetException
     */
    public function receive(?string &$peerAddress, ?int &$peerPort, int $readMaxBytes = 100): ?string;

    /**
     * @throws NetException
     */
    public function sendTo(Client $client, string &$msg): void;

    public function close(): void;

}
