import {InventorySlot} from "./Enums.js";

export class Control {
    #pointerLock;
    #localPlayerActions;
    #game;
    #hud;

    constructor(game, hud) {
        this.#game = game
        this.#hud = hud
    }

    getRotation() {
        return threeRotationToServer(this.#pointerLock.getObject().rotation)
    }

    init(camera) {
        let shootLookAt = ''
        let lastLookAt = ''
        let moveForward = false
        let moveBackward = false
        let moveLeft = false
        let moveRight = false
        let jumping = false
        let crouching = false
        let standing = false
        let attack = false
        let shifting = false
        let running = false
        let reload = false
        let equip = false
        let drop = false
        let spraying = false

        const self = this
        const game = this.#game
        const hud = this.#hud
        this.#pointerLock = new THREE.PointerLockControls(camera, document.body)
        const pointer = this.#pointerLock
        let sprayTriggerStartMs = null;
        const sprayTriggerDeltaMs = 80; // TODO settings

        // todo: use binds object for action shortcut and allow changing in settings
        document.addEventListener("mouseup", function (event) {
            event.preventDefault()
            spraying = false
            sprayTriggerStartMs = null
        })
        document.addEventListener("mousedown", function (event) {
            event.preventDefault()
            spraying = false
            if (!(game.isPlaying() && game.meIsAlive())) {
                return
            }

            if (!pointer.isLocked || !game.playerMe.data.canAttack) {
                return;
            }

            if (game.playerMe.getEquippedSlotId() === InventorySlot.SLOT_PRIMARY) {
                sprayTriggerStartMs = Date.now()
                spraying = true
            }

            attack = true
            let lookAt = self.getRotation()
            shootLookAt = `lookAt ${lookAt[0]} ${lookAt[1]}`
        })
        document.addEventListener('wheel', (event) => {
            if (!(game.isPlaying() && game.meIsAlive())) {
                return
            }

            if (event.deltaY > 0) {
                equip = InventorySlot.SLOT_SECONDARY
            } else {
                equip = InventorySlot.SLOT_PRIMARY
            }
        })
        document.addEventListener('keydown', function (event) {
            event.preventDefault()

            switch (event.code) {
                case 'Tab':
                    hud.showScore()
                    break;
            }

            if (!(game.isPlaying() && game.meIsAlive())) {
                return
            }

            switch (event.code) {
                case 'KeyW':
                    moveForward = true;
                    break;
                case 'KeyA':
                    moveLeft = true;
                    break;
                case 'KeyS':
                    moveBackward = true;
                    break;
                case 'KeyD':
                    moveRight = true;
                    break;
                case 'KeyG':
                    drop = true;
                    break;
                case 'Space':
                    jumping = true;
                    break;
                case 'CapsLock':
                case 'ControlLeft':
                    crouching = true;
                    break;
                case 'ShiftLeft':
                    shifting = true;
                    break;
            }
        });
        document.addEventListener('keyup', function (event) {
            event.preventDefault()

            switch (event.code) {
                case 'Tab':
                    hud.hideScore()
                    break;
            }

            if (!(game.isPlaying() && game.meIsAlive())) {
                return
            }

            switch (event.code) {
                case 'KeyW':
                    moveForward = false;
                    break;
                case 'KeyA':
                    moveLeft = false;
                    break;
                case 'KeyS':
                    moveBackward = false;
                    break;
                case 'KeyD':
                    moveRight = false;
                    break;
                case 'KeyR':
                    reload = true;
                    break;
                case 'KeyQ':
                    equip = InventorySlot.SLOT_KNIFE;
                    break;
                case 'Digit1':
                    equip = InventorySlot.SLOT_PRIMARY;
                    break;
                case 'Digit2':
                    equip = InventorySlot.SLOT_SECONDARY;
                    break;
                case 'Digit5':
                    equip = InventorySlot.SLOT_BOMB;
                    break;
                case 'CapsLock':
                case 'ControlLeft':
                    standing = true;
                    break;
                case 'ShiftLeft':
                    running = true;
                    break;
                case 'KeyB':
                    hud.toggleBuyMenu()
                    break;
            }
        });

        this.#localPlayerActions = function () {
            let serverAction = []

            if (game.buyList.length) {
                game.buyList.forEach(function (buyMenuItemId) {
                    serverAction.push('buy ' + buyMenuItemId)
                })
                game.buyList = []
            }
            if (moveForward) {
                serverAction.push('forward')
            }
            if (moveLeft) {
                serverAction.push('left')
            }
            if (moveRight) {
                serverAction.push('right')
            }
            if (moveBackward) {
                serverAction.push('backward')
            }
            if (jumping) {
                serverAction.push('jump')
                jumping = false
            }
            if (crouching) {
                serverAction.push('crouch')
                crouching = false
            }
            if (standing) {
                serverAction.push('stand')
                standing = false
            }
            if (shifting) {
                serverAction.push('walk')
                shifting = false
            }
            if (running) {
                serverAction.push('run')
                running = false
            }
            if (reload) {
                serverAction.push('reload')
                reload = false
            }
            if (drop) {
                serverAction.push('drop')
                drop = false
            }
            if (equip !== false) {
                serverAction.push('equip ' + equip)
                equip = false
            }

            if (attack) {
                game.attack()
                serverAction.push(shootLookAt)
                serverAction.push('attack')
                attack = false
            } else if (spraying && sprayTriggerStartMs && sprayTriggerStartMs + sprayTriggerDeltaMs < Date.now()) {
                game.attack()
                let rotation = self.getRotation()
                serverAction.push(`lookAt ${rotation[0]} ${rotation[1]}`)
                serverAction.push('attack')
            } else {
                let horizontal, vertical
                [horizontal, vertical] = self.getRotation()
                let action = `lookAt ${horizontal} ${vertical}`
                if (lastLookAt !== action) {
                    serverAction.push(action)
                    lastLookAt = action
                }
            }

            return serverAction.join('|')
        }
    }

    requestLock() {
        if (this.#pointerLock.isLocked) {
            return
        }
        this.#pointerLock.lock()
    }

    requestUnLock() {
        if (!this.#pointerLock.isLocked) {
            return
        }
        this.#pointerLock.unlock()
    }

    getTickAction() {
        return this.#localPlayerActions()
    }
}
