<?php

use cs\Core\ConsoleLogger;
use cs\Core\GameFactory;
use cs\Core\Map;
use cs\Map\DefaultMap;
use cs\Net\ClueSocket;
use cs\Net\Server;
use cs\Net\ServerSetting;

require __DIR__ . '/../vendor/autoload.php';

/////
$playersMax = (int)($argv[1] ?? 1);
$port = (int)($argv[2] ?? 8080);
$debug = (bool)($argv[3] ?? 0);
$bindAddress = "udp://0.0.0.0:$port";
/////

$logger = new ConsoleLogger();
$settings = new ServerSetting($playersMax);
$logger->info("Starting server on '{$bindAddress}', waiting maximum of '{$settings->warmupWaitSec}' sec for '{$playersMax}' player" . ($playersMax > 1 ? 's' : '') . " to connect.");
$net = new ClueSocket($bindAddress);

$game = ($debug ? GameFactory::createDebug() : GameFactory::createDefaultCompetitive());
$game->loadMap(new DefaultMap());

$server = new Server($game, $settings, $net);
$server->setLogger($logger);
if ($debug) {
    $server->storeRequests();
}

$server->start();
sleep(1);
$net->close();
