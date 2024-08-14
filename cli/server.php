<?php

use cs\Core\ConsoleLogger;
use cs\Core\GameFactory;
use cs\Map as Maps;
use cs\Net\ClueSocket;
use cs\Net\Server;
use cs\Net\ServerSetting;

require __DIR__ . '/../vendor/autoload.php';

/////
$playersMax = (int)($argv[1] ?? 1);
$port = (int)($argv[2] ?? 8080);
$debug = in_array('--debug', $argv);
$bindAddress = "udp://0.0.0.0:$port";
/////

$settings = new ServerSetting($playersMax); // must be first for correctly setting the global tickRate (Util::$TICK_RATE)

$logger = new ConsoleLogger();
$logger->info("Preparing game for launch.");

$game = ($debug ? GameFactory::createDebug() : GameFactory::createDefaultCompetitive());
$game->loadMap(new Maps\DefaultMap());
$game->getWorld()->regenerateNavigationMeshes();

$logger->info("Starting server on '{$bindAddress}', waiting maximum of '{$settings->warmupWaitSec}' sec for '{$playersMax}' player" . ($playersMax > 1 ? 's' : '') . " to connect.");
$net = new ClueSocket($bindAddress);
$server = new Server($game, $settings, $net);
$server->setLogger($logger);
if ($debug) {
    $server->storeRequests();
}

$server->start();
sleep(1);
$net->close();
