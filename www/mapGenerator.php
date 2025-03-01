<?php

use cs\Core\Game;
use cs\Core\Setting;
use cs\Map;

require __DIR__ . '/../vendor/autoload.php';

////////
$map = new Map\DefaultMap();
////////

$game = new Game();
$game->loadMap($map);
$world = $game->getWorld();

$radarMapGenerator = (isset($_GET['radar']));
$showNavigationMesh = (isset($_GET['navmesh']));
$buyAreas = [
    $map->getBuyArea(false)->toArray(),
    $map->getBuyArea(true)->toArray(),
];
$spawnAttackers = [];
foreach ($map->getSpawnPositionAttacker() as $point) {
    $spawnAttackers[] = $point->toArray();
}
$spawnDefenders = [];
foreach ($map->getSpawnPositionDefender() as $point) {
    $spawnDefenders[] = $point->toArray();
}
$planes = [];
foreach ($map->getWalls() as $plane) {
    $planes[] = [
        'x' => $plane->getStart()->x,
        'y' => $plane->getStart()->y,
        'z' => $plane->getStart()->z,
        'width' => $plane->width,
        'height' => $plane->height,
        'plane' => $plane->getPlane(),
    ];
}
foreach ($map->getFloors() as $plane) {
    $planes[] = [
        'x' => $plane->getStart()->x,
        'y' => $plane->getStart()->y,
        'z' => $plane->getStart()->z,
        'width' => $plane->width,
        'height' => $plane->depth,
        'plane' => $plane->getPlane(),
    ];
}
?>
<!Doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Map Generator</title>
    <script type="importmap">
        {
            "imports": {
                "three": "./assets/threejs/three.min.js",
                "three/addons/": "./assets/threejs/"
            }
        }
    </script>
</head>
<body style="margin:0">
<div style="position:absolute;<?= $radarMapGenerator ? 'display:none;' : '' ?>">
    <textarea class="map">Generating...</textarea>
</div>
<script type="module">
    import * as THREE from 'three'
    import { OrbitControls } from 'three/addons/controls/OrbitControls.js'

    const forRadar = <?= ($radarMapGenerator ? 'true' : 'false') ?>;
    let camera, scene, renderer, controls, map, extra;
    const material = new THREE.MeshLambertMaterial({
        color: 0x9f998e,
        wireframe: <?= ((bool)($_GET['solid'] ?? false) ? 'false' : 'true') ?>,
        side: THREE.DoubleSide
    })

    function init() {
        scene = new THREE.Scene();

        renderer = new THREE.WebGLRenderer({antialias: true});
        if (forRadar) {
            renderer.setSize(800, 800);
        } else {
            renderer.setSize(window.innerWidth, window.innerHeight);
        }
        document.body.appendChild(renderer.domElement);

        if (forRadar) {
            camera = new THREE.OrthographicCamera(-400, 400, 400, -400, 1, 9000)
        } else {
            camera = new THREE.PerspectiveCamera(70, window.innerWidth / window.innerHeight, 0.01, 99999);
        }
        camera.position.y = 1000

        controls = new OrbitControls(camera, renderer.domElement);
    }

    function object() {
        const json = '<?= json_encode($planes, JSON_THROW_ON_ERROR) ?>';
        const data = JSON.parse(json);
        extra = new THREE.Group()
        extra.name = 'extra'
        map = new THREE.Group()
        map.name = 'map'

        data.forEach(function (plane) {
            let mesh = new THREE.Mesh(
                new THREE.PlaneGeometry(plane.width, plane.height),
                material,
            )

            if (plane.plane === 'zy') {
                mesh.rotateOnWorldAxis(new THREE.Vector3(0, 1, 0), Math.PI / 2)
            } else if (plane.plane === 'xz') {
                mesh.rotateOnWorldAxis(new THREE.Vector3(1, 0, 0), Math.PI / -2)
            }
            mesh.position.set(plane.x, plane.y, -1 * plane.z)
            mesh.translateX(plane.width / 2)
            mesh.translateY(plane.height / 2)

            map.add(mesh)
        })

        const d1 = new THREE.DirectionalLight(0xf0eadf, 5);
        const a1 = new THREE.AmbientLight(0xDADADA, 1)
        extra.add(d1, a1);

        scene.add(map, extra)
    }

    function spawns() {
        const spawnMaterialAttackers = new THREE.MeshStandardMaterial({color: 0xFF0000, wireframe: true, transparent: true, opacity: 0.1})
        const spawnMaterialDefenders = new THREE.MeshStandardMaterial({color: 0x0000FF, wireframe: true, transparent: true, opacity: 0.1})
        const spawnAttackersJson = '<?= json_encode($spawnAttackers, JSON_THROW_ON_ERROR) ?>';
        const spawnDefendersJson = '<?= json_encode($spawnDefenders, JSON_THROW_ON_ERROR) ?>';
        const spawnAttackers = JSON.parse(spawnAttackersJson);
        const spawnDefenders = JSON.parse(spawnDefendersJson);

        const radius = <?= Setting::playerBoundingRadius() ?>;
        const height = <?= Setting::playerHeadHeightStand() ?>;
        const geometry = new THREE.CylinderGeometry(radius, radius, height, 16);

        spawnAttackers.forEach(function (point) {
            const spawn = new THREE.Mesh(geometry, spawnMaterialAttackers);
            spawn.position.set(point.x, point.y, -point.z)
            spawn.translateY(height / 2)
            scene.add(spawn);
        })
        spawnDefenders.forEach(function (point) {
            const spawn = new THREE.Mesh(geometry, spawnMaterialDefenders);
            spawn.position.set(point.x, point.y, -point.z)
            spawn.translateY(height / 2)
            scene.add(spawn);
        })
    }

    function buyAreas() {
        const buyAreas = JSON.parse('<?= json_encode($buyAreas) ?>');

        let box = buyAreas[0];
        const areaDefenders = new THREE.Mesh(
            new THREE.BoxGeometry(box.width, box.height, box.depth),
            new THREE.MeshStandardMaterial({color: 0x0000DD, wireframe: true, transparent: true, opacity: 0.1})
        );
        areaDefenders.position.set(box.x, box.y, -1 * box.z)
        areaDefenders.translateX(box.width / 2)
        areaDefenders.translateY(box.height / 2)
        areaDefenders.translateZ(box.depth / -2)

        box = buyAreas[1];
        const areaAttackers = new THREE.Mesh(
            new THREE.BoxGeometry(box.width, box.height, box.depth),
            new THREE.MeshStandardMaterial({color: 0xDD0000, wireframe: true, transparent: true, opacity: 0.1})
        );
        areaAttackers.position.set(box.x, box.y, -1 * box.z)
        areaAttackers.translateX(box.width / 2)
        areaAttackers.translateY(box.height / 2)
        areaAttackers.translateZ(box.depth / -2)

        scene.add(areaDefenders, areaAttackers);
    }

    function plants() {
        const box = JSON.parse('<?= json_encode($map->getPlantArea()->toArray()) ?>');

        const area = new THREE.Mesh(
            new THREE.BoxGeometry(box.width, box.height, box.depth),
            new THREE.MeshStandardMaterial({color: 0xFF6600})
        );

        area.position.set(box.x, box.y - 0.1, -1 * box.z)
        area.translateX(box.width / 2)
        area.translateY(box.height / 2)
        area.translateZ(box.depth / -2)
        scene.add(area);

        <?php if ($showNavigationMesh): ?>
        <?php
            $tileSize = $world::GRENADE_NAVIGATION_MESH_TILE_SIZE;
            $path = $world->buildNavigationMesh($tileSize, $world::GRENADE_NAVIGATION_MESH_OBJECT_HEIGHT);
            $navmesh = [];
            foreach ($path->getGraph()->internalGetGeneratedNeighbors() as $nodeId => $ids) {
                $navmesh[] = $nodeId;
            }
        ?>
        let mesh = null;
        const navMeshGeometry = new THREE.BoxGeometry(<?= $tileSize ?>, .5, <?= $tileSize ?>);
        const navMeshMaterial = new THREE.MeshStandardMaterial({color: 0xD024A3})
        <?php foreach ($navmesh as $coords): ?>
        mesh = new THREE.Mesh(navMeshGeometry, navMeshMaterial)
        mesh.translateY(navMeshGeometry.parameters.height / 2)
        mesh.position.set(<?= $coords ?>)
        mesh.position.z *= -1
        scene.add(mesh);
        <?php endforeach; ?>
        if (false) {
            mesh = new THREE.Mesh(
                new THREE.BoxGeometry(20, 20, 20),
                new THREE.MeshStandardMaterial({color: 0xFF0000, transparent: true, opacity: .8})
            )
            mesh.position.set(1902, 20, -2550)
            mesh.translateY(mesh.geometry.parameters.height / 2)
            scene.add(mesh)
        }
        <?php endif; ?>
    }

    function animate() {
        renderer.render(scene, camera);
        requestAnimationFrame(animate);
    }

    init()
    object()
    if (!forRadar) {
        spawns()
        buyAreas()
    }
    plants()
    renderer.render(scene, camera);
    document.querySelector('textarea.map').innerText = JSON.stringify(map.toJSON())
    animate()
</script>
</body>
</html>
