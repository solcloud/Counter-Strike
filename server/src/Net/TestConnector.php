<?php

namespace cs\Net;

use cs\Core\GameException;

class TestConnector implements NetConnector
{
    public const msg = '_Test server shutdown_';

    private int $iterator = 0;
    /** @var string[] */
    private array $requests;
    /** @var string[] */
    private array $responses = [];
    private bool $expectReceive = true;

    /**
     * @param string[] $requests
     */
    public function __construct(array $requests)
    {
        $this->requests = $requests;
    }

    public function receive(?string &$peerAddress, ?int &$peerPort, int $readMaxBytes = 100): ?string
    {
        if (!$this->expectReceive) {
            return null;
        }

        $peerAddress = 'test';
        $peerPort = 1234;
        $request = ($this->requests[$this->iterator++] ?? null);
        if ($request === null) {
            $this->close();
        }

        $this->expectReceive = false;
        return $request;
    }

    public function sendTo(Client $client, string &$msg): void
    {
        $this->expectReceive = true;
        $this->responses[] = $msg;
    }

    public function close(): void
    {
        throw new GameException(self::msg);
    }

    /**
     * @return string[]
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

}
