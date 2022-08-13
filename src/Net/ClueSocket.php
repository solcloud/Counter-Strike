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

    public function __construct(string $bindAddress)
    {
        try {
            $factory = new Factory();
            $this->socket = $factory->createServer($bindAddress);
        } catch (Exception $ex) {
            throw new NetException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    private function setSocketTimeout(int $timeoutMicroSeconds): void
    {
        $sec = (int)floor($timeoutMicroSeconds / 1000000);
        $this->socket->setOption(SOL_SOCKET, SO_RCVTIMEO, [
            'sec'  => $sec,
            'usec' => $timeoutMicroSeconds % 1000000,
        ]);
    }

    public function sendTo(Client $client, string $msg): bool
    {
        try {
            $bytesSend = $this->socket->sendTo($msg, MSG_EOR, $client->getAddress());
            if ($bytesSend === strlen($msg)) {
                return true;
            }
        } catch (Exception $ex) {
            throw new NetException($ex->getMessage(), $ex->getCode(), $ex);
        }

        return false;
    }

    public function receive(string &$peerAddress, int $blockTimeoutMicroSeconds, int $readMaxBytes = 100): ?string
    {
        $this->setSocketTimeout($blockTimeoutMicroSeconds);

        try {
            return $this->socket->recvFrom($readMaxBytes, MSG_OOB, $peerAddress);
        } catch (Exception $ex) {
            if ($ex->getCode() !== SOCKET_EAGAIN) {
                throw new NetException($ex->getMessage(), $ex->getCode(), $ex);
            }
        }

        return null;
    }

    public function close(): void
    {
        $this->socket->close();
    }

}
