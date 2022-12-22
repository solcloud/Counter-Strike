<?php

use Socket\Raw\Factory;

require __DIR__ . '/../vendor/autoload.php';

/////
$loginCode = ($argv[1] ?? 'acode');
$port = (int)($argv[2] ?? 8080);
$address = "udp://localhost:$port";
/////

$factory = new Factory();
$socket = $factory->createClient($address, 4);
$socket->write('login ' . $loginCode);

$bool = true;
while (true) { // @phpstan-ignore-line
    $socket->read(10241024);
    $socket->write($bool ? 'left' : 'right');
    $bool = !$bool;
}
