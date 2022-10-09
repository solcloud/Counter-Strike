export class Player {
    data = {
        id: null,
        color: null,
        money: null,
        item: null,
        canAttack: null,
        canBuy: null,
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
}
