export class Control {
    #pointerLock;
    #localPlayerActions;
    #game;
    #hud;

    constructor(game, hud) {
        this.#game = game
        this.#hud = hud
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
        let shoot = false
        let shifting = false
        let running = false
        let reload = false
        let equip = false

        const game = this.#game
        const hud = this.#hud
        this.#pointerLock = new THREE.PointerLockControls(camera, document.body)
        const pointer = this.#pointerLock

        document.addEventListener("click", function (event) {
            event.preventDefault()
            if (!game.playerIsAlive()) {
                return
            }

            if (pointer.isLocked) {
                shoot = true
                let lookAt = threeHorizontalRotationToServer(pointer.getObject().rotation)
                shootLookAt = `lookAt ${lookAt[0]} ${lookAt[1]}`
            }
        })
        document.addEventListener('wheel', (event) => {
            if (!game.playerIsAlive()) {
                return
            }

            if (event.deltaY > 0) {
                equip = 2
            } else {
                equip = 1
            }
        })
        document.addEventListener('keydown', function (event) {
            event.preventDefault()

            switch (event.code) {
                case 'Tab':
                    hud.showScore()
                    break;
            }

            if (!game.playerIsAlive()) {
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
                case 'Tab':
                    hud.showScore()
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

            if (!game.playerIsAlive()) {
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
                    equip = 0;
                    break;
                case 'Digit1':
                    equip = 1;
                    break;
                case 'Digit2':
                    equip = 2;
                    break;
                case 'CapsLock':
                case 'ControlLeft':
                    standing = true;
                    break;
                case 'ShiftLeft':
                    running = true;
                    break;
                case 'Tab':
                    hud.hideScore()
                    break;
            }
        });

        this.#localPlayerActions = function () {
            let serverAction = []

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
            if (equip !== false) {
                if (game.equip(equip)) {
                    serverAction.push('equip ' + equip)
                }
                equip = false
            }

            let horizontal, vertical
            [horizontal, vertical] = threeHorizontalRotationToServer(pointer.getObject().rotation)
            if (shoot) {
                serverAction.push(shootLookAt)
                serverAction.push('attack')
                shoot = false
            } else {
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

    getTickAction() {
        return this.#localPlayerActions()
    }
}