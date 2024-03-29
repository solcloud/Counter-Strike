<?php

use Socket\Raw\Factory;

require __DIR__ . '/../vendor/autoload.php';

/////
$loginCode = $argv[1];
$sendEveryXTick = abs((int)($argv[2] ?? 40));
$port = (int)($argv[3] ?? 8080);
$address = "udp://localhost:$port";
/////

$socket = (new Factory())->createClient($address, 4);
$socket->write('login ' . $loginCode);

$commandPool = [
    'backward',
    'crouch',
    'forward',
    'jump',
    'left',
    fn() => sprintf('look %d %d', rand(0, 359), rand(-45, 45)),
    'right',
    'run',
    'stand',
    'walk',
];
$max = count($commandPool) - 1;
$i = rand(1, $sendEveryXTick);
while (true) { // @phpstan-ignore-line
    $socket->read(10241024);
    if (--$i !== 0) {
        continue;
    }

    $i = $sendEveryXTick;
    $command = $commandPool[rand(0, $max)];
    if (!is_string($command)) {
        $command = $command();
    }
    $socket->write($command);
}
