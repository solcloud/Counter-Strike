export class Player {
    data = {
        id: null,
        color: null,
        money: null,
        item: null,
        canAttack: null,
        slots: null,
        health: null,
        position: null,
        look: null,
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
        slotId: 2
    }
    threeObject = null

    constructor(serverData, object3D) {
        this.updateData(serverData)
        this.threeObject = object3D
    }

    equip(slotId) {
        this.custom.slotId = slotId
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

    getTeamIndex() {
        return +(this.data.isAttacker)
    }

    getOtherTeamIndex() {
        return +(!this.data.isAttacker)
    }

    getId() {
        if (this.data.id === null) {
            throw new Error("NO id set")
        }
        return this.data.id
    }

    isAttacker() {
        return this.data.isAttacker
    }

    isAlive() {
        return (this.data.health > 0)
    }
}
