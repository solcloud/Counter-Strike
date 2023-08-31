import {ItemId} from "./Enums.js";

export class ModelRepository {
    #gltfLoader
    #objectLoader
    #textureLoader
    #models = {}
    #meshes = {}
    #materials = {
        caps: {},
        outfitTeam: null,
        outfitOpponent: null,
    }
    #textures = {
        cap: {}
    }
    #mapObjects = [];

    constructor() {
        this.#gltfLoader = new THREE.GLTFLoader()
        this.#objectLoader = new THREE.ObjectLoader()
        this.#textureLoader = new THREE.TextureLoader()

    }

    #loadModel(url) {
        return this.#gltfLoader.loadAsync(url)
    }

    #loadTexture(url) {
        return this.#textureLoader.loadAsync(url)
    }

    getMapObjects() {
        return this.#mapObjects
    }

    loadMap(mapName) {
        const self = this
        self.#mapObjects = []
        return this.#loadModel(`./resources/map/${mapName}.glb`).then((model) => {
            model.scene.traverse(function (object) {
                if (object.isMesh) {
                    if (object.name !== 'world') {
                        object.castShadow = true
                        self.#mapObjects.push(object)
                    }
                    if (object.name === 'floor') {
                        object.material.envMapIntensity = .08
                        object.castShadow = false
                        object.material.polygonOffset = true
                        object.material.polygonOffsetFactor = -1
                    }
                    object.receiveShadow = true
                    object.matrixAutoUpdate = false
                }
            })

            const sun = new THREE.DirectionalLight(0xffeac2, .9)
            sun.position.set(4000, 4999, -4000)
            sun.castShadow = true
            sun.shadow.mapSize.width = 4096
            sun.shadow.mapSize.height = 4096
            sun.shadow.camera.far = 10000
            sun.shadow.camera.left = -2000
            sun.shadow.camera.right = 2000
            sun.shadow.camera.top = 0
            sun.shadow.camera.bottom = -3000
            model.scene.add(sun, new THREE.AmbientLight(0xcfe4bb, .4))
            return model.scene
        })
    }

    getBomb() {
        const bomb = this.#models[ItemId.Bomb]
        bomb.children.forEach((root) => root.visible = false)
        bomb.getObjectByName('item').visible = true
        bomb.position.setScalar(0)
        return bomb
    }

    getPlayer(colorIndex, isOpponent) {
        const clone = THREE.SkeletonUtils.clone(this.#models.player)
        const player = clone.getObjectByName('player')

        const headWear = player.getObjectByName('Wolf3D_Headwear')
        if (this.#materials.caps[colorIndex] === undefined) {
            const newMaterial = headWear.material.clone()
            newMaterial.map = this.#textures.cap[colorIndex]
            this.#materials.caps[colorIndex] = newMaterial
        }
        headWear.material = this.#materials.caps[colorIndex]

        const outfit = player.getObjectByName('Wolf3D_Outfit_Top')
        outfit.material = isOpponent ? this.#materials.outfitOpponent : this.#materials.outfitTeam
        return player
    }

    getPlayerAnimation() {
        return this.#models.playerAnimation
    }

    getPlayerHitMesh() {
        return this.#meshes.playerHitMesh.clone()
    }

    getModelForItem(item) {
        if (item.id === ItemId.Bomb) {
            return this.getBomb()
        }

        const model = this.#models[item.id]
        if (model === undefined) {
            console.warn("No model for", item)
            return new THREE.Mesh(new THREE.SphereGeometry(8), new THREE.MeshBasicMaterial({color: 0xFF0000}))
        }

        return model.clone()
    }

    loadAll() {
        const self = this
        const promises = []

        promises.push(this.#loadModel('./resources/model/player.glb').then((model) => {
            model.scene.traverse(function (object) {
                object.frustumCulled = false // fixme find out how to recalculate bounding boxes or bake animation
                if (object.isMesh) {
                    object.castShadow = true
                    object.receiveShadow = true
                }
            })

            // fixme inside model
            const kitSlot = model.scene.getObjectByName('slot-11')
            kitSlot.position.y -= 11
            const knifeSlot = model.scene.getObjectByName('slot-0')
            knifeSlot.position.y += 5.7
            knifeSlot.position.x += 3.8
            knifeSlot.position.z += -3.5
            knifeSlot.rotateY(degreeToRadian(-70))
            knifeSlot.rotateX(degreeToRadian(-4))
            const belt = model.scene.getObjectByName('belt')
            const slot4 = new THREE.Object3D()
            slot4.name = 'slot-4'
            slot4.rotation.x = degreeToRadian(90)
            slot4.position.set(16, 131, -5)
            const slot5 = new THREE.Object3D()
            slot5.name = 'slot-5'
            slot5.rotation.x = degreeToRadian(90)
            slot5.position.set(-14, 114, -9)
            const slot6 = new THREE.Object3D()
            slot6.name = 'slot-6'
            slot6.rotation.x = degreeToRadian(90)
            slot6.position.set(-19, 114, 3)
            const slot7 = new THREE.Object3D()
            slot7.name = 'slot-7'
            slot7.rotation.x = degreeToRadian(90)
            slot7.position.set(16.5, 130, 1)
            const slot8 = new THREE.Object3D()
            slot8.name = 'slot-8'
            slot8.position.set(-18, 112, -4)
            const slot9 = new THREE.Object3D()
            slot9.name = 'slot-9'
            slot9.rotation.x = degreeToRadian(90)
            slot9.position.set(13, 110, -8)
            belt.add(slot4, slot5, slot6, slot7, slot8, slot9)

            this.#models.player = model.scene.getObjectByName('player')
            this.#models.playerAnimation = model.animations
        }))

        const models = {}
        models[ItemId.Bomb] = 'bomb.glb'
        models[ItemId.Knife] = 'knife.glb'
        models[ItemId.RifleAk] = 'ak.glb'
        models[ItemId.RifleM4A4] = 'm4.glb'
        models[ItemId.PistolUsp] = 'pistol.glb' // fixme
        models[ItemId.PistolP250] = 'pistol.glb'
        models[ItemId.PistolGlock] = 'pistol.glb' // fixme
        models[ItemId.HighExplosive] = 'highexplosive.glb'
        models[ItemId.Flashbang] = 'flashbang.glb'
        models[ItemId.Smoke] = 'smoke.glb'
        models[ItemId.Decoy] = 'decoy.glb'
        models[ItemId.Incendiary] = 'incendiary.glb'
        models[ItemId.Molotov] = 'molotov.glb'

        Object.keys(models).forEach(function (itemId) {
            const fileName = models[itemId]
            promises.push(self.#loadModel(`./resources/model/${fileName}`).then((model) => {
                model.scene.children.forEach((root) => root.visible = false)
                const item = model.scene.getObjectByName('item')
                item.traverse(function (object) {
                    if (object.isMesh) {
                        object.castShadow = true
                    }
                })
                item.visible = true

                self.#models[itemId] = model.scene
            }))
        })
        promises.push(this.#loadModel('./resources/model/kit.glb').then((model) => {
            model.scene.traverse(function (object) {
                if (object.isMesh) {
                    object.castShadow = true
                }
            })

            this.#models[ItemId.DefuseKit] = model.scene
        }))

        promises.push(this.#loadTexture('./resources/img/player/outfit_0.png').then((texture) => {
            texture.flipY = false
            texture.encoding = THREE.sRGBEncoding
            this.#textures.team = texture
        }))
        promises.push(this.#loadTexture('./resources/img/player/outfit_1.png').then((texture) => {
            texture.flipY = false
            texture.encoding = THREE.sRGBEncoding
            this.#textures.opponent = texture
        }))
        for (let number = 1; number <= 5; number++) {
            promises.push(self.#loadTexture(`./resources/img/player/cap_${number}.png`).then((texture) => {
                texture.flipY = false
                self.#textures.cap[number] = texture
            }))
        }

        promises.push(this.#loadTexture('./resources/img/sphere_glow.png').then((texture) => {
            const material = new THREE.SpriteMaterial({
                map: texture,
                color: 0xFFFFFF,
                blending: THREE.AdditiveBlending,
                transparent: true,
            });
            const sprite = new THREE.Sprite(material);
            sprite.scale.set(35, 45, 30);

            this.#meshes.playerHitMesh = sprite
        }))

        return Promise.all(promises).then(() => {
            const outfit = this.#models.player.getObjectByName('Wolf3D_Outfit_Top')
            const materialTeam = outfit.material.clone()
            materialTeam.map = this.#textures.team
            const materialOpponent = outfit.material.clone()
            materialOpponent.map = this.#textures.opponent

            this.#materials.outfitTeam = materialTeam
            this.#materials.outfitOpponent = materialOpponent
        })
    }
}
