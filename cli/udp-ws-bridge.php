<?php

use Socket\Raw\Exception as UdpException;
use Socket\Raw\Factory;
use WebSocket\ConnectionException;
use WebSocket\Server;

require __DIR__ . '/../vendor/autoload.php';

///
$portWs = (int)($argv[1] ?? 8081);
$portUdp = (int)($argv[2] ?? 8080);
///

$addressUdp = "udp://localhost:{$portUdp}";
$udp = (new Factory())->createClient($addressUdp, 4);

$ws = new Server([
    'port'    => $portWs,
    'timeout' => 8,
]);

$logged = false;
$loginResponse = '';

while (true) {
    try {
        $clientMsg = $ws->receive();
        if (is_string($clientMsg) && $clientMsg !== '') { // if valid ws request
            if ($clientMsg === 'CLOSE') {
                break;
            }
            if (str_starts_with($clientMsg, 'login ')) { // login request
                if ($logged) { // client trying reconnect
                    $ws->text($loginResponse);
                    while ($udp->selectRead()) {
                        $udp->read(10241024);
                    }
                    continue;
                } else {
                    $logged = true;
                }
            }
            $udp->write($clientMsg);
        }

        $serverMsg = $udp->read(10241024);
        if ($loginResponse === '' && $logged) {
            $loginResponse = $serverMsg;
        }
        $ws->text($serverMsg);
    } catch (ConnectionException $ex) {
        if ($ex->getCode() !== ConnectionException::TIMED_OUT) {
            throw $ex;
        }
    } catch (UdpException $ex) {
        break;
    }
}

$udp->close();
$ws->close();
