import * as Enum from "./Enums.js";
import {ModelRepository} from "./ModelRepository.js";

export class World {
    #scene;
    #camera;
    #renderer;
    #soundListener;
    #audioLoader;
    #modelRepository;
    #dropItems = [];
    #decals = [];

    constructor() {
        THREE.Cache.enabled = true
        this.#audioLoader = new THREE.AudioLoader()
        this.#modelRepository = new ModelRepository()
    }

    init(map, setting) {
        const scene = new THREE.Scene()
        scene.background = new THREE.Color(0xdadada)

        const promises = []
        promises.push(this.#modelRepository.loadAll())
        promises.push(this.#modelRepository.loadMap(map).then((model) => scene.add(model)))

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
        const bomb = this.#modelRepository.getBomb()
        this.#scene.add(bomb)
        bomb.rotation.set(0, 0, 0)
        bomb.position.set(position.x, position.y, -position.z)
        bomb.visible = true
    }

    getModelForItem(item) {
        return this.#modelRepository.getModelForItem(item)
    }

    itemDrop(position, item) {
        const dropItem = this.#modelRepository.getModelForItem(item)
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
        const bomb = this.#modelRepository.getBomb()
        bomb.visible = false
    }

    #createPlayer(colorIndex, isOpponent) { // fixme: create glb player models and remove
        const color = new THREE.Color(Enum.Color[colorIndex])
        const headMaterial = new THREE.MeshPhongMaterial({
            map: new THREE.TextureLoader().load(
                './resources/face.png'
            )
        })

        const bodyColor = isOpponent ? 0x993d00 : 0x75b359
        const player = this.#modelRepository.getPlayer().clone()
        player.getObjectByName('head').children[0].material = headMaterial
        player.getObjectByName('body').children[0].material = new THREE.MeshPhongMaterial({color: bodyColor})
        player.getObjectByName('legs').children.forEach((mesh) => mesh.material.color = color)
        player.rotation.reorder("YXZ")
        return player
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
