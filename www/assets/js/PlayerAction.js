import {Action, InventorySlot} from "./Enums.js";


export class PlayerAction {
    #game
    #hud
    #states = {
        shootLookAt: '',
        lastLookAt: '',
        sprayTriggerStartMs: null,
        moveForward: false,
        moveBackward: false,
        moveLeft: false,
        moveRight: false,
        jumping: false,
        crouching: false,
        standing: false,
        attack: false,
        shifting: false,
        running: false,
        reload: false,
        equip: false,
        drop: false,
        spraying: false,
    }
    actionCallback = {}

    constructor(game, hud) {
        this.#game = game
        this.#hud = hud

        this.#loadCallbacks()
    }

    #loadCallbacks() {
        this.actionCallback[Action.MOVE_FORWARD] = (enabled) => this.moveForward(enabled)
        this.actionCallback[Action.MOVE_LEFT] = (enabled) => this.moveLeft(enabled)
        this.actionCallback[Action.MOVE_BACK] = (enabled) => this.moveBackward(enabled)
        this.actionCallback[Action.MOVE_RIGHT] = (enabled) => this.moveRight(enabled)
        this.actionCallback[Action.JUMP] = (enabled) => enabled && this.jump()
        this.actionCallback[Action.CROUCH] = (enabled) => enabled ? this.crouch() : this.stand()
        this.actionCallback[Action.WALK] = (enabled) => enabled ? this.shift() : this.run()
        this.actionCallback[Action.RELOAD] = (enabled) => enabled && this.reload()
        this.actionCallback[Action.EQUIP_KNIFE] = (enabled) => enabled && this.equip(InventorySlot.SLOT_KNIFE)
        this.actionCallback[Action.EQUIP_PRIMARY] = (enabled) => enabled && this.equip(InventorySlot.SLOT_PRIMARY)
        this.actionCallback[Action.EQUIP_SECONDARY] = (enabled) => enabled && this.equip(InventorySlot.SLOT_SECONDARY)
        this.actionCallback[Action.EQUIP_BOMB] = (enabled) => enabled && this.equip(InventorySlot.SLOT_BOMB)
        this.actionCallback[Action.BUY_MENU] = (enabled) => enabled && this.#hud.toggleBuyMenu()
        this.actionCallback[Action.SCORE_BOARD] = (enabled) => this.#hud.toggleScore(enabled)
        this.actionCallback[Action.DROP] = (enabled) => enabled && this.drop()
    }

    attack([x, y]) {
        this.#states.attack = true
        this.#states.shootLookAt = `lookAt ${x} ${y}`
    }

    equip(slotId) {
        this.#states.equip = slotId
    }

    drop() {
        this.#states.drop = true
    }

    moveForward(enabled) {
        this.#states.moveForward = enabled
    }

    moveLeft(enabled) {
        this.#states.moveLeft = enabled
    }

    moveBackward(enabled) {
        this.#states.moveBackward = enabled
    }

    moveRight(enabled) {
        this.#states.moveRight = enabled
    }

    jump() {
        this.#states.jumping = true
    }

    stand() {
        this.#states.standing = true
    }

    crouch() {
        this.#states.crouching = true
    }

    run() {
        this.#states.running = true
    }

    shift() {
        this.#states.shifting = true
    }

    reload() {
        this.#states.reload = true
    }

    sprayingEnable() {
        this.#states.sprayTriggerStartMs = Date.now()
        this.#states.spraying = true
    }

    sprayingDisable() {
        if (!this.#states.spraying) {
            return
        }

        this.#states.spraying = false
        this.#states.sprayTriggerStartMs = null
    }

    getPlayerAction(sprayTriggerDeltaMs) {
        let action = []
        const game = this.#game

        if (game.buyList.length) {
            game.buyList.forEach(function (buyMenuItemId) {
                action.push('buy ' + buyMenuItemId)
            })
            game.buyList = []
        }
        if (this.#states.moveForward) {
            action.push('forward')
        }
        if (this.#states.moveLeft) {
            action.push('left')
        }
        if (this.#states.moveRight) {
            action.push('right')
        }
        if (this.#states.moveBackward) {
            action.push('backward')
        }
        if (this.#states.jumping) {
            action.push('jump')
            this.#states.jumping = false
        }
        if (this.#states.crouching) {
            action.push('crouch')
            this.#states.crouching = false
        }
        if (this.#states.standing) {
            action.push('stand')
            this.#states.standing = false
        }
        if (this.#states.shifting) {
            action.push('walk')
            this.#states.shifting = false
        }
        if (this.#states.running) {
            action.push('run')
            this.#states.running = false
        }
        if (this.#states.reload) {
            action.push('reload')
            this.#states.reload = false
        }
        if (this.#states.drop) {
            action.push('drop')
            this.#states.drop = false
        }
        if (this.#states.equip !== false) {
            action.push('equip ' + this.#states.equip)
            this.#states.equip = false
        }

        if (this.#states.attack) {
            game.attack()
            action.push(this.#states.shootLookAt)
            action.push('attack')
            this.#states.attack = false
        } else if (this.#states.spraying && this.#states.sprayTriggerStartMs && this.#states.sprayTriggerStartMs + sprayTriggerDeltaMs < Date.now()) {
            game.attack()
            let rotation = game.getPlayerMeRotation()
            action.push(`lookAt ${rotation[0]} ${rotation[1]}`)
            action.push('attack')
        } else {
            let horizontal, vertical
            [horizontal, vertical] = game.getPlayerMeRotation()
            let lookAt = `lookAt ${horizontal} ${vertical}`
            if (this.#states.lastLookAt !== lookAt) {
                action.push(lookAt)
                this.#states.lastLookAt = lookAt
            }
        }

        return action.join('|')
    }
}
