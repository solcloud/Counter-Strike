<?php

use Socket\Raw\Factory;
use WebSocket\Server;

require __DIR__ . '/../vendor/autoload.php';

///
$portWs = (int)($argv[1] ?? 8081);
$portUdp = (int)($argv[2] ?? 8080);
$addressUdp = "udp://localhost:{$portUdp}";
///

$udp = (new Factory())->createClient($addressUdp, 4);
$ws = new Server([
    'filter'        => ['text'],
    'fragment_size' => '8192',
    'port'          => $portWs,
    'timeout'       => 15,
]);

while (true) {
    $clientMsg = $ws->receive();
    assert(is_string($clientMsg));
    if ($clientMsg !== '') {
        if ($clientMsg === 'CLOSE') {
            break;
        }
        $udp->write($clientMsg);
    }

    $serverMsg = $udp->read(10241024);
    $ws->send($serverMsg, 'text', false);
}

$udp->close();
$ws->close();
