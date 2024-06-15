<?php

namespace cs\Net;

use Socket\Raw\Exception;
use Socket\Raw\Factory;
use Socket\Raw\Socket;

/**
 * @codeCoverageIgnore
 */
class ClueSocket implements NetConnector
{

    private Socket $socket;
    private readonly \Socket $resource;

    public function __construct(string $bindAddress)
    {
        try {
            $this->socket = (new Factory())->createServer($bindAddress);
            $this->resource = $this->socket->getResource(); // @phpstan-ignore-line
            socket_set_nonblock($this->resource);
        } catch (Exception $ex) {
            throw new NetException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function sendTo(Client $client, string &$msg): void
    {
        $ret = @socket_sendto($this->resource, $msg, strlen($msg), 0, $client->ip, $client->port);
        if ($ret === false) {
            $code = socket_last_error($this->resource);
            throw new NetException(socket_strerror($code), $code);
        }
    }

    public function receive(?string &$peerAddress, ?int &$peerPort, int $readMaxBytes = 100): ?string
    {
        $ret = @socket_recvfrom($this->resource, $buffer, $readMaxBytes, 0, $peerAddress, $peerPort);
        if ($ret === false) {
            $code = socket_last_error($this->resource);
            if ($code !== SOCKET_EWOULDBLOCK) {
                throw new NetException(socket_strerror($code), $code);
            }
            return null;
        }

        return $buffer;
    }

    public function close(): void
    {
        $this->socket->close();
    }

}
