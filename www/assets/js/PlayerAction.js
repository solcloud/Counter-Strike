import {Action, InventorySlot} from "./Enums.js";

export class PlayerAction {
    #game
    #states = {}
    #actionCallback = {}

    constructor(game, hud) {
        this.#game = game
        this.#loadCallbacks(game, hud)
        this.resetStates()
    }

    execute(actionIndex, isKeyDown) {
        if (this.#actionCallback[actionIndex]) {
            this.#actionCallback[actionIndex](isKeyDown)
            return
        }

        if (!isKeyDown && actionIndex.startsWith('buy ')) {
            const match = actionIndex.match(/buy (\d+)([,\d]+)?/)
            if (match === null) {
                return
            }
            if (match[1]) {
                this.#game.buyList.push(match[1])
            }
            if (match[2]) {
                match[2].split(',').forEach((value) => {
                    let buyItemId = parseInt(value)
                    if (buyItemId) {
                        this.#game.buyList.push(buyItemId)
                    }
                })
            }
            return
        }
    }

    resetStates() {
        this.#states = {
            attackLook: '',
            lastLook: '',
            attack2StartMs: 0,
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

    #loadCallbacks(game, hud) {
        this.#actionCallback[Action.MOVE_FORWARD] = (enabled) => this.#states.moveForward = enabled
        this.#actionCallback[Action.MOVE_LEFT] = (enabled) => this.#states.moveLeft = enabled
        this.#actionCallback[Action.MOVE_BACK] = (enabled) => this.#states.moveBackward = enabled
        this.#actionCallback[Action.MOVE_RIGHT] = (enabled) => this.#states.moveRight = enabled
        this.#actionCallback[Action.USE] = (enabled) => this.#states.use = enabled
        this.#actionCallback[Action.JUMP] = (enabled) => enabled && (this.#states.jumping = true)
        this.#actionCallback[Action.CROUCH] = (enabled) => enabled ? this.#states.crouching = true : this.#states.standing = true
        this.#actionCallback[Action.WALK] = (enabled) => enabled ? this.#states.shifting = true : this.#states.running = true
        this.#actionCallback[Action.DROP] = (enabled) => enabled && (this.#states.drop = true)
        this.#actionCallback[Action.RELOAD] = (enabled) => enabled && this.reload()
        this.#actionCallback[Action.EQUIP_KNIFE] = (enabled) => enabled && this.equip(InventorySlot.SLOT_KNIFE)
        this.#actionCallback[Action.EQUIP_PRIMARY] = (enabled) => enabled && this.equip(InventorySlot.SLOT_PRIMARY)
        this.#actionCallback[Action.EQUIP_SECONDARY] = (enabled) => enabled && this.equip(InventorySlot.SLOT_SECONDARY)
        this.#actionCallback[Action.EQUIP_BOMB] = (enabled) => enabled && this.equip(InventorySlot.SLOT_BOMB)
        this.#actionCallback[Action.EQUIP_SMOKE] = (enabled) => enabled && this.equip(InventorySlot.SLOT_GRENADE_SMOKE)
        this.#actionCallback[Action.EQUIP_FLASH] = (enabled) => enabled && this.equip(InventorySlot.SLOT_GRENADE_FLASH)
        this.#actionCallback[Action.EQUIP_HE] = (enabled) => enabled && this.equip(InventorySlot.SLOT_GRENADE_HE)
        this.#actionCallback[Action.EQUIP_MOLOTOV] = (enabled) => enabled && this.equip(InventorySlot.SLOT_GRENADE_MOLOTOV)
        this.#actionCallback[Action.EQUIP_DECOY] = (enabled) => enabled && this.equip(InventorySlot.SLOT_GRENADE_DECOY)
        this.#actionCallback[Action.BUY_MENU] = (enabled) => enabled && hud.toggleBuyMenu()
        this.#actionCallback[Action.GAME_MENU] = (enabled) => enabled && hud.toggleGameMenu()
        this.#actionCallback[Action.CLEAR_DECALS] = (enabled) => enabled && game.clearDecals()
        this.#actionCallback[Action.SWITCH_HANDS] = (enabled) => enabled && game.switchHands()
        this.#actionCallback[Action.SCORE_BOARD] = (enabled) => hud.toggleScore(enabled)
        this.#actionCallback[Action.DROP_BOMB] = (enabled) => {
            if (enabled && game.playerMe.data.slots[InventorySlot.SLOT_BOMB]) {
                this.equip(InventorySlot.SLOT_BOMB)
                this.#states.drop = true
            }
        }
    }

    #rotationToServerLook(xy) {
        return `look ${xy[0].toFixed(2)} ${xy[1].toFixed(2)}`
    }

    attack(xy) {
        this.#states.attack = true
        this.#states.attackLook = this.#rotationToServerLook(xy)
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

    getPlayerAction(game, userSetting) {
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

        if (this.#states.attack2 && this.#states.attack2StartMs + userSetting.getMouseClickTimeMs() < Date.now()) {
            action.push('attack2')
            this.#states.attack2 = false
            this.#states.attack2StartMs = Date.now()
        }
        if (this.#states.attack) {
            action.push(this.#states.attackLook)
            action.push('attack')
            this.#states.attack = false
        } else if (this.#states.spraying && this.#states.sprayTriggerStartMs && this.#states.sprayTriggerStartMs + userSetting.getSprayTriggerDeltaMs() < Date.now()) {
            action.push(this.#rotationToServerLook(game.getPlayerMeRotation()))
            action.push('attack')
        } else {
            let look = this.#rotationToServerLook(game.getPlayerMeRotation())
            if (this.#states.lastLook !== look) {
                action.push(look)
                this.#states.lastLook = look
            }
        }

        return action.join('|')
    }
}
