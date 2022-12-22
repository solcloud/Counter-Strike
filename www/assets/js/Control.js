import {InventorySlot} from "./Enums.js";

export class Control {
    #game
    #action
    #setting

    constructor(game, action) {
        this.#game = game
        this.#action = action
    }

    init(element, pointer, setting) {
        this.#setting = setting
        const self = this
        const action = this.#action
        const game = this.#game
        const sprayEnableSlots = [InventorySlot.SLOT_KNIFE, InventorySlot.SLOT_PRIMARY, InventorySlot.SLOT_BOMB]

        element.addEventListener("mouseup", function (event) {
            action.sprayingDisable()
            if (pointer.isLocked) {
                event.preventDefault()
            }
        })
        element.addEventListener("mousedown", function (event) {
            action.sprayingDisable()
            if (!game.isPlaying() || game.isPaused()) {
                return
            }
            if (game.meIsSpectating()) {
                game.spectatePlayer(event.buttons === 1)
                return
            }
            if (!pointer.isLocked) {
                return;
            }
            event.preventDefault()

            if (event.buttons === 2) {
                action.attack2()
            }
            if (event.buttons === 1) {
                if (sprayEnableSlots.includes(game.playerMe.getEquippedSlotId())) {
                    action.sprayingEnable()
                }
                if (game.playerMe.data.canAttack) {
                    action.attack(game.getPlayerMeRotation())
                }
            }
        })
        element.addEventListener('wheel', (event) => {
            if (!game.isPlaying() || !pointer.isLocked) {
                return;
            }

            if (event.deltaY > 0) { // wheel down
                if (game.playerMe.data.slots[InventorySlot.SLOT_SECONDARY]) {
                    action.equip(InventorySlot.SLOT_SECONDARY)
                } else {
                    action.equip(InventorySlot.SLOT_KNIFE)
                }
            } else { // wheel up
                if (game.playerMe.data.slots[InventorySlot.SLOT_PRIMARY]) {
                    action.equip(InventorySlot.SLOT_PRIMARY)
                } else {
                    action.equip(InventorySlot.SLOT_KNIFE)
                }
            }
        })
        element.addEventListener('keydown', function (event) {
            event.preventDefault()

            if (!game.isPlaying()) {
                return
            }

            self.#processKeyboardEvent(event, true)
        });
        element.addEventListener('keyup', function (event) {
            event.preventDefault()

            if (!game.isPlaying()) {
                return
            }

            self.#processKeyboardEvent(event, false)
        });
    }

    #processKeyboardEvent(event, isKeyDown) {
        const actionIndex = this.#setting.getBinds()[event.code]
        if (actionIndex !== undefined) {
            this.#action.actionCallback[actionIndex](isKeyDown)
        }
    }

    getTickAction() {
        if (this.#game.isPlaying() && this.#game.meIsAlive()) {
            return this.#action.getPlayerAction(this.#game, this.#setting.getSprayTriggerDeltaMs())
        }
        this.#action.resetStates()
        return ''
    }
}
