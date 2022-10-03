<?php

use cs\Core\Setting;
use cs\Core\Player;
use cs\Map;

require __DIR__ . '/../vendor/autoload.php';

////////
$map = new Map\DefaultMap();
////////

$spawnAttackers = [];
foreach ($map->getSpawnPositionAttacker() as $point) {
    $spawnAttackers[] = $point->toArray();
}
$spawnDefenders = [];
foreach ($map->getSpawnPositionDefender() as $point) {
    $spawnDefenders[] = $point->toArray();
}
$boxes = [];
foreach ($map->getBoxes() as $box) {
    $boxes[] = [
        "width"  => $box->widthX,
        "height" => $box->heightY,
        "depth"  => $box->depthZ,
        "x"      => $box->getBase()->x,
        "y"      => $box->getBase()->y,
        "z"      => $box->getBase()->z,
    ];
}
?>
<!Doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Map Generator</title>
    <script src="/assets/threejs/three.js"></script>
    <script src="/assets/threejs/orbit-control.js"></script>
</head>
<body style="margin:0">
<div style="position:absolute">
    <textarea class="map">Generating...</textarea>
    <textarea class="extra">Generating...</textarea>
</div>
<script>
    let camera, scene, renderer, controls;
    const worldMaterial = new THREE.MeshPhongMaterial({color: 0x9f998e})
    const material = new THREE.MeshPhongMaterial({color: 0x664b17})

    function init() {
        scene = new THREE.Scene();
        camera = new THREE.PerspectiveCamera(70, window.innerWidth / window.innerHeight, 0.01, 9999);
        camera.position.y = 4000

        renderer = new THREE.WebGLRenderer({antialias: true});
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        document.body.appendChild(renderer.domElement);

        controls = new THREE.OrbitControls(camera, renderer.domElement);
        scene.add(new THREE.Mesh(new THREE.SphereGeometry(10), new THREE.MeshBasicMaterial({color: 0x000000, transparent: true, opacity: 0.8})))
    }

    function createRamp(startX = 1507.1, startY = 0, endX = 1676, endY = 140.1, depth = 80) {
        const shape = new THREE.Shape();
        shape.moveTo(startX, startY);
        shape.lineTo(endX, startY);
        shape.lineTo(endX, endY);
        shape.lineTo(startX, startY);

        return new THREE.Mesh(new THREE.ExtrudeGeometry(shape, {steps: 2, depth: depth,}), material);
    }

    function object() {
        const json = '<?= json_encode($boxes, JSON_THROW_ON_ERROR) ?>';
        const data = JSON.parse(json);
        const extra = new THREE.Group()
        extra.name = 'extra'
        const map = new THREE.Group()
        map.name = 'map'

        let first = true, center, maxHeight
        data.forEach(function (box) {
            let mesh = new THREE.Mesh(
                new THREE.BoxGeometry(box.width, box.height, box.depth),
                material,
            )
            mesh.position.set(box.x, box.y, -1 * box.z)
            mesh.translateX(box.width / 2)
            mesh.translateY(box.height / 2)
            mesh.translateZ(box.depth / -2)
            mesh.castShadow = true
            mesh.receiveShadow = true

            if (first) {
                maxHeight = box.height - 1
                center = new THREE.Group()
                center.name = "center"
                center.position.set(box.width / 2, box.height / 2, box.depth / -2)
                map.add(center)
                camera.position.x = center.position.x;
                camera.position.z = center.position.z;
                camera.lookAt(camera.position.x, 0, camera.position.z)
                controls.update()

                mesh.castShadow = false
                mesh.material = worldMaterial
                mesh.material.side = THREE.BackSide
                mesh.name = "world"
                first = false
            }
            map.add(mesh)
        })

        const bulb = new THREE.Mesh(new THREE.SphereGeometry(90), new THREE.MeshBasicMaterial({color: 0xFFFFFF}))
        bulb.position.set(center.position.x, maxHeight + 30, center.position.z)
        const lightTarget = new THREE.Object3D()
        lightTarget.name = "light-target"
        lightTarget.position.set(center.position.x, 0, center.position.z)
        const s1 = new THREE.SpotLight(0xFFFFFF, .6)
        s1.castShadow = true
        s1.shadow.mapSize.width = 2048
        s1.shadow.mapSize.height = 2048
        s1.shadow.camera.near = 1
        s1.shadow.camera.far = maxHeight * 10
        s1.position.set(center.position.x, maxHeight * 2.5, center.position.z)
        s1.target = lightTarget
        const d1 = new THREE.DirectionalLight(0xffeac2, 0.6);
        const a1 = new THREE.AmbientLight(0xDADADA, .8)
        extra.add(s1, d1, a1, lightTarget, bulb);
        const ramp1 = createRamp()
        ramp1.position.z = -80.1
        extra.add(ramp1)

        scene.add(map, extra)
        renderer.render(scene, camera);
        document.querySelector('textarea.map').innerText = JSON.stringify(map.toJSON())
        document.querySelector('textarea.extra').innerText = JSON.stringify(extra.toJSON())
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

    function animate() {
        renderer.render(scene, camera);
        requestAnimationFrame(animate);
    }

    init()
    object()
    spawns()
    animate()
</script>
</body>
</html>
