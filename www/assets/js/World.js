import * as THREE from 'three'
import * as Enum from "./Enums.js"
import {RGBELoader} from "three/addons/loaders/RGBELoader.js"
import {ModelRepository} from "./ModelRepository.js"
import {Utils} from "./Utils.js";

export class World {
    #scene
    #camera
    #renderer
    #soundListener
    #audioLoader
    #modelRepository
    #raycaster
    #decals = []
    #cache = {}
    volume = 30

    constructor() {
        THREE.Cache.enabled = true

        this.#audioLoader = new THREE.AudioLoader()
        this.#modelRepository = new ModelRepository()
        this.#raycaster = new THREE.Raycaster()
    }

    init(mapName, setting) {
        const scene = new THREE.Scene()
        scene.name = 'MainScene'
        scene.background = new THREE.Color(0xdadada)

        const promises = []
        promises.push(this.#modelRepository.loadAll())
        promises.push(this.#modelRepository.loadMap(mapName).then((model) => scene.add(model)))

        const camera = new THREE.PerspectiveCamera(setting.getFieldOfView(), window.innerWidth / window.innerHeight, 1, 19999)
        camera.rotation.reorder("YXZ")
        const listener = new THREE.AudioListener()
        const povItem = new THREE.Group()
        povItem.name = 'pov-item'
        povItem.scale.setScalar(.7)
        povItem.scale.x *= (setting.hasWeaponInRightHand() ? 1 : -1)
        povItem.position.x = (setting.hasWeaponInRightHand() ? 8 : -8)
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
        renderer.toneMapping = THREE.CineonToneMapping
        renderer.toneMappingExposure = setting.getExposure()
        renderer.setSize(window.innerWidth, window.innerHeight)
        if (!setting.shouldPreferPerformance()) {
            new RGBELoader().load('./resources/img/kloofendal_48d_partly_cloudy_puresky_1k.hdr', function (texture) {
                texture.mapping = THREE.EquirectangularReflectionMapping
                texture.toneMapped = true
                scene.background = texture
                scene.environment = texture
                scene.environmentIntensity = 0.4
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
        bomb.position.set(position.x, position.y, -position.z)
        bomb.visible = true
    }

    spawnGrenade(item, radius) {
        const grenade = new THREE.Group()
        const model = this.getModelForItem(item).getObjectByName('item')
        const bb = new THREE.Mesh(new THREE.SphereGeometry(radius, 32, 20), new THREE.MeshBasicMaterial())
        bb.name = 'collider'
        bb.visible = false
        grenade.add(model, bb)
        this.#scene.add(grenade)
        return grenade
    }

    getModelForItem(item) {
        const model = this.#modelRepository.getModelForItem(item)
        model.name = `item-${item.id}`
        return model
    }

    getScene() {
        return this.#scene
    }

    itemAttack(player, item, isSpectator) {
        const sparkFeedbackSlots = [Enum.InventorySlot.SLOT_PRIMARY, Enum.InventorySlot.SLOT_SECONDARY]
        if (sparkFeedbackSlots.includes(item.slot)) {
            let spark
            if (isSpectator) {
                spark = player.get3DObject().getObjectByName('pov-item')?.getObjectByName(`item-${item.id}`)?.getObjectByName('pov-spark')
            } else {
                spark = player.get3DObject().getObjectByName('hand')?.getObjectByName('spark')
            }
            if (spark) {
                const mesh = this.#modelRepository.getPlayerHitMesh()
                mesh.material = mesh.material.clone()
                mesh.material.color.set(0xe6ce8c)
                mesh.scale.setScalar((item.slot === Enum.InventorySlot.SLOT_PRIMARY ? 12 : 8) * (isSpectator ? 0.85 : 1))
                spark.add(mesh)
                setTimeout(() => this.destroyObject(mesh), isSpectator ? 40 : 70)
            }
        }
    }

    itemDropped(item) {
        const dropItem = this.getModelForItem(item)
        dropItem.visible = true
        this.#scene.add(dropItem)
        return dropItem
    }

    loadCache(index, loadCallback) {
        if (this.#cache[index] === undefined) {
            this.#cache[index] = loadCallback()
        }

        return this.#cache[index];
    }

    spawnFlame(size, height) {
        const coneDetail = Utils.randomInt(5, 7)
        const lightnessValue = Utils.randomInt(30, 80)
        const geometry = this.loadCache(`flame-geo-c-${coneDetail}`, () => new THREE.ConeGeometry(1, 1, coneDetail))
        const material = this.loadCache(`flame-mat-${lightnessValue}`, () => new THREE.MeshPhongMaterial({
            color: new THREE.Color(`hsl(53, 100%, ${lightnessValue}%, 1)`)
        }))

        let mesh = new THREE.Mesh(geometry, material)
        mesh.scale.set(size, height, size)
        mesh.castShadow = false
        mesh.receiveShadow = true

        this.#scene.add(mesh)
        return mesh
    }

    spawnSmoke(geometry) {
        const mesh = new THREE.Mesh(geometry, this.#modelRepository.getSmokeMaterial())
        this.#scene.add(mesh)
        return mesh
    }

    bulletWallHit(origin, hitPosition, item) {
        const rayHit = new THREE.Vector3(hitPosition.x, hitPosition.y, -hitPosition.z)
        const rayDirection = rayHit.clone().sub(new THREE.Vector3(origin.x, origin.y, -origin.z)).normalize()
        rayHit.addScaledVector(rayDirection, -50); // offset a bit back to give raycaster more space to match server-client geometry

        this.#raycaster.near = 1
        this.#raycaster.far = 100
        this.#raycaster.layers.set(Utils.LAYER_WORLD)
        this.#raycaster.set(rayHit, rayDirection)
        const intersects = this.#raycaster.intersectObject(this.getScene())
        if (intersects.length === 0) {
            return
        }

        const radius = (item.slot === Enum.InventorySlot.SLOT_PRIMARY ? 1.8 : (item.slot === Enum.InventorySlot.SLOT_SECONDARY ? 1.5 : 1.1))
        const hit = new THREE.Mesh(
            this.loadCache(`bulletHit-wc-${radius}`, () => new THREE.CylinderGeometry(radius, radius, 2, 8, 1)),
            new THREE.MeshBasicMaterial({color: new THREE.Color(`hsl(23, 24%, ${Math.random() * 18}%, 1)`)}),
        )
        hit.position.copy(rayHit)
        hit.lookAt(intersects[0].point)
        hit.rotateX(Utils.degreeToRadian(90))
        hit.rotateY(Math.random() * 6.28)
        hit.translateY(intersects[0].distance)

        this.#scene.add(hit)
        this.#decals.push(hit)
    }

    bulletPlayerHit(position, wasHeadshot) {
        const hit = this.#modelRepository.getPlayerHitMesh()
        hit.material = hit.material.clone()
        hit.material.color.set(wasHeadshot ? 0x11784b : 0x5061A4)
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
        const bomb = this.#modelRepository.getBomb()
        if (bomb.parent && bomb.parent.name === 'MainScene') {
            bomb.visible = false
        }
    }

    destroyObject(object) {
        if (object.name === undefined) {
            return
        }

        if (object.name === `item-${Enum.ItemId.Bomb}`) {
            if (object.parent && object.parent.name === 'MainScene') {
                object.visible = false
            }
            return
        }

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

    updateCameraZoom(zoomLevel) {
        this.#camera.zoom = zoomLevel
        this.#camera.updateProjectionMatrix()
    }

    getCamera() {
        return this.#camera
    }

}
