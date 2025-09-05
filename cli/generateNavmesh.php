<?php

use cs\Core\Game;
use cs\Core\NavigationMesh;
use cs\Core\PathFinder;
use cs\Map\DefaultMap;

require __DIR__ . '/../vendor/autoload.php';

/////
$map = new DefaultMap();
/////

$game = new Game();
$planeCount = $game->loadMap($map);
$tileSize = $game->getWorld()::GRENADE_NAVIGATION_MESH_TILE_SIZE;
$colliderHeight = $game->getWorld()::GRENADE_NAVIGATION_MESH_OBJECT_HEIGHT;

echo "Generating navigation mesh for map, please wait, it could take some time..." . PHP_EOL;
$navmesh = new NavigationMesh($tileSize, $colliderHeight);
$pathFinder = new PathFinder($game->getWorld(), $navmesh);
foreach ($map->getStartingPointsForNavigationMesh() as $point) {
    $pathFinder->buildNavigationMesh($point->clone(), $navmesh->colliderHeight, 100_000);
}
assert($pathFinder->getGraph()->getEdgeCount() > 100);

foreach ($map->getFloors() as $floor) {
    if (!$floor->supportNavmesh || $floor->width <= $tileSize || $floor->depth <= $tileSize) {
        continue;
    }

    $start = $floor->getStart()->clone()->addPart((int)ceil($floor->width / 2), 0, (int)ceil($floor->depth / 2));
    $pathFinder->buildNavigationMesh($start, $navmesh->colliderHeight, 10_000);
}

$pathFinder->saveAndClear();
$path = $map->getNavigationMeshPath($map->generateNavigationMeshKey($tileSize, $colliderHeight));
file_put_contents($path, $pathFinder->getNavigationMesh()->serialize());
printf(
    "Navmesh (Planes: %d, Nodes: %d; Edges: %d) generated to '%s'%s",
    $planeCount,
    $pathFinder->getGraph()->getNodesCount(),
    $pathFinder->getGraph()->getEdgeCount(),
    $path,
    PHP_EOL,
);
