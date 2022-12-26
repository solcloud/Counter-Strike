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

    loadMap(mapName) {
        return this.#loadModel(`./resources/map/${mapName}.glb`).then((model) => {
            model.scene.traverse(function (object) {
                if (object.isMesh) {
                    if (object.name !== 'world') {
                        object.castShadow = true
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
            //sun.shadow.bias = .0001
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

            this.#models.player = model.scene.getObjectByName('player')
            this.#models.playerAnimation = model.animations
        }))
        promises.push(this.#loadModel('./resources/model/bomb.glb').then((model) => {
            model.scene.children.forEach((root) => root.visible = false)
            const item = model.scene.getObjectByName('item')
            item.traverse(function (object) {
                if (object.isMesh) {
                    object.castShadow = true
                }
            })
            item.visible = true

            this.#models[ItemId.Bomb] = model.scene
        }))
        promises.push(this.#loadModel('./resources/model/kit.glb').then((model) => {
            model.scene.traverse(function (object) {
                if (object.isMesh) {
                    object.castShadow = true
                }
            })

            this.#models[ItemId.DefuseKit] = model.scene
        }))
        promises.push(this.#loadModel('./resources/model/knife.glb').then((model) => {
            model.scene.children.forEach((root) => root.visible = false)
            const item = model.scene.getObjectByName('item')
            item.traverse(function (object) {
                if (object.isMesh) {
                    object.castShadow = true
                }
            })
            item.visible = true

            this.#models[ItemId.Knife] = model.scene
        }))
        promises.push(this.#loadModel('./resources/model/pistol.glb').then((model) => {
            model.scene.children.forEach((root) => root.visible = false)
            const item = model.scene.getObjectByName('item')
            item.traverse(function (object) {
                if (object.isMesh) {
                    object.castShadow = true
                }
            })
            item.visible = true

            this.#models[ItemId.PistolUsp] = model.scene
            this.#models[ItemId.PistolP250] = model.scene // fixme
            this.#models[ItemId.PistolGlock] = model.scene // fixme
        }))
        promises.push(this.#loadModel('./resources/model/ak.glb').then((model) => {
            model.scene.children.forEach((root) => root.visible = false)
            const item = model.scene.getObjectByName('item')
            item.traverse(function (object) {
                if (object.isMesh) {
                    object.castShadow = true
                }
            })
            item.visible = true

            this.#models[ItemId.RifleAk] = model.scene
        }))
        promises.push(this.#loadModel('./resources/model/m4.glb').then((model) => {
            model.scene.children.forEach((root) => root.visible = false)
            const item = model.scene.getObjectByName('item')
            item.traverse(function (object) {
                if (object.isMesh) {
                    object.castShadow = true
                }
            })
            item.visible = true

            this.#models[ItemId.RifleM4A4] = model.scene
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
        promises.push(this.#loadTexture('./resources/img/player/cap_1.png').then((texture) => {
            texture.flipY = false
            this.#textures.cap[1] = texture
        }))
        promises.push(this.#loadTexture('./resources/img/player/cap_2.png').then((texture) => {
            texture.flipY = false
            this.#textures.cap[2] = texture
        }))
        promises.push(this.#loadTexture('./resources/img/player/cap_3.png').then((texture) => {
            texture.flipY = false
            this.#textures.cap[3] = texture
        }))
        promises.push(this.#loadTexture('./resources/img/player/cap_4.png').then((texture) => {
            texture.flipY = false
            this.#textures.cap[4] = texture
        }))
        promises.push(this.#loadTexture('./resources/img/player/cap_5.png').then((texture) => {
            texture.flipY = false
            this.#textures.cap[5] = texture
        }))
        promises.push(this.#loadTexture('./resources/img/sphere_glow.png').then((texture) => {
            const material = new THREE.SpriteMaterial({
                map: texture,
                color: 0xFFFFFF,
                blending: THREE.AdditiveBlending,
                transparent: true,
            });
            const sprite = new THREE.Sprite(material);
            sprite.scale.set(30, 30, 1);

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
