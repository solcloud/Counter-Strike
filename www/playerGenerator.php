<?php

use cs\Core\Game;
use cs\Core\Player;
use cs\Core\PlayerCollider;
use cs\Core\Point;
use cs\Core\Setting;
use cs\Enum\Color;
use cs\Enum\HitBoxType;
use cs\HitGeometry\SphereGroupHitBox;
use cs\Map\TestMap;

require __DIR__ . '/../vendor/autoload.php';

////////
$game = new Game();
$game->loadMap(new TestMap());
$player = new Player(0, Color::GREEN, true);
$player->setWorld($game->getWorld());
$player->setPosition(new Point());
$collider = new PlayerCollider($player);
////////

if (isset($_GET['crouch'])) {
    // TODO crouch, move animation
    $player->crouch();
    for ($tick = 0; $tick <= Setting::tickCountCrouch(); $tick++) {
        $player->onTick($tick);
    }
}

$playerState = $player->serialize();
$playerParts = [];
foreach ($collider->getHitBoxes() as $box) {
    $geometry = $box->getGeometry();
    if ($geometry instanceof SphereGroupHitBox) {
        foreach ($geometry->getParts() as $part) {
            $playerParts[$box->getType()->value][] = [
                "center" => $part->getRelativeCenter()->toArray(),
                "radius" => $part->radius,
            ];
        }
        continue;
    }

    throw new Exception("Unknown geometry '" . get_class($geometry) . "' given");
}

?>
<!Doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Player model Generator</title>
    <script src="./assets/threejs/three.js"></script>
    <script src="./assets/js/utils.js"></script>
    <script src="./assets/threejs/orbit-control.js"></script>
</head>
<body style="margin:0">
<div style="position:absolute">
    <textarea>Generating...</textarea>
</div>
<script>
    let camera, scene, renderer, controls, belt, hand;
    const materialDefault = new THREE.MeshBasicMaterial({color: 0x664b17})
    const materialArm = new THREE.MeshPhongMaterial({color: 0x614a09})
    const materialLeg = new THREE.MeshPhongMaterial({color: 0x124a13})
    const materialBody = new THREE.MeshPhongMaterial({color: 0xFF6600})

    const playerState = JSON.parse('<?= json_encode($playerState) ?>');
    const headHeight = playerState.heightSight;
    const bodyHeight = playerState.heightBody
    const modelHeight = playerState.height

    function init() {
        scene = new THREE.Scene();
        scene.background = new THREE.Color(0xdadada);
        camera = new THREE.PerspectiveCamera(70, window.innerWidth / window.innerHeight, 0.01, 9999);
        camera.position.y = 180
        camera.position.z = -140

        renderer = new THREE.WebGLRenderer({antialias: true});
        renderer.setSize(window.innerWidth, window.innerHeight);
        document.body.appendChild(renderer.domElement);

        controls = new THREE.OrbitControls(camera, renderer.domElement);
        const gridHelper = new THREE.GridHelper(500, 50);
        gridHelper.rotateX(THREE.MathUtils.degToRad(90))
        scene.add(gridHelper);
        scene.add(new THREE.GridHelper(500, 1));
        controls.update()
        camera.lookAt(0, 100, 1)

        const d1 = new THREE.DirectionalLight(0xffeac2, 0.6);
        const a1 = new THREE.AmbientLight(0xDADADA, .8)
        scene.add(d1, a1);
    }

    function createSphere(sphere, material) {
        const mesh = new THREE.Mesh(new THREE.SphereGeometry(sphere.radius), material || materialDefault)
        mesh.castShadow = true
        mesh.receiveShadow = true
        mesh.position.set(sphere.center.x, sphere.center.y, -1 * sphere.center.z)
        return mesh
    }

    function object() {
        const json = '<?= json_encode($playerParts, JSON_THROW_ON_ERROR) ?>';
        const data = JSON.parse(json);
        belt = new THREE.Group()
        belt.name = 'belt'
        belt.position.y = -40
        const arms = new THREE.Group()
        arms.name = 'arms'
        const legs = new THREE.Group()
        legs.name = 'legs'
        const body = new THREE.Group()
        body.name = 'body'
        body.userData.height = bodyHeight
        body.position.y = bodyHeight
        const head = new THREE.Group()
        head.name = 'head'
        head.position.y = headHeight
        head.rotateY(THREE.MathUtils.degToRad(90));
        const player = new THREE.Group()
        player.name = 'player'

        data[<?= HitBoxType::HEAD->value ?>].forEach(function (sphereData) {
            head.add(createSphere(sphereData))
        })
        data[<?= HitBoxType::STOMACH->value ?>].forEach(function (sphereData) {
            body.add(createSphere(sphereData, materialBody))
        })
        data[<?= HitBoxType::CHEST->value ?>].forEach(function (sphereData) {
            arms.add(createSphere(sphereData, materialArm))
        })
        data[<?= HitBoxType::LEG->value ?>].forEach(function (sphereData) {
            legs.add(createSphere(sphereData, materialLeg))
        })

        hand = new THREE.Group()
        hand.name = 'hand'
        hand.position.x = 22
        hand.position.y = -12
        hand.position.z = -30
        hand.rotateX(degreeToRadian(90))
        hand.rotateZ(degreeToRadian(-115))
        arms.add(hand)

        const slot0 = new THREE.Group()
        slot0.name = 'slot-0'
        slot0.position.x = -36
        slot0.position.y = -8
        slot0.position.z = -2
        slot0.rotateX(degreeToRadian(-10))
        slot0.rotateZ(degreeToRadian(100))
        const slot1 = new THREE.Group()
        slot1.name = 'slot-1'
        slot1.position.x = 14
        slot1.position.y = 22
        slot1.position.z = 35
        slot1.rotateX(degreeToRadian(-110))
        slot1.rotateY(degreeToRadian(90))
        const slot2 = new THREE.Group()
        slot2.name = 'slot-2'
        slot2.position.x = 36
        slot2.position.y = -6
        slot2.position.z = -6
        slot2.rotateX(degreeToRadian(-20))
        slot2.rotateZ(degreeToRadian(-100))
        const slot3 = new THREE.Group()
        slot3.name = 'slot-3'
        slot3.position.x = -18
        slot3.position.y = 35
        slot3.position.z = 14
        slot3.rotateX(degreeToRadian(70))
        slot3.rotateY(degreeToRadian(190))
        belt.add(slot0, slot1, slot2, slot3)

        body.add(arms, belt)
        player.add(head, body, legs)
        scene.add(player)
        renderer.render(scene, camera);
        document.querySelector('textarea').innerText = JSON.stringify(player.toJSON())

        head.children[0].material = new THREE.MeshBasicMaterial({map: new THREE.TextureLoader().load('./resources/face.png')})
    }

    function extra() {
        // Bounding box
        const bbRadius = <?= $player->getBoundingRadius() ?>;
        const bbMaterial = new THREE.MeshBasicMaterial({color: 0x000aa0, wireframe: true, transparent: true, opacity: 0.1})
        const bb = new THREE.Mesh(new THREE.CylinderGeometry(bbRadius, bbRadius, modelHeight, 16), bbMaterial);
        bb.translateY(bb.geometry.parameters.height / 2)
        scene.add(bb)

        belt.children.forEach(function (slot) {
            let slotItem = new THREE.Mesh(new THREE.CylinderGeometry(8, 8, 30, 8), bbMaterial)
            slotItem.rotateZ(degreeToRadian(90))
            slot.add(slotItem)
        })
        let handItem = new THREE.Mesh(new THREE.CylinderGeometry(8, 8, 30, 8), bbMaterial)
        handItem.rotateZ(degreeToRadian(90))
        hand.add(handItem)
    }

    function animate() {
        renderer.render(scene, camera);
        requestAnimationFrame(animate);
    }

    init()
    object()
    extra()
    animate()
</script>
</body>
</html>
