import * as Enum from "./Enums.js";

export class World {
    #scene;
    #camera;
    #map;
    #renderer;
    #playerModel;

    setPlayerModelAttributes(modelData) {
        this.#playerModel = modelData

        const scene = this.#scene
        const mapLoader = new THREE.ObjectLoader()
        mapLoader.load(`/resources/map/${this.#map}.json`, function (object) {
            object.scale.set(modelData.scaleX, modelData.scaleY, modelData.scaleZ)
            scene.add(object)
        })
    }

    init(map, fov = 70) {
        this.#map = map
        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0xdadada);

        const camera = new THREE.PerspectiveCamera(fov, window.innerWidth / window.innerHeight, 0.1, 4999);
        camera.rotation.reorder("YXZ")

        if (true) {
            const gridHelper = new THREE.GridHelper(9999, 999);
            scene.add(gridHelper);

            const wallHeight = 300
            const wallWidth = 800
            const wallXZero = new THREE.Mesh(new THREE.PlaneGeometry(wallWidth, wallHeight), new THREE.MeshBasicMaterial({
                color: 0xffaabb,
                side: THREE.DoubleSide
            }));
            wallXZero.translateY(wallXZero.geometry.parameters.height / 2)
            wallXZero.translateZ(-wallXZero.geometry.parameters.width / 2)
            wallXZero.rotateY(degreeToRadian(90))
            scene.add(wallXZero)

            const wallZZero = new THREE.Mesh(new THREE.PlaneGeometry(wallWidth, wallHeight), new THREE.MeshBasicMaterial({
                color: 0xF2FFa2,
                side: THREE.DoubleSide
            }));
            wallZZero.translateY(wallZZero.geometry.parameters.height / 2)
            wallZZero.rotateY(degreeToRadian(180))
            scene.add(wallZZero)
        }

        const renderer = new THREE.WebGLRenderer({
            canvas: document.getElementById('canvas'),
            antialias: true
        });
        renderer.setPixelRatio(window.devicePixelRatio);
        renderer.setSize(window.innerWidth, window.innerHeight);

        window.addEventListener('resize', function () {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();

            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        this.#scene = scene
        this.#camera = camera
        this.#renderer = renderer
    }

    spawnPlayer(id, colorIndex, isOpponent) {
        const newPlayer = this.#createPlayer(colorIndex, isOpponent)
        this.#scene.add(newPlayer)

        return newPlayer
    }

    #createPlayer(colorIndex, isOpponent) {
        const radiusHead = this.#playerModel.headRadius
        const sightHeight = this.#playerModel.sightHeight
        const radiusBody = this.#playerModel.bodyRadius
        const heightBody = this.#playerModel.bodyHeight

        const color = new THREE.Color(Enum.Color[colorIndex])
        const colorStart = isOpponent ? '#FF6600' : '#75b359'
        const colorEnd = isOpponent ? '#9b190c' : '#399b0c'

        const head = new THREE.Mesh(
            new THREE.SphereGeometry(radiusHead),
            new THREE.MeshBasicMaterial({
                map: new THREE.TextureLoader().load(
                    '/resources/face.png'
                )
            })
        );
        head.name = "head"
        head.rotation.y = degreeToRadian(90)
        head.position.y = sightHeight

        const bodyTexture = new THREE.Texture(
            this.#generateTexture(
                Enum.ColorNames[colorIndex],
                '#' + color.getHexString(),
                colorStart,
                colorEnd
            )
        );
        bodyTexture.needsUpdate = true;
        const body = new THREE.Mesh(
            new THREE.CylinderGeometry(radiusBody, radiusBody, heightBody, 32),
            new THREE.MeshBasicMaterial({
                map: bodyTexture
            })
        );
        body.translateY(body.geometry.parameters.height / 2)
        body.name = "body"

        const player = new THREE.Object3D();
        player.rotation.reorder("YXZ")
        player.add(head, body)
        return player
    }

    #generateTexture(playerText, playerColor, colorStart, colorEnd, resolution = 200) {
        const canvas = document.createElement("canvas");
        canvas.width = resolution;
        canvas.height = resolution;

        const ctx = canvas.getContext("2d");
        ctx.rect(0, 0, resolution, resolution);
        const gradient = ctx.createLinearGradient(0, 0, resolution, resolution);
        gradient.addColorStop(0, colorStart); // todo add more steps .5?
        gradient.addColorStop(1, colorEnd);
        ctx.fillStyle = gradient;
        ctx.fill();

        ctx.fillStyle = playerColor
        ctx.font = '90px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(playerText, resolution / 2, resolution / 2)

        return canvas;
    }

    render() {
        this.#renderer.render(this.#scene, this.#camera);
    }

    updatePlayerModel(player, data) {
        const body = player.getObjectByName('body')
        if (body.geometry.parameters.height !== data.heightBody) { // update body height if changed
            const oldParams = body.geometry.parameters
            let newGeometry = new THREE.CylinderGeometry(oldParams.radiusTop, oldParams.radiusBottom, data.heightBody, oldParams.radialSegments)
            body.geometry.dispose()
            body.geometry = newGeometry
            body.position.setY(newGeometry.parameters.height / 2)
        }
    }

    getCamera() {
        return this.#camera;
    }

}
