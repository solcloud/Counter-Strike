import * as Enum from "./Enums.js";
import {ModelRepository} from "./ModelRepository.js";

export class World {
    #scene
    #camera
    #renderer
    #soundListener
    #audioLoader
    #modelRepository
    #dropItems = []
    #decals = []
    volume = 30

    constructor() {
        THREE.Cache.enabled = true
        THREE.ColorManagement.legacyMode = false

        this.#audioLoader = new THREE.AudioLoader()
        this.#modelRepository = new ModelRepository()
    }

    init(mapName, setting) {
        const scene = new THREE.Scene()
        scene.background = new THREE.Color(0xdadada)

        const promises = []
        promises.push(this.#modelRepository.loadAll())
        promises.push(this.#modelRepository.loadMap(mapName).then((model) => scene.add(model)))

        const camera = new THREE.PerspectiveCamera(setting.getFieldOfView(), window.innerWidth / window.innerHeight, 1, 4999)
        camera.rotation.reorder("YXZ")
        const listener = new THREE.AudioListener()
        const povItem = new THREE.Group()
        povItem.name = 'pov-item'
        povItem.scale.setScalar(.7)
        povItem.position.z = -12
        povItem.position.y = -14
        camera.add(listener, povItem)
        this.#soundListener = listener

        const glParameters = {
            powerPreference: 'high-performance',
        }
        if (!setting.shouldPreferPerformance()) {
            glParameters.antialias = true
        }
        const renderer = new THREE.WebGLRenderer(glParameters)
        renderer.outputEncoding = THREE.sRGBEncoding
        renderer.toneMapping = THREE.ACESFilmicToneMapping
        renderer.toneMappingExposure = setting.getExposure()
        renderer.physicallyCorrectLights = false
        renderer.setSize(window.innerWidth, window.innerHeight)
        if (!setting.shouldPreferPerformance()) {
            new THREE.RGBELoader().load('./resources/img/orlando_stadium_1k.hdr', function (texture) {
                texture.mapping = THREE.EquirectangularReflectionMapping
                texture.toneMapped = true
                scene.environment = texture
            })
            renderer.shadowMap.enabled = true
            renderer.shadowMap.type = THREE.PCFSoftShadowMap
            renderer.setPixelRatio(window.devicePixelRatio)
        }

        window.addEventListener('resize', function () {
            camera.aspect = window.innerWidth / window.innerHeight
            camera.updateProjectionMatrix()
            renderer.setSize(window.innerWidth, window.innerHeight)
        })

        this.#scene = scene
        this.#camera = camera
        this.#renderer = renderer
        this.volume = setting.getMasterVolume()

        const anisotropy = Math.min(setting.getAnisotropicFiltering(), renderer.capabilities.getMaxAnisotropy())
        return Promise.all(promises).then(() => {
            scene.traverse(function (object) {
                if (object.isMesh && object.material.map) {
                    object.material.map.anisotropy = anisotropy
                }
            })
            return renderer.domElement
        })
    }

    createPlayerMe() {
        const me = new THREE.Object3D()
        const sight = new THREE.Object3D()
        sight.name = 'sight'
        sight.add(this.getCamera())
        const figure = new THREE.Group()
        figure.name = 'figure'
        me.add(sight, figure)

        // FIXME spawn into more interesting warmup place, like some credits area, walls with actual map of map, etc.
        //      if client also implement moving and shooting warmup aim arena would be cool (some place with random spawning targets to warmup aim)
        me.position.y = 9999
        me.position.z = -9999

        this.#scene.add(me)
        return me
    }

    spawnPlayer(player, isOpponent) {
        const mesh = this.#modelRepository.getPlayer(player.getColorIndex(), isOpponent)
        const sight = new THREE.Object3D()
        sight.name = 'sight'
        mesh.add(sight)
        mesh.rotation.reorder("YXZ")
        this.#scene.add(mesh)

        player.set3DObject(mesh)
        player.setAnimations(this.#modelRepository.getPlayerAnimation())
    }

    spawnBomb(position) {
        const bomb = this.#modelRepository.getBomb()
        this.#scene.add(bomb)
        bomb.rotation.set(0, 0, 0)
        bomb.position.set(position.x, position.y, -position.z)
        bomb.visible = true
    }

    getModelForItem(item) {
        const model = this.#modelRepository.getModelForItem(item)
        model.name = `item-${item.id}`
        return model
    }

    itemDrop(position, item) {
        const dropItem = this.getModelForItem(item)
        dropItem.position.set(position.x, position.y, -position.z)
        dropItem.visible = true
        this.#scene.add(dropItem)

        if (item.id !== Enum.ItemId.Bomb) {
            this.#dropItems.push(dropItem)
        }
    }

    itemPickup(position, item, isSpectatorPickup) {
        if (item.id === Enum.ItemId.Bomb) {
            if (isSpectatorPickup) {
                const bomb = this.#modelRepository.getBomb()
                bomb.visible = false
            }
            return
        }

        for (let i = 0; i < this.#dropItems.length; i++) {
            const dropItem = this.#dropItems[i]
            if (!dropItem || dropItem.name !== `item-${item.id}`) {
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

    bulletPlayerHit(position, wasHeadshot) {
        const hit = this.#modelRepository.getPlayerHitMesh()
        hit.material.color.set(wasHeadshot ? 0xFF6600 : 0x5061A4)
        hit.position.set(position.x, position.y, -position.z)

        this.#scene.add(hit)
        setTimeout(() => this.destroyObject(hit), 100)
    }

    clearDecals() {
        this.#decals.forEach((item) => this.destroyObject(item))
        this.#decals = []
    }

    reset() {
        this.clearDecals()
        this.#dropItems.forEach((item) => this.destroyObject(item))
        this.#dropItems = []
        const bomb = this.#modelRepository.getBomb()
        if (bomb.parent && bomb.parent.type === 'Scene') {
            bomb.visible = false
        }
    }

    destroyObject(object) {
        object.clear()
        object.removeFromParent()
        object.geometry && object.geometry.dispose()
        object.material && object.material.dispose()
        object = null
    }

    playSound(soundName, position, inPlayerHead, refDistance = 1) {
        const sound = new THREE.PositionalAudio(this.#soundListener)
        sound.setVolume(this.volume)
        sound.setRefDistance(refDistance)
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
            sound.setLoop(false)
            sound.play()
            sound.source.addEventListener('ended', function () {
                audioSource.clear()
                audioSource.removeFromParent()
            })
        })
    }

    render() {
        this.#renderer.render(this.#scene, this.#camera)
    }

    getCamera() {
        return this.#camera
    }

}
