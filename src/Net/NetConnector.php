<?php

namespace cs\Net;

interface NetConnector
{

    /**
     * @throws NetException
     */
    public function receive(string &$peerAddress, int $blockTimeoutMicroSeconds, int $readMaxBytes = 100): ?string;

    /**
     * @throws NetException
     */
    public function sendTo(Client $client, string $msg): bool;

    public function close(): void;

}
