import {ItemId} from "./Enums.js";

export class ModelRepository {
    #gltfLoader
    #objectLoader
    #models = {}

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
                        object.material.map.magFilter = THREE.NearestFilter
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
        return this.#models[ItemId.Bomb]
    }

    getPlayer() {
        return this.#models.player
    }

    getModelForItem(item) {
        if (item.id === ItemId.Bomb) {
            return this.getBomb()
        }

        const model = this.#models[item.id]
        if (model === undefined) {
            console.warn("No model for", item)
            return new THREE.Mesh(new THREE.SphereGeometry(10), new THREE.MeshBasicMaterial({color: 0xFF0000}))
        }

        return model.clone()
    }

    loadAll() {
        const promises = []
        promises.push(this.#loadJSON('./resources/model/player.json').then((model) => {
            this.#models.player = model
        }))
        promises.push(this.#loadModel('./resources/model/bomb.glb').then((model) => {
            model.scene.scale.set(.3, .3, .3)
            model.scene.traverse(function (object) {
                if (object.isMesh) {
                    object.castShadow = true
                    object.receiveShadow = true
                }
            })

            this.#models[ItemId.Bomb] = model.scene
        }))
        promises.push(this.#loadModel('./resources/model/knife.glb').then((model) => {
            model.scene.scale.set(-400, 400, 400)
            model.scene.traverse(function (object) {
                if (object.isMesh) {
                    object.castShadow = true
                    object.receiveShadow = true
                }
            })

            this.#models[ItemId.Knife] = model.scene
        }))
        promises.push(this.#loadModel('./resources/model/pistol.glb').then((model) => {
            model.scene.scale.set(10, 10, 10)
            model.scene.traverse(function (object) {
                if (object.isMesh) {
                    object.castShadow = true
                    object.receiveShadow = true
                }
            })

            this.#models[ItemId.PistolUsp] = model.scene
            this.#models[ItemId.PistolP250] = model.scene
            this.#models[ItemId.PistolGlock] = model.scene
        }))
        promises.push(this.#loadModel('./resources/model/ak.glb').then((model) => {
            model.scene.scale.set(10, 10, 10)
            model.scene.traverse(function (object) {
                if (object.isMesh) {
                    object.castShadow = true
                    object.receiveShadow = true
                }
            })

            this.#models[ItemId.RifleAk] = model.scene
        }))
        promises.push(this.#loadModel('./resources/model/m4.glb').then((model) => {
            model.scene.scale.set(10, 10, 10)
            model.scene.traverse(function (object) {
                if (object.isMesh) {
                    object.castShadow = true
                    object.receiveShadow = true
                }
            })

            this.#models[ItemId.RifleM4A4] = model.scene
        }))

        return Promise.all(promises)
    }
}
