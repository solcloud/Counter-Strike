import {Action, InventorySlot} from "./Enums.js";

export class PlayerAction {
    #states = {}
    actionCallback = {}

    constructor(hud) {
        this.#loadCallbacks(hud)
        this.resetStates()
    }

    resetStates() {
        this.#states = {
            attacklook: '',
            lastlook: '',
            sprayTriggerStartMs: null,
            moveForward: false,
            moveBackward: false,
            moveLeft: false,
            moveRight: false,
            use: false,
            jumping: false,
            crouching: false,
            standing: false,
            attack: false,
            attack2: false,
            shifting: false,
            running: false,
            reload: false,
            equip: false,
            drop: false,
            spraying: false,
        }
    }

    #loadCallbacks(hud) {
        this.actionCallback[Action.MOVE_FORWARD] = (enabled) => this.#states.moveForward = enabled
        this.actionCallback[Action.MOVE_LEFT] = (enabled) => this.#states.moveLeft = enabled
        this.actionCallback[Action.MOVE_BACK] = (enabled) => this.#states.moveBackward = enabled
        this.actionCallback[Action.MOVE_RIGHT] = (enabled) => this.#states.moveRight = enabled
        this.actionCallback[Action.USE] = (enabled) => this.#states.use = enabled
        this.actionCallback[Action.JUMP] = (enabled) => enabled && (this.#states.jumping = true)
        this.actionCallback[Action.CROUCH] = (enabled) => enabled ? this.#states.crouching = true : this.#states.standing = true
        this.actionCallback[Action.WALK] = (enabled) => enabled ? this.#states.shifting = true : this.#states.running = true
        this.actionCallback[Action.DROP] = (enabled) => enabled && (this.#states.drop = true)
        this.actionCallback[Action.RELOAD] = (enabled) => enabled && this.reload()
        this.actionCallback[Action.EQUIP_KNIFE] = (enabled) => enabled && this.equip(InventorySlot.SLOT_KNIFE)
        this.actionCallback[Action.EQUIP_PRIMARY] = (enabled) => enabled && this.equip(InventorySlot.SLOT_PRIMARY)
        this.actionCallback[Action.EQUIP_SECONDARY] = (enabled) => enabled && this.equip(InventorySlot.SLOT_SECONDARY)
        this.actionCallback[Action.EQUIP_BOMB] = (enabled) => enabled && this.equip(InventorySlot.SLOT_BOMB)
        this.actionCallback[Action.BUY_MENU] = (enabled) => enabled && hud.toggleBuyMenu()
        this.actionCallback[Action.GAME_MENU] = (enabled) => enabled && hud.toggleGameMenu()
        this.actionCallback[Action.SCORE_BOARD] = (enabled) => hud.toggleScore(enabled)
    }

    #rotationToServerlook(xy) {
        return `look ${xy[0].toFixed(2)} ${xy[1].toFixed(2)}`
    }

    attack(xy) {
        this.#states.attack = true
        this.#states.attacklook = this.#rotationToServerlook(xy)
    }

    attack2() {
        this.#states.attack2 = true
    }

    equip(slotId) {
        this.sprayingDisable()
        this.#states.equip = slotId
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

    getPlayerAction(game, sprayTriggerDeltaMs) {
        let action = []

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
        if (this.#states.use) {
            action.push('use')
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
        if (this.#states.equip !== false) {
            action.push('equip ' + this.#states.equip)
            this.#states.equip = false
        }
        if (this.#states.reload) {
            action.push('reload')
            this.#states.reload = false
        }
        if (this.#states.drop) {
            action.push('drop')
            this.#states.drop = false
        }

        if (this.#states.attack2) {
            action.push('attack2')
            this.#states.attack2 = false
        }
        if (this.#states.attack) {
            action.push(this.#states.attacklook)
            action.push('attack')
            this.#states.attack = false
        } else if (this.#states.spraying && this.#states.sprayTriggerStartMs && this.#states.sprayTriggerStartMs + sprayTriggerDeltaMs < Date.now()) {
            action.push(this.#rotationToServerlook(game.getPlayerMeRotation()))
            action.push('attack')
        } else {
            let look = this.#rotationToServerlook(game.getPlayerMeRotation())
            if (this.#states.lastlook !== look) {
                action.push(look)
                this.#states.lastlook = look
            }
        }

        return action.join('|')
    }
}
