<?php

require __DIR__ . '/../vendor/autoload.php';

use cs\Enum\SoundType;
use cs\Event\EventList;
use cs\Event\KillEvent;
use cs\Event\SoundEvent;
use cs\Event\ThrowEvent;

////////
$data = @file_get_contents('/tmp/cs.demo.json');
////////

if ($data === false) {
    echo "No data found";
    return;
}
$data = json_decode($data, true);
$frameIdStart = null;
$frameIdEnd = null;
?>
<!Doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Demo player</title>
    <style>
        body {
            margin: 0;
            font-size: 14px;
            overflow: hidden;
        }

        .container {
            position: absolute;
            background-color: rgba(196, 196, 196, 0.75);
            width: 100%;
            padding-top: 12px;
            text-align: center;
        }

        .container button {
            font-size: 1.2rem;
            padding: 4px 8px;
            text-align: center;
        }

        .container .progress {
            padding: 2px 8px;
        }

        .container .progress * {
            display: block;
            width: 100%;
        }
    </style>
    <script src="./assets/threejs/three.js"></script>
    <script src="./assets/js/utils.js"></script>
</head>
<body>
<script>
    const frames = []
    <?php foreach ($data['states'] as $frameId => $state) : ?>
    <?php
    if ($frameIdStart === null) {
        $frameIdStart = $frameId;
    }
    $frameIdEnd = $frameId;
    ?>
    frames[<?= $frameId ?>] = JSON.parse('<?= addcslashes(json_encode($state, JSON_THROW_ON_ERROR), "'\\") ?>');
    <?php endforeach; ?>
</script>
<div class="container">
    <div>
        <i>Frame start state for frame:</i>
        <strong><span id="frameId">0</span></strong> / <?= $frameIdEnd ?>
        <button onclick="driver.goToFrame(<?= $frameIdStart ?>)">Go to Start</button>
        <button onclick="driver.previousFrame()">Prev</button>
        <button onclick="driver.playPause()">Play/Pause</button>
        <button onclick="driver.nextFrame()">Next</button>
        <button onclick="driver.goToFrame(<?= $frameIdEnd ?>)">Go to End</button>
        &nbsp;
        Speed: <span id="speedLimit"></span>ms</span>
        <button onclick="driver.changeSpeed(10)">Slower</button>
        <button onclick="driver.changeSpeed()">Default</button>
        <button onclick="driver.changeSpeed(-10)">Faster</button>
    </div>
    <div class="progress">
        <input type="range" id="progress" min="0" value="0" max="<?= $frameIdEnd ?>">
    </div>
</div>
<script>

    const renderer = {
        gl: null,
        scene: null,
        camera: null,
        initialize: function (floors, walls) {
            this.scene = new THREE.Scene();
            this.scene.background = new THREE.Color(0xdddddd);
            this.camera = new THREE.PerspectiveCamera(70, window.innerWidth / window.innerHeight, 0.01, 99999);
            this.camera.position.y = 1500

            this.gl = new THREE.WebGLRenderer({antialias: true});
            this.gl.setSize(window.innerWidth, window.innerHeight);
            document.body.appendChild(this.gl.domElement);

            this.scene.add(new THREE.Mesh(new THREE.SphereGeometry(10), new THREE.MeshBasicMaterial({
                color: 0x000000,
                transparent: true,
                opacity: 0.8
            })))
            const lastObject = this.fillWorld(floors, walls)
            this.camera.lookAt(lastObject)
            new THREE.OrbitControls(this.camera, this.gl.domElement);
        },
        fillWorld: function (floors, walls) {
            let lastMesh
            const scene = this.scene
            const materialFloor = new THREE.MeshBasicMaterial({
                color: 0xFF0000,
                wireframe: true,
                transparent: true,
                opacity: 0.4,
                side: THREE.DoubleSide
            })
            const materialWall = new THREE.MeshBasicMaterial({
                color: 0x0000FF,
                wireframe: true,
                transparent: true,
                opacity: 0.3,
                side: THREE.DoubleSide
            })
            floors.forEach(function (floor) {
                const mesh = new THREE.Mesh(
                    new THREE.PlaneGeometry(floor.e.x - floor.s.x, floor.e.z - floor.s.z, 4, 4),
                    materialFloor
                )
                mesh.rotateX(degreeToRadian(-90))
                mesh.position.set(floor.s.x, floor.s.y, -floor.s.z)
                mesh.translateX(mesh.geometry.parameters.width / 2)
                mesh.translateY(mesh.geometry.parameters.height / 2)
                scene.add(mesh)
                lastMesh = mesh
            })
            walls.forEach(function (wall) {
                const width = wall.p === 'xy' ? wall.e.x - wall.s.x : wall.e.z - wall.s.z
                const mesh = new THREE.Mesh(
                    new THREE.PlaneGeometry(width, wall.e.y - wall.s.y, 4, 2),
                    materialWall
                )
                if (wall.p === 'xy') {
                    // no rotation needed
                } else if (wall.p === 'zy') {
                    mesh.rotateY(degreeToRadian(90))
                } else {
                    throw new Error("Bad wall axis: " + wall.p)
                }
                mesh.position.set(wall.s.x, wall.s.y, -wall.s.z)
                mesh.translateX(mesh.geometry.parameters.width / 2)
                mesh.translateY(mesh.geometry.parameters.height / 2)
                scene.add(mesh)
                lastMesh = mesh
            })

            return lastMesh
        },
        animate: function () {
            this.gl.render(this.scene, this.camera);
        },
        players: [],
        spawnPlayer: function (id, colorIndex, isAttacker) {
            const color = isAttacker ? new THREE.Color(0xff9145) : new THREE.Color(0x43b4fd)
            const radiusHead = <?= $data['player']['head'] ?>;

            const sight = new THREE.Mesh(
                new THREE.CylinderGeometry(1, 1, 150, 4),
                new THREE.MeshBasicMaterial({color})
            );
            sight.translateY(sight.geometry.parameters.height / 2)

            const head = new THREE.Mesh(
                new THREE.SphereGeometry(radiusHead),
                new THREE.MeshBasicMaterial({color})
            );
            head.name = "head"
            head.rotation.reorder("YXZ")
            head.add(sight)

            const radiusBody = <?= $data['player']['body'] ?>;
            const heightBody = <?= $data['player']['height'] ?>;
            const body = new THREE.Mesh(
                new THREE.CylinderGeometry(radiusBody, radiusBody, heightBody, 16),
                new THREE.MeshBasicMaterial({color, transparent: true, opacity: .4})
            );
            body.translateY(body.geometry.parameters.height / 2)
            body.name = "body"

            const body0 = new THREE.Mesh(
                new THREE.CylinderGeometry(1, 1, heightBody, 16),
                new THREE.MeshBasicMaterial({color, opacity: .2})
            );
            body0.translateY(body0.geometry.parameters.height / 2)

            const boundingRadius = new THREE.Mesh(
                new THREE.CircleGeometry(radiusBody, 16),
                new THREE.MeshBasicMaterial({color, side: THREE.DoubleSide})
            );
            boundingRadius.rotateX(degreeToRadian(90))

            const player = new THREE.Object3D();
            player.rotation.reorder("YXZ")
            player.add(head, body, body0, boundingRadius)
            this.scene.add(player)
            return player
        },
        showTrajectory: false,
        throwables: [],
        flammable: [],
        createBox: function (width, height, depth, color) {
            let box = new THREE.Mesh(
                new THREE.BoxGeometry(width, height, depth),
                new THREE.MeshBasicMaterial({color: color}),
            )
            this.scene.add(box)
            return box
        },
        createBall: function (radius) {
            const ball = new THREE.Mesh(new THREE.SphereGeometry(radius), new THREE.MeshBasicMaterial({
                color: 0xAA6611,
                transparent: true,
                opacity: 0.9
            }))
            this.scene.add(ball)
            return ball
        },
        renderFrame: function (frameId, state) {
            const self = this
            state.events.forEach(function (event) {
                if (event.code === <?= EventList::map[KillEvent::class] ?>) {
                    console.log("KillEvent - frame: " + frameId, event.data)
                }
                if (event.code === <?= EventList::map[ThrowEvent::class] ?>) {
                    const radius = event.data.radius
                    if (self.showTrajectory) {
                        self.throwables[event.data.id] = radius
                        return
                    }

                    if (self.throwables[event.data.id] === undefined) {
                        const ball = self.createBall(radius)
                        ball.position.set(event.data.position.x, event.data.position.y, -event.data.position.z)
                        self.throwables[event.data.id] = ball
                    }
                }
                if (event.code === <?= EventList::map[SoundEvent::class] ?>) {
                    if ([<?= SoundType::GRENADE_LAND->value ?>, <?= SoundType::GRENADE_BOUNCE->value ?>, <?= SoundType::GRENADE_AIR->value ?>].includes(event.data.type)) {
                        const ball = self.showTrajectory ? self.createBall(self.throwables[event.data.extra.id]) : self.throwables[event.data.extra.id]
                        ball.visible = true
                        ball.position.set(event.data.position.x, event.data.position.y, -event.data.position.z)
                        if (!self.showTrajectory && event.data.type === <?= SoundType::GRENADE_LAND->value ?>) {
                            setTimeout(() => ball.visible = false, 2000)
                        }
                    }
                    if (event.data.type === <?= SoundType::FLAME_SPAWN->value ?>) {
                        const color = new THREE.Color(`hsl(53, 100%, ${Math.random() * 70 + 20}%, 1)`)
                        let flame = self.createBox(event.data.extra.size, event.data.extra.height, event.data.extra.size, color)
                        if (self.flammable[event.data.extra.fire] === undefined) {
                            self.flammable[event.data.extra.fire] = {}
                        }
                        self.flammable[event.data.extra.fire][`${event.data.position.x}-${event.data.position.y}-${event.data.position.z}`] = flame
                        flame.position.set(event.data.position.x, event.data.position.y + (event.data.extra.height /  2), -event.data.position.z)
                    }
                    if (event.data.type === <?= SoundType::FLAME_EXTINGUISH->value ?>) {
                        const flame = self.flammable[event.data.extra.fire][`${event.data.position.x}-${event.data.position.y}-${event.data.position.z}`]
                        flame.visible = false
                    }
                }
            })
            state.players.forEach(function (playerState) {
                let player = self.players[playerState.id]
                if (player === undefined) {
                    player = self.spawnPlayer(playerState.id, playerState.color, playerState.isAttacker)
                    self.players[playerState.id] = player
                }

                console.debug(frameId, playerState.id, playerState.position, playerState.health)
                player.getObjectByName('head').position.y = playerState.sight
                player.getObjectByName('head').rotation.x = degreeToRadian(playerState.look.vertical - 90)
                player.position.set(playerState.position.x, playerState.position.y, -1 * (playerState.position.z))
                player.rotation.y = serverHorizontalRotationToThreeRadian(playerState.look.horizontal)
            })
        }
    }

    function createDriver(renderer) {

        const driver = {
            frameId: <?= $frameIdStart ?>,
            defaultAnimationSpeedMs: 100,
            getFrameId: function () {
                return this.frameId;
            }
        };

        /////
        const progress = document.getElementById('progress');
        const frameIdCaption = document.getElementById('frameId');
        const speedLimitCaption = document.getElementById('speedLimit');
        let frameId = driver.getFrameId();
        let frameMaxId = <?= $frameIdEnd ?>;
        let animationSpeed = driver.defaultAnimationSpeedMs;
        let animationId = null;
        speedLimitCaption.innerText = animationSpeed;
        /////

        driver.goToFrame = function (goToFrameId) {
            if (goToFrameId === undefined || goToFrameId < 0 || goToFrameId > frameMaxId) {
                driver.pause();
                return false;
            }

            frameId = goToFrameId;
            driver.frameId = frameId;
            frameIdCaption.innerText = frameId
            progress.value = frameId
            renderer.renderFrame(frameId, frames[frameId])
        }

        driver.previousFrame = function () {
            driver.goToFrame(frameId - 1);
        }

        driver.nextFrame = function () {
            driver.goToFrame(frameId + 1);
        }

        driver.playPause = function () {
            if (animationId) {
                driver.pause();
            } else {
                driver.play();
            }
        }

        driver.play = function () {
            if (animationId) {
                return;
            }

            animationId = setInterval(function () {
                driver.nextFrame();
            }, animationSpeed);
        }

        driver.pause = function () {
            clearInterval(animationId);
            animationId = null;
        }

        driver.changeSpeed = function (speedDelta) {
            if (speedDelta === undefined) {
                animationSpeed = driver.defaultAnimationSpeedMs;
            } else {
                animationSpeed += speedDelta;
            }
            animationSpeed = Math.max(1, animationSpeed);

            speedLimitCaption.innerText = animationSpeed;
            driver.pause();
            driver.play();
        }

        return driver;
    }

    renderer.initialize(JSON.parse('<?= json_encode($data['floors']) ?>'), JSON.parse('<?= json_encode($data['walls']) ?>'))
    window.driver = createDriver(renderer)
    document.getElementById('progress').addEventListener('input', function () {
        const value = parseInt(this.value)
        if (driver.getFrameId() === value) {
            return
        }
        driver.goToFrame(value)
    })
    driver.goToFrame(0)

    function animate() {
        renderer.animate();
        requestAnimationFrame(animate);
    }

    animate()

</script>
</body>
</html>
