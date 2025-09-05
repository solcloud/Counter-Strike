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
$map = Maps\DefaultMap::class;
ini_set('memory_limit', '1G');
/////

$settings = new ServerSetting($playersMax); // must be first for correctly setting the global tickRate (Util::$TICK_RATE)

$logger = new ConsoleLogger();
$logger->info("Preparing game for launch, please wait...");

$game = ($debug ? GameFactory::createDebug() : GameFactory::createDefaultCompetitive());
$game->loadMap(new $map);
$game->getWorld()->regenerateNavigationMeshes();

$logger->info("Starting server on '{$bindAddress}', waiting maximum of '{$settings->warmupWaitSec}' sec for '{$playersMax}' player" . ($playersMax > 1 ? 's' : '') . " to connect.");
$net = new ClueSocket($bindAddress);
$server = new Server($game, $settings, $net);
$server->setLogger($logger);
if ($debug) {
    $server->storeRequests(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cs.server.req');
}

$server->start();
sleep(1);
$net->close();
