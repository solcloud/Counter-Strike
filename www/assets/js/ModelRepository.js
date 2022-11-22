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

    loadMap(map) {
        // TODO convert json map to single glb
        return new Promise(async resolve => {
            const promises = []
            const mapGroup = new THREE.Group()
            promises.push(this.#loadJSON(`./resources/map/${map}.json`).then((data) => mapGroup.add(data)))
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
                    mapGroup.add(data)
                })
            )

            await Promise.all(promises)
            resolve(mapGroup)
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

        return Promise.all(promises)
    }
}
