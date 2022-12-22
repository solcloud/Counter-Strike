export class Player {
    data = {
        id: null,
        color: null,
        money: null,
        item: {
            id: null,
            slot: null,
        },
        canAttack: null,
        canBuy: null,
        canPlant: null,
        slots: {},
        health: null,
        position: null,
        look: {
            horizontal: null,
            vertical: null,
        },
        isAttacker: null,
        sight: null,
        armor: null,
        armorType: null,
        ammo: null,
        ammoReserve: null,
        isReloading: null,
    }
    #custom = {
        slotId: null,
        slots: null,
        crouchSight: null,
    }
    #animation = {}
    #threeObject = null

    constructor(serverData, object3D) {
        this.updateData(serverData)
        this.#threeObject = object3D
    }

    set3DObject(object) {
        this.#threeObject = object
    }

    setAnimations(animations) {
        animations.forEach((clip) => {
            const mixer = new THREE.AnimationMixer(this.#threeObject)
            const action = mixer.clipAction(clip);
            if (clip.name === 'crouch') {
                action.play()
                this.#animation.crouch = mixer
            }
        })
    }

    equip(slotId) {
        this.#custom.slotId = slotId
        this.#custom.slots = JSON.stringify(this.data.slots)
    }

    updateData(serverData) {
        this.data = serverData
    }

    get3DObject() {
        return this.#threeObject
    }

    animate() {
        if (this.#animation.crouch && this.data.sight !== this.#custom.crouchSight) {
            this.#animation.crouch.setTime(this.data.sight + 9)
            this.#custom.crouchSight = this.data.sight
        }
    }

    getEquippedSlotId() {
        return this.#custom.slotId
    }

    isInventoryChanged(serverState) {
        return (this.getEquippedSlotId() !== serverState.item.slot || this.#custom.slots !== JSON.stringify(serverState.slots))
    }

    getTeamName() {
        return (this.isAttacker() ? 'Attackers' : 'Defenders')
    }

    getOtherTeamName() {
        return (this.isAttacker() ? 'Defenders' : 'Attackers')
    }

    getTeamIndex() {
        return (this.data.isAttacker ? 1 : 0)
    }

    getOtherTeamIndex() {
        return (this.data.isAttacker ? 0 : 1)
    }

    getId() {
        if (this.data.id === null) {
            throw new Error("No ID set")
        }
        return this.data.id
    }

    getColorIndex() {
        return this.data.color
    }

    isAttacker() {
        return this.data.isAttacker
    }

    isAlive() {
        return (this.data.health > 0)
    }

    respawn() {
        this.data.health = 100
        this.get3DObject().visible = true
        this.#custom = {
            slotId: null,
            slots: null,
            crouchSight: null,
        }
    }

    died() {
        this.data.health = 0
        this.get3DObject().visible = false
    }

}
