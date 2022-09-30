import * as Enum from "./Enums.js";

export class World {
    #scene;
    #camera;
    #renderer;
    #playerModel;
    #objectLoader;

    constructor() {
        this.#objectLoader = new THREE.ObjectLoader()
    }

    async #loadMap(scene, map) {
        const mapData = await this.#loadJSON(`/resources/map/${map}.json`)
        scene.add(mapData)

        const mapDataExtra = await this.#loadJSON(`/resources/map/${map}-extra.json`)
        // JSON do not support light.target https://github.com/mrdoob/three.js/issues/9508
        const lightTarget = mapDataExtra.getObjectByName('light-target')
        mapDataExtra.traverse(function (object) {
            if (object.type !== 'SpotLight') {
                return
            }

            object.target = lightTarget
        })
        scene.add(mapDataExtra)
    }

    #loadJSON(url) {
        const loader = this.#objectLoader;
        return new Promise(resolve => {
            loader.load(url, resolve);
        });
    }

    async init(map, fov = 70) {
        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0xdadada);

        this.#playerModel = await this.#loadJSON('/resources/model/player.json')
        await this.#loadMap(scene, map)

        const camera = new THREE.PerspectiveCamera(fov, window.innerWidth / window.innerHeight, 0.1, 4999);
        camera.rotation.reorder("YXZ")

        const renderer = new THREE.WebGLRenderer({
            antialias: true
        });
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;
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

        return renderer.domElement
    }

    createPlayerMe() {
        const me = new THREE.Object3D()
        me.visible = false
        const head = new THREE.Object3D()
        head.name = "head"
        head.add(this.getCamera())
        me.add(head)

        // TODO spawn into more interesting warmup place, like some credits area, walls with actual map of map, etc.
        //      if client also implement moving and shooting warmup aim arena would be cool (some place with random spawning targets to warmup aim)
        me.position.y = 9999
        me.position.z = -9999

        this.#scene.add(me)
        return me
    }

    spawnPlayer(id, colorIndex, isOpponent) {
        const newPlayer = this.#createPlayer(colorIndex, isOpponent)
        this.#scene.add(newPlayer)

        return newPlayer
    }

    #createPlayer(colorIndex, isOpponent) {
        const color = new THREE.Color(Enum.Color[colorIndex])
        const headMaterial = new THREE.MeshPhongMaterial({
            map: new THREE.TextureLoader().load(
                '/resources/face.png'
            )
        })
        const bodyTexture = new THREE.Texture(
            this.#generateTexture(
                Enum.ColorNames[colorIndex],
                '#' + color.getHexString(),
                isOpponent ? '#FF6600' : '#75b359',
                isOpponent ? '#9b190c' : '#399b0c'
            )
        );
        bodyTexture.needsUpdate = true;

        const player = this.#playerModel.clone()
        player.getObjectByName('head').children[0].material = headMaterial
        player.getObjectByName('body').children[0].material = new THREE.MeshPhongMaterial({map: bodyTexture})
        player.rotation.reorder("YXZ")
        return player
    }

    #generateTexture(playerText, playerColor, colorStart, colorEnd, resolution = 200) {
        const canvas = document.createElement("canvas");
        canvas.width = resolution;
        canvas.height = resolution;

        const ctx = canvas.getContext("2d");
        ctx.rect(0, 0, resolution, resolution);
        const gradient = ctx.createLinearGradient(0, 0, resolution, resolution);
        gradient.addColorStop(0, colorStart);
        gradient.addColorStop(1, colorEnd);
        ctx.fillStyle = gradient;
        ctx.fill();

        ctx.fillStyle = playerColor
        ctx.font = '60px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(playerText, resolution / 2, resolution / 2)

        return canvas;
    }

    render() {
        this.#renderer.render(this.#scene, this.#camera);
    }

    updatePlayerModel(player, data) {
        const body = player.getObjectByName('body')
        if (body.position.y !== data.heightBody) { // update body height position if changed
            // TODO probably keyframe time based animation from userData like body.position = body.userData.animation[data.playerAnimationId]
            body.position.y = data.heightBody
        }
    }

    getCamera() {
        return this.#camera;
    }

}
