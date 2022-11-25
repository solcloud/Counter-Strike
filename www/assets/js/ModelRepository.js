import * as Enum from "./Enums.js";

export class ModelRepository {
    #gltfLoader
    #objectLoader
    #models = {
        player: null,
        bomb: null,
        knife: null,
        pistol: null,
        ak: null,
        m4: null,
    }

    constructor() {
        this.#gltfLoader = new THREE.GLTFLoader()
        this.#objectLoader = new THREE.ObjectLoader()
    }

    #loadModel(url) {
        return new Promise(resolve => {
            this.#gltfLoader.load(url, resolve)
        });
    }

    #loadJSON(url) { // TODO migrate to gltf and remove
        return new Promise(resolve => {
            this.#objectLoader.load(url, resolve)
        });
    }

    loadMap(mapName) {
        return this.#loadModel(`./resources/map/${mapName}.glb`).then(async (model) => {
            model.scene.traverse(function (object) {
                if (object.isMesh) {
                    if (object.name === 'floor') {
                        object.material.envMapIntensity = .08
                    }
                    if (object.name !== 'world') {
                        object.castShadow = true
                    }
                    object.receiveShadow = true
                }
            })

            const sun = new THREE.DirectionalLight(0xffeac2, .9)
            sun.position.set(4000, 4999, -4000)
            sun.castShadow = true
            sun.shadow.mapSize.width = 512
            sun.shadow.mapSize.height = 512
            sun.shadow.camera.far = 20000
            sun.shadow.camera.left = -20000
            sun.shadow.camera.right = 20000
            sun.shadow.camera.top = 20000
            sun.shadow.camera.bottom = -20000
            model.scene.add(sun, new THREE.AmbientLight(0xcfe4bb, .4))
            return model.scene
        })
    }

    getBomb() {
        return this.#models.bomb
    }

    getPlayer() {
        return this.#models.player
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
            return this.getBomb()
        }

        console.warn("No model for", item)
        return new THREE.Mesh(new THREE.SphereGeometry(10), new THREE.MeshBasicMaterial({color: 0xFF0000}))
    }

    loadAll() {
        const promises = []
        promises.push(this.#loadJSON('./resources/model/player.json').then((model) => {
            this.#models.player = model
        }))
        promises.push(this.#loadModel('./resources/model/bomb.glb').then((model) => {
            this.#models.bomb = model.scene
            this.#models.bomb.scale.set(.3, .3, .3)
            this.#models.bomb.traverse(function (object) {
                if (object.isMesh) {
                    object.castShadow = true
                    object.receiveShadow = true
                }
            })
        }))
        promises.push(this.#loadModel('./resources/model/knife.glb').then((model) => {
            this.#models.knife = model.scene
            this.#models.knife.scale.set(-400, 400, 400)
            this.#models.knife.traverse(function (object) {
                if (object.isMesh) {
                    object.castShadow = true
                    object.receiveShadow = true
                }
            })
        }))
        promises.push(this.#loadModel('./resources/model/pistol.glb').then((model) => {
            this.#models.pistol = model.scene
            this.#models.pistol.scale.set(10, 10, 10)
            this.#models.pistol.traverse(function (object) {
                if (object.isMesh) {
                    object.castShadow = true
                    object.receiveShadow = true
                }
            })
        }))
        promises.push(this.#loadModel('./resources/model/ak.glb').then((model) => {
            this.#models.ak = model.scene
            this.#models.ak.scale.set(10, 10, 10)
            this.#models.ak.traverse(function (object) {
                if (object.isMesh) {
                    object.castShadow = true
                    object.receiveShadow = true
                }
            })
        }))
        promises.push(this.#loadModel('./resources/model/m4.glb').then((model) => {
            this.#models.m4 = model.scene
            this.#models.m4.scale.set(10, 10, 10)
            this.#models.m4.traverse(function (object) {
                if (object.isMesh) {
                    object.castShadow = true
                    object.receiveShadow = true
                }
            })
        }))

        return Promise.all(promises)
    }
}
