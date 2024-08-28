<?php

use cs\Core\Game;
use cs\Core\Player;
use cs\Core\PlayerCollider;
use cs\Core\Point;
use cs\Enum\Color;
use cs\Enum\HitBoxType;
use cs\Enum\InventorySlot;
use cs\Equipment;
use cs\HitGeometry\SphereGroupHitBox;
use cs\Map\TestMap;
use cs\Weapon;

require __DIR__ . '/../vendor/autoload.php';

////////
$game = new Game();
$game->loadMap(new TestMap());
$player = new Player(0, Color::GREEN, true);
$player->setWorld($game->getWorld());
$player->getSight()->lookHorizontal(22);
$collider = new PlayerCollider($player);
////////

if (is_numeric($_GET['crouch'] ?? false)) {
    $player->crouch();
    for ($tick = 0; $tick <= intval($_GET['crouch']); $tick++) {
        $player->onTick($tick);
    }
}

$playerParts = [];
foreach ($collider->getHitBoxes() as $box) {
    $geometry = $box->getGeometry();
    if ($geometry instanceof SphereGroupHitBox) {
        $modifier = $geometry->centerPointModifier;
        $modifier = $modifier === null ? new Point() : $modifier($player);
        foreach ($geometry->getParts($player) as $part) {
            $playerParts[$box->getType()->value][] = [
                "center" => $part->calculateWorldCoordinate($player, $modifier)->toArray(),
                "radius" => $part->radius,
            ];
        }
        continue;
    }

    throw new Exception("Unknown geometry '" . get_class($geometry) . "' given");
}

$slots = [
    InventorySlot::SLOT_KNIFE->value     => (new Weapon\Knife())->toArray(),
    InventorySlot::SLOT_PRIMARY->value   => (new Weapon\RifleAk())->toArray(),
    InventorySlot::SLOT_SECONDARY->value => (new Weapon\PistolGlock())->toArray(),
    InventorySlot::SLOT_BOMB->value      => (new Equipment\Bomb(1, 1))->toArray(),
    InventorySlot::SLOT_KIT->value       => (new Equipment\DefuseKit())->toArray(),
];

?>
<!Doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Player model Generator</title>
    <script type="importmap">
        {
            "imports": {
                "three": "./assets/threejs/three.min.js",
                "three/addons/": "./assets/threejs/"
            }
        }
    </script>
    <script src="./assets/js/utils.js"></script>
</head>
<body style="margin:0">
<script type="module">
    import * as THREE from 'three'
    import { OrbitControls } from 'three/addons/controls/OrbitControls.js'
    import {ModelRepository} from "./assets/js/ModelRepository.js";

    let camera, scene, renderer, controls;

    ////
    // fixme: add gui controls
    const opacityPlayer = 1.0
    const opacityHitBoxes = 0.0
    const opacityBoundingBox = 0.0
    ////

    const materialDefault = new THREE.MeshBasicMaterial({color: 0x664b17, transparent: true, opacity: opacityHitBoxes, depthTest: false})
    const materialArm = new THREE.MeshBasicMaterial({color: 0x114b3d, transparent: true, opacity: opacityHitBoxes, depthTest: false})
    const materialBack = new THREE.MeshBasicMaterial({color: 0x320121, transparent: true, opacity: opacityHitBoxes, depthTest: false})
    const materialLeg = new THREE.MeshBasicMaterial({color: 0x196b1a, transparent: true, opacity: opacityHitBoxes, depthTest: false})
    const materialBody = new THREE.MeshBasicMaterial({color: 0xFF6600, transparent: true, opacity: opacityHitBoxes, depthTest: false})
    const bbMaterial = new THREE.MeshBasicMaterial({color: 0x000aa0, transparent: true, opacity: opacityBoundingBox, depthTest: true})
    const modelHeight = <?= $player->getHeadHeight() ?>

    function init() {
        scene = new THREE.Scene();
        scene.background = new THREE.Color(0xdadada);

        scene.add(
            new THREE.DirectionalLight(0xffeac2, 2.8),
            new THREE.AmbientLight(0xDADADA, 1.2),
            new THREE.AmbientLight(0xcecece, 1.1),
        );

        camera = new THREE.PerspectiveCamera(70, window.innerWidth / window.innerHeight, 0.01, 9999);
        camera.position.y = 180
        camera.position.z = -140

        renderer = new THREE.WebGLRenderer({antialias: true});
        renderer.setSize(window.innerWidth, window.innerHeight);
        document.body.appendChild(renderer.domElement);

        controls = new OrbitControls(camera, renderer.domElement);
        controls.target.set(0, 110, 0)
        controls.update()
    }

    function createSphere(sphere, material) {
        const mesh = new THREE.Mesh(new THREE.SphereGeometry(sphere.radius), material || materialDefault)
        mesh.position.set(sphere.center.x, sphere.center.y, -1 * sphere.center.z)
        return mesh
    }

    function extra() {
        // Bounding box
        const bbRadius = <?= $player->getBoundingRadius() ?>;
        const bb = new THREE.Mesh(new THREE.CylinderGeometry(bbRadius, bbRadius, modelHeight, 16), bbMaterial);
        bb.translateY(bb.geometry.parameters.height / 2)
        scene.add(bb)
    }

    function animate() {
        renderer.render(scene, camera);
        requestAnimationFrame(animate);
    }

    init()
    extra()
    animate()

    const modelRepository = new ModelRepository()
    await modelRepository.loadAll()
    const player = modelRepository.getPlayer(1, true)
    if (opacityPlayer < 1) {
        player.traverse((o) => {
            if (!o.material) {
                return
            }
            o.material.depthTest = false
            o.material.transparent = true
            o.material.opacity = opacityPlayer
        })
    }
    if (opacityPlayer > 0) {
        player.rotation.y = serverHorizontalRotationToThreeRadian(<?= $player->getSight()->getRotationHorizontal() ?>)
        scene.add(player)
    }

    const mixer = new THREE.AnimationMixer(player)
    const playerAnimation = modelRepository.getPlayerAnimation()
    playerAnimation.forEach((clip) => {
        const action = mixer.clipAction(clip);
        action.play()
    })
    mixer.setTime(<?= $player->getHeadHeight() - 1 ?>)

    if (opacityHitBoxes > 0) {
        const json = '<?= json_encode($playerParts, JSON_THROW_ON_ERROR) ?>';
        const data = JSON.parse(json);

        data[<?= HitBoxType::HEAD->value ?>].forEach(function (sphereData) {
            scene.add(createSphere(sphereData))
        })
        data[<?= HitBoxType::BACK->value ?>].forEach(function (sphereData) {
            scene.add(createSphere(sphereData, materialBack))
        })
        data[<?= HitBoxType::STOMACH->value ?>].forEach(function (sphereData) {
            scene.add(createSphere(sphereData, materialBody))
        })
        data[<?= HitBoxType::CHEST->value ?>].forEach(function (sphereData) {
            scene.add(createSphere(sphereData, materialArm))
        })
        data[<?= HitBoxType::LEG->value ?>].forEach(function (sphereData) {
            scene.add(createSphere(sphereData, materialLeg))
        })
    }

    const slotsJson = '<?= json_encode($slots, JSON_THROW_ON_ERROR) ?>';
    const slots = JSON.parse(slotsJson);
    const belt = player.getObjectByName('belt')
    belt.children.forEach(function (slot) {
        let item = slots[slot.name.replace('slot-', '')]
        if (item) {
            slot.add(modelRepository.getModelForItem(item))
        }
    })

    let handItem = modelRepository.getModelForItem(slots[<?= array_rand($slots) ?>])
    if (false) {
        handItem = modelRepository.getModelForItem(slots[<?= InventorySlot::SLOT_PRIMARY->value ?>])
    }
    player.getObjectByName('hand').add(handItem)
</script>
</body>
</html>
