export class Player {
    data = {
        id: null,
        color: null,
        money: null,
        item: null,
        canAttack: null,
        canBuy: null,
        canPlant: null,
        slots: null,
        health: null,
        position: null,
        look: {
            horizontal: null,
            vertical: null,
        },
        isAttacker: null,
        heightSight: null,
        heightBody: null,
        height: null,
        armor: null,
        ammo: null,
        ammoReserve: null,
        isReloading: null,
    }
    custom = {
        slotId: null,
        slots: null,
    }
    threeObject = null

    constructor(serverData, object3D) {
        this.updateData(serverData)
        this.threeObject = object3D
    }

    equip(slotId) {
        this.custom.slotId = slotId
        this.custom.slots = JSON.stringify(this.data.slots)
    }

    updateData(serverData) {
        this.data = serverData
    }

    get3DObject() {
        return this.threeObject
    }

    getEquippedSlotId() {
        return this.custom.slotId
    }

    isInventoryChanged(serverState) {
        return (this.getEquippedSlotId() !== serverState.item.slot || this.custom.slots !== JSON.stringify(serverState.slots))
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

    isAttacker() {
        return this.data.isAttacker
    }

    isAlive() {
        return (this.data.health > 0)
    }

    respawn() {
        this.data.health = 100
        this.get3DObject().visible = true
    }

    died() {
        this.data.health = 0
        this.get3DObject().visible = false
    }

}
