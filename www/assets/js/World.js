import * as Enum from "./Enums.js";

export class World {
    #scene;
    #camera;
    #renderer;
    #soundListener;
    #playerModel;
    #objectLoader;
    #audioLoader;
    #gltfLoader;
    #dropItems = [];
    #decals = [];
    #models = {
        bomb: null,
        knife: null,
        pistol: null,
        ak: null,
        m4: null,
    };

    constructor() {
        THREE.Cache.enabled = true
        this.#objectLoader = new THREE.ObjectLoader()
        this.#audioLoader = new THREE.AudioLoader()
        this.#gltfLoader = new THREE.GLTFLoader()
    }

    #loadMap(scene, map) {
        // TODO convert maps to glb
        return new Promise(resolve => {
            const promises = []
            promises.push(this.#loadJSON(`./resources/map/${map}.json`).then((data) => scene.add(data)))
            promises.push(
                this.#loadJSON(`./resources/map/${map}-extra.json`).then((data) => {
                    // JSON do not support light.target https://github.com/mrdoob/three.js/issues/9508
                    const lightTarget = data.getObjectByName('light-target')
                    data.traverse(function (object) {
                        if (object.type !== 'SpotLight') {
                            return
                        }

                        object.target = lightTarget
                    })
                    scene.add(data)
                })
            )
            Promise.all(promises).then(resolve)
        })
    }

    #loadJSON(url) {
        return new Promise(resolve => {
            this.#objectLoader.load(url, resolve)
        });
    }

    #loadModel(url) {
        return new Promise(resolve => {
            this.#gltfLoader.load(url, resolve)
        });
    }

    init(map, setting) {
        const scene = new THREE.Scene()
        scene.background = new THREE.Color(0xdadada)

        const promises = []
        promises.push(this.#loadJSON('./resources/model/player.json').then((model) => this.#playerModel = model))
        promises.push(this.#loadModel('./resources/model/bomb.glb').then((model) => {
            this.#models.bomb = model.scene
            this.#models.bomb.scale.set(.3, .3, .3)
        }))
        promises.push(this.#loadModel('./resources/model/knife.glb').then((model) => {
            this.#models.knife = model.scene
            this.#models.knife.scale.set(-400, 400, 400)
        }))
        promises.push(this.#loadModel('./resources/model/pistol.glb').then((model) => {
            this.#models.pistol = model.scene
            this.#models.pistol.scale.set(10, 10, 10)
        }))
        promises.push(this.#loadModel('./resources/model/ak.glb').then((model) => {
            this.#models.ak = model.scene
            this.#models.ak.scale.set(10, 10, 10)
        }))
        promises.push(this.#loadModel('./resources/model/m4.glb').then((model) => {
            this.#models.m4 = model.scene
            this.#models.m4.scale.set(10, 10, 10)
        }))
        promises.push(this.#loadMap(scene, map))

        const camera = new THREE.PerspectiveCamera(setting.getFieldOfView(), window.innerWidth / window.innerHeight, 1, 4999)
        camera.rotation.reorder("YXZ")
        const listener = new THREE.AudioListener()
        camera.add(listener)
        this.#soundListener = listener

        const glParameters = {}
        if (!setting.shouldPreferPerformance()) {
            glParameters.antialias = true
        }
        const renderer = new THREE.WebGLRenderer(glParameters);
        //renderer.outputEncoding = THREE.sRGBEncoding;
        renderer.setSize(window.innerWidth, window.innerHeight);
        if (!setting.shouldPreferPerformance()) {
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            renderer.setPixelRatio(window.devicePixelRatio);
        }

        window.addEventListener('resize', function () {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        this.#scene = scene
        this.#camera = camera
        this.#renderer = renderer

        return Promise.all(promises).then(() => renderer.domElement)
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

    spawnPlayer(colorIndex, isOpponent) {
        const newPlayer = this.#createPlayer(colorIndex, isOpponent)
        this.#scene.add(newPlayer)

        return newPlayer
    }

    spawnBomb(position) {
        this.#scene.add(this.#models.bomb)
        this.#models.bomb.rotation.set(0, 0, 0)
        this.#models.bomb.position.set(position.x, position.y, -position.z)
        this.#models.bomb.visible = true
    }

    getModelForItem(item) {
        if (item.slot === Enum.InventorySlot.SLOT_PRIMARY) {
            return item.id === Enum.ItemId.RifleM4A4 ? this.#models.m4.clone() : this.#models.ak.clone()
        }

        if (item.slot === Enum.InventorySlot.SLOT_SECONDARY) {
            return this.#models.pistol.clone()
        }

        if (item.slot === Enum.InventorySlot.SLOT_KNIFE) {
            return this.#models.knife.clone()
        }

        if (item.slot === Enum.InventorySlot.SLOT_BOMB) {
            return this.#models.bomb
        }

        console.warn("No model for", item)
        return new THREE.Mesh(new THREE.SphereGeometry(10), new THREE.MeshBasicMaterial({color: 0xFF0000}))
    }

    itemDrop(position, item) {
        const dropItem = this.getModelForItem(item)
        dropItem.position.set(position.x, position.y, -position.z)
        dropItem.visible = true
        dropItem.userData.itemId = item.id
        this.#scene.add(dropItem)

        if (item.id !== Enum.ItemId.Bomb) {
            this.#dropItems.push(dropItem)
        }
    }

    itemPickup(position, item) {
        if (item.id === Enum.ItemId.Bomb) {
            this.#removeBomb()
            return
        }

        for (let i = 0; i < this.#dropItems.length; i++) {
            const dropItem = this.#dropItems[i]
            if (!dropItem || dropItem.userData.itemId !== item.id) {
                continue
            }

            if (dropItem.position.x === position.x && dropItem.position.y === position.y && dropItem.position.z === -position.z) {
                this.destroyObject(dropItem)
                delete this.#dropItems[i]
                return
            }
        }
    }

    bulletWallHit(position, surface, radius) {
        const hit = new THREE.Mesh(
            new THREE.CylinderGeometry(radius, radius, .4, 8, 1),
            new THREE.MeshBasicMaterial({color: new THREE.Color(`hsl(23, 24%, ${Math.random() * 18}%, 1)`)})
        )
        hit.rotateOnWorldAxis(new THREE.Vector3(0, 1, 0), Math.random() * 6.28)
        if (surface.plane === 'zy') {
            hit.rotateOnWorldAxis(new THREE.Vector3(0, 0, 1), degreeToRadian(90))
        } else if (surface.plane === 'xy') {
            hit.rotateOnWorldAxis(new THREE.Vector3(1, 0, 0), degreeToRadian(90))
        }
        hit.position.set(position.x + 0.1, position.y + 0.1, -position.z + 0.1)
        this.#scene.add(hit)
        this.#decals.push(hit)
    }

    clearDecals() {
        this.#decals.forEach((item) => this.destroyObject(item))
    }

    reset() {
        this.#removeBomb()
        this.clearDecals()
        this.#dropItems.forEach((item) => this.destroyObject(item))
        this.#dropItems = []
    }

    destroyObject(object) {
        object.clear()
        object.removeFromParent()
        object.geometry && object.geometry.dispose()
        object.material && object.material.dispose()
        object = null
    }

    #removeBomb() {
        this.#models.bomb.visible = false
    }

    #createPlayer(colorIndex, isOpponent) {
        const color = new THREE.Color(Enum.Color[colorIndex])
        const headMaterial = new THREE.MeshPhongMaterial({
            map: new THREE.TextureLoader().load(
                './resources/face.png'
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

    playSound(soundName, position, inPlayerHead, refDistance = 1) {
        const sound = new THREE.PositionalAudio(this.#soundListener)
        const audioSource = new THREE.Object3D()
        audioSource.add(sound)

        if (inPlayerHead) {
            audioSource.position.setY(-20)
            this.#camera.add(audioSource)
        } else {
            audioSource.position.set(position.x, position.y, -position.z)
            this.#scene.add(audioSource)
        }

        this.#audioLoader.load('./resources/sound/' + soundName, function (buffer) {
            sound.setBuffer(buffer)
            sound.setRefDistance(refDistance)
            sound.setVolume(30)
            sound.setLoop(false)
            sound.play()
            sound.source.addEventListener('ended', function () {
                audioSource.clear()
                audioSource.removeFromParent()
            })
        });
    }

    render() {
        this.#renderer.render(this.#scene, this.#camera);
    }

    getCamera() {
        return this.#camera;
    }

}
