<?php

use cs\Core\Game;
use cs\Core\Point;
use cs\Core\Setting;
use cs\Map;

require __DIR__ . '/../vendor/autoload.php';

////////
$map = new Map\DefaultMap();
$generateMapJson = (isset($_GET['generate']));
$showNavigationMesh = (isset($_GET['navmesh']));
$showWireframe = (isset($_GET['solid']));
$showFloors = (isset($_GET['floors']));
$showWalls = (isset($_GET['walls']));
////////

$game = new Game();
$game->loadMap($map);

$buyAreas = [
    $map->getBuyArea(false)->toArray(),
    $map->getBuyArea(true)->toArray(),
];
$spawnAttackers = array_map(fn(Point $point) => $point->toArray(), $map->getSpawnPositionAttacker());
$spawnDefenders = array_map(fn(Point $point) => $point->toArray(), $map->getSpawnPositionDefender());
$planes = [];
if (!$showFloors) {
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
}
if (!$showWalls) {
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
<body style="margin:0;background:#000000;color:#FFFFFF">
<script type="module">
    import * as THREE from 'three'
    import {OrbitControls} from 'three/addons/controls/OrbitControls.js'

    let camera, scene, renderer, controls, map;
    const material = new THREE.MeshLambertMaterial({
        color: 0x9f998e,
        wireframe: <?= ($showWireframe ? 'false' : 'true') ?>,
        side: THREE.DoubleSide,
    })

    function init() {
        scene = new THREE.Scene();

        renderer = new THREE.WebGLRenderer({antialias: true})
        renderer.setSize(window.innerWidth, window.innerHeight)
        renderer.domElement.style.zIndex = -1
        document.body.appendChild(renderer.domElement)

        camera = new THREE.PerspectiveCamera(70, window.innerWidth / window.innerHeight, 0.01, 99999)
        camera.position.y = 11000

        controls = new OrbitControls(camera, renderer.domElement);
    }

    function object() {
        const json = '<?= json_encode($planes, JSON_THROW_ON_ERROR) ?>';
        const data = JSON.parse(json);
        map = new THREE.Group()

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

        scene.add(map, d1, a1)
    }

    function spawns() {
        const spawnMaterialAttackers = new THREE.MeshBasicMaterial({color: 0xFF0000, wireframe: true, transparent: true, opacity: 0.1})
        const spawnMaterialDefenders = new THREE.MeshBasicMaterial({color: 0x0000FF, wireframe: true, transparent: true, opacity: 0.1})
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
        buyAreas.forEach(function (sideAreas, teamIndex) {
            let material = new THREE.MeshBasicMaterial({
                color: teamIndex === 0 ? 0x0000DD : 0xDD0000,
                wireframe: true, transparent: true, opacity: 0.1,
            })
            sideAreas.forEach(function (box) {
                const area = new THREE.Mesh(
                    new THREE.BoxGeometry(box.width, box.height, box.depth),
                    material,
                )
                area.position.set(box.x, box.y, -1 * box.z)
                area.translateX(box.width / 2)
                area.translateY(box.height / 2)
                area.translateZ(box.depth / -2)
                scene.add(area)
            })
        });
    }

    function plants() {
        let material = new THREE.MeshBasicMaterial({color: 0xFF6600})
        const boxes = JSON.parse('<?= json_encode($map->getPlantArea()->toArray()) ?>');
        boxes.forEach(function (box) {
            const area = new THREE.Mesh(
                new THREE.BoxGeometry(box.width, box.height, box.depth),
                material,
            )

            area.position.set(box.x, box.y - 0.1, -1 * box.z)
            area.translateX(box.width / 2)
            area.translateY(box.height / 2)
            area.translateZ(box.depth / -2)
            scene.add(area)
        })

<?php if ($showNavigationMesh): ?>
        <?php
        $world = $game->getWorld();
        $tileSize = $world::GRENADE_NAVIGATION_MESH_TILE_SIZE;
        $navmesh = [];
        $navigationMesh = $map->getNavigationMesh($map->generateNavigationMeshKey($tileSize, $world::GRENADE_NAVIGATION_MESH_OBJECT_HEIGHT));
        foreach ($navigationMesh->getData() as $nodeId => $ids) {
            $navmesh[] = $nodeId;
        }
        ?>
        const navMeshGeometry = new THREE.BoxGeometry(<?= $tileSize ?>, .5, <?= $tileSize ?>);
        const navMeshMaterial = new THREE.MeshStandardMaterial({color: 0xD024A3})
        let mesh
    <?php foreach ($navmesh as $coords): ?>
        mesh = new THREE.Mesh(navMeshGeometry, navMeshMaterial)
        mesh.translateY(navMeshGeometry.parameters.height / 2)
        mesh.position.set(<?= $coords ?>)
        mesh.position.z *= -1
        scene.add(mesh);
    <?php endforeach; ?>
<?php endif; ?>
    }

    function animate() {
        renderer.render(scene, camera);
        requestAnimationFrame(animate);
    }

    init()
    object()
    spawns()
    buyAreas()
    plants()
    <?php if ($generateMapJson) : ?>
    renderer.render(scene, camera)
    console.log(JSON.stringify(map.toJSON()))
    <?php endif; ?>
    animate()
</script>
</body>
</html>
