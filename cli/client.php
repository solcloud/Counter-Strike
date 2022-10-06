<?php

use Socket\Raw\Factory;

require __DIR__ . '/../vendor/autoload.php';

/////
$loginCode = $argv[1] ?? 'dcode';
$port = (int)($argv[2] ?? 8080);
$command = ($argv[3] ?? '');
$address = "udp://localhost:$port";
/////

$factory = new Factory();
$socket = $factory->createClient($address, 4);

$socket->write('login ' . $loginCode);
while (true) {
    $response = $socket->read(10241024);
    if ($command) {
        $socket->write($command);
        continue;
    }

    echo PHP_EOL . PHP_EOL . PHP_EOL . "--------------------------";
    echo "Server data:" . PHP_EOL;
    var_dump($response);
    echo "--------------------------" . PHP_EOL . PHP_EOL . PHP_EOL;
    $request = readline("Send command: ");
    if ($request === false) {
        break;
    }
    $request = trim($request);
    while ($socket->selectRead()) { // drain socket
        $socket->read(10241024);
    }
    if ($request !== '') {
        $socket->write($request);
    }
}
$socket->close();
