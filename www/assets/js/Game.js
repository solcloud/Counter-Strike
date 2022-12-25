import {EventProcessor} from "./EventProcessor.js";
import {Player} from "./Player.js";
import {InventorySlot, SoundType} from "./Enums.js";
import {SoundRepository} from "./SoundRepository.js";

export class Game {
    #world
    #hud
    #stats
    #pointer
    #shouldRenderInsideTick
    #tick = 0
    #round = 1
    #roundHalfTime = 2
    #paused = false
    #started = false
    #options = null
    #readyCallback
    #endCallback
    #soundRepository
    #hudDebounceTicks = 1
    #bombTimerId = null;
    #eventProcessor
    score = null
    bombDropPosition = null
    alivePlayers = [0, 0]
    buyList = []
    players = []
    playerMe = null
    playerSpectate = null
    #playerSlotsVisibleModels = [InventorySlot.SLOT_KNIFE, InventorySlot.SLOT_BOMB, InventorySlot.SLOT_PRIMARY, InventorySlot.SLOT_SECONDARY, InventorySlot.SLOT_KIT]

    constructor(world, hud, stats) {
        this.#world = world
        this.#hud = hud
        this.#stats = stats
        this.#eventProcessor = new EventProcessor(this)
        this.#soundRepository = new SoundRepository((...args) => world.playSound(...args))
    }

    pause(msg, score, timeMs) {
        this.#paused = true
        console.log("Pause: " + msg + " for " + timeMs + "ms")
        clearInterval(this.#bombTimerId)
        this.#world.reset()

        const game = this
        this.players.forEach(function (player) {
            if (player.getId() === game.playerMe.getId()) { // reset spectate camera to our player
                const camera = game.#world.getCamera()
                camera.rotation.set(0, serverHorizontalRotationToThreeRadian(player.data.look.horizontal), 0)
                if (game.#pointer) {
                    game.#pointer.reset()
                }
                player.get3DObject().getObjectByName('sight').add(camera)
                game.playerSpectate = game.playerMe
                game.requestPointerLock()
            } else {
                player.get3DObject().getObjectByName('figure').visible = true
            }
            player.respawn()
        })
        if (!this.#started) {
            this.#gameStartOrHalfTimeOrEnd()
            this.#started = true
        }
        if (this.#roundHalfTime === this.#round + 1) {
            this.#world.playSound('voice/blanka-last_round_of_half.mp3', null, true)
        }
        this.score = score
        this.#hud.pause(msg, timeMs)
        this.#hud.requestFullScoreBoardUpdate(this.score)
    }

    unpause() {
        this.#paused = false
        this.#hud.clearTopMessage()
        console.log("Game unpause")
    }

    end(msg) {
        console.log('Game ended')
        this.#gameStartOrHalfTimeOrEnd()
        if (this.#endCallback) {
            this.#endCallback(msg)
        }
    }

    roundStart(aliveAttackers, aliveDefenders) {
        console.log("Starting round " + this.#round)
        this.alivePlayers[0] = aliveDefenders
        this.alivePlayers[1] = aliveAttackers
        this.#hud.clearAlerts()
        this.#hud.roundStart(this.#options.setting.round_time_ms)
    }

    roundEnd(attackersWins, newRoundNumber, score) {
        let winner = attackersWins ? 'Attackers' : 'Defenders'
        console.log("Round " + this.#round + " ended. Round wins: " + winner)
        this.score = score;
        this.#round = newRoundNumber
        this.#hud.displayTopMessage(winner + ' wins')
        this.#hud.requestFullScoreBoardUpdate(this.score)
    }

    halfTime() {
        this.#gameStartOrHalfTimeOrEnd()
    }

    #gameStartOrHalfTimeOrEnd() {
        this.#world.playSound('538422__rosa-orenes256__referee-whistle-sound.wav', null, true)
    }

    playerHit(data, wasHeadshot) {
        if (data.player === this.playerSpectate.getId()) {
            const anglePlayer = Math.round(this.getPlayerSpectateRotation()[0])
            const camera = this.#world.getCamera()

            const cameraPosition = new THREE.Vector3()
            camera.getWorldPosition(cameraPosition)
            cameraPosition.z = Math.abs(cameraPosition.z)

            const angleHit = radianToDegree(Math.atan2(data.extra.origin.x - cameraPosition.x, data.extra.origin.z - cameraPosition.z))
            const delta = smallestDeltaAngle(anglePlayer, angleHit)
            this.#hud.spectatorHit(
                (delta < -30 && delta > -150),
                (delta > 30 && delta < 150),
                (Math.abs(delta) <= 40),
                (Math.abs(delta) >= 120),
            )

            // Update hit position for better audio feedback
            const rotate = rotatePointY(angleHit, 0, 10)
            data.position.x = cameraPosition.x + rotate[0]
            data.position.y = cameraPosition.y + (Math.sign(data.extra.origin.y - cameraPosition.y) * 2)
            data.position.z = cameraPosition.z + rotate[1]
        } else {
            this.#world.bulletPlayerHit(data.position, wasHeadshot)
        }
    }

    processSound(data) {
        const spectatorId = this.playerSpectate.getId()
        if (data.type === SoundType.ITEM_ATTACK && data.player === spectatorId) {
            this.attackFeedback(data.item)
        }
        if (data.type === SoundType.ITEM_PICKUP) {
            this.#world.itemPickup(data.position, data.item, (spectatorId === data.player))
        }
        if (data.type === SoundType.BULLET_HIT) {
            if (data.player) {
                this.playerHit(data, false)
            } else if (data.surface && (data.item.slot === InventorySlot.SLOT_PRIMARY || data.item.slot === InventorySlot.SLOT_SECONDARY)) {
                this.#world.bulletWallHit(data.position, data.surface, (data.item.slot === InventorySlot.SLOT_PRIMARY ? 1.5 : 1.1))
            }
        } else if (data.type === SoundType.BULLET_HIT_HEADSHOT) {
            this.playerHit(data, true)
        }
        if (data.type === SoundType.ITEM_DROP) {
            this.#world.itemDrop(data.position, data.item)
            if (data.player === spectatorId) {
                this.dropFeedback(data.item)
            }
            if (data.item.slot === InventorySlot.SLOT_BOMB) {
                this.bombDropPosition = data.position
            }
        }
        if (data.type === SoundType.BOMB_DEFUSED) {
            clearInterval(this.#bombTimerId)
        }

        this.#soundRepository.play(data, spectatorId, this.#tick)
    }

    bombPlanted(timeMs, position) {
        const world = this.#world
        world.spawnBomb(position)
        this.bombDropPosition = position

        const bombSecCount = Math.round(timeMs / 1000)
        this.#hud.bombPlanted(bombSecCount)

        const tenSecWarningSecCount = Math.round(timeMs / 1000 - 10)
        let tickSecondsCount = 0;
        let bombTimerId = setInterval(function () {
            if (tickSecondsCount === bombSecCount) {
                clearInterval(bombTimerId)
            }
            if (tickSecondsCount === tenSecWarningSecCount) {
                world.playSound('88532__northern87__woosh-northern87.wav', null, true)
            }
            world.playSound('536422__rudmer-rotteveel__setting-electronic-timer-1-beep.wav', position, false)
            tickSecondsCount++;
        }, 1000)
        this.#bombTimerId = bombTimerId
    }

    getRoundNumber() {
        return this.#round
    }

    isPaused() {
        return this.#paused
    }

    isPlaying() {
        return this.#started
    }

    onReady(callback) {
        this.#readyCallback = callback
    }

    onEnd(callback) {
        this.#endCallback = callback
    }

    gameStart(options) {
        this.#options = options
        window._csfGlobal.tickMs = options.tickMs
        this.#roundHalfTime = Math.floor(options.setting.max_rounds / 2) + 1
        this.#hud.startWarmup(options.warmupSec * 1000)

        const playerId = options.playerId
        if (this.players[playerId]) {
            throw new Error("My Player is already set!")
        }

        this.playerMe = new Player(options.player, this.#world.createPlayerMe())
        this.players[playerId] = this.playerMe;
        this.playerSpectate = this.playerMe

        if (this.#readyCallback) {
            this.#readyCallback(this.#options)
        }
    }

    playerKilled(playerIdDead, playerIdCulprit, wasHeadshot, killItemId) {
        const culpritPlayer = this.players[playerIdCulprit]
        const deadPlayer = this.players[playerIdDead]

        deadPlayer.died()
        this.alivePlayers[deadPlayer.getTeamIndex()]--

        this.#hud.showKill(
            culpritPlayer.data,
            deadPlayer.data,
            wasHeadshot,
            this.playerMe.data,
            killItemId
        )

        if (playerIdDead === this.playerSpectate.getId()) {
            this.requestPointerUnLock()
            this.spectatePlayer()
        }
    }

    spectatePlayer(directionNext = true) {
        if (this.playerMe.isAlive() || this.alivePlayers[this.playerMe.getTeamIndex()] === 0) {
            return
        }

        const myId = this.playerSpectate.getId()
        let aliveAvailableSpectateMates = this.getMyTeamPlayers().filter((player) => player.isAlive() && myId !== player.getId())
        if (aliveAvailableSpectateMates.length === 0) {
            return;
        }

        let ids = aliveAvailableSpectateMates.map((player) => player.getId()).sort()
        if (!directionNext) {
            ids.reverse()
        }

        let playerId = ids.find((id) => myId > id)
        if (!playerId) {
            playerId = ids.shift()
        }

        const camera = this.#world.getCamera()
        camera.rotation.set(0, degreeToRadian(-90), 0)

        const player = this.players[playerId]
        player.get3DObject().getObjectByName('sight').add(camera)
        player.get3DObject().getObjectByName('figure').visible = false
        if (this.playerSpectate.isAlive()) {
            this.playerSpectate.get3DObject().getObjectByName('figure').visible = true
        }
        this.playerSpectate = player
        this.equip(player.getEquippedSlotId())
    }

    createPlayer(data) {
        if (this.players[data.id]) {
            throw new Error('Player already exist with id ' + data.id)
        }

        const player = new Player(data)
        this.#world.spawnPlayer(player, this.playerMe.isAttacker() !== data.isAttacker)
        this.players[data.id] = player
        return player
    }

    attackFeedback(item) {
        if (this.playerSpectate.data.ammo > 0) {
            this.#hud.showShot(item)
        }
    }

    dropFeedback(item) {
        this.#hud.showDropAnimation(item)
    }

    equip(slotId) {
        if (!this.playerSpectate.data.slots[slotId]) {
            return false
        }

        this.playerSpectate.equip(slotId)
        const item = this.playerSpectate.data.slots[slotId]
        const povItems = this.#world.getCamera().getObjectByName('pov-item')
        povItems.children.forEach((mesh) => mesh.visible = false)

        let model = povItems.getObjectByName(`item-${item.id}`)
        if (!model) {
            model = this.#world.getModelForItem(item)
            povItems.add(model)
        }
        model.position.set(0, 0, 0)
        model.rotation.set(0, 0, 0)
        model.visible = true

        this.#hud.equip(slotId, this.playerSpectate.data.slots)
        return true
    }

    tick(state) {
        this.#stats.begin()
        this.#tick++
        const game = this

        if (this.#options !== null) {
            state.players.forEach(function (serverState) {
                let player = game.players[serverState.id]
                if (player === undefined) {
                    player = game.createPlayer(serverState)
                }
                game.updatePlayerData(player, serverState)
            })
        }
        state.events.forEach(function (event) {
            game.#eventProcessor.process(event)
        })

        this.#render()
        this.#stats.end()
    }

    updatePlayerData(player, serverState) {
        player.get3DObject().getObjectByName('sight').position.y = serverState.sight
        player.get3DObject().position.set(serverState.position.x, serverState.position.y, -serverState.position.z)

        if (player.data.isAttacker === this.playerMe.data.isAttacker) { // if player on my team
            if (player.data.money !== serverState.money) {
                this.#hud.updateMyTeamPlayerMoney(player.data, serverState.money)
            }
            player.updateData(serverState)
        } else {
            player.data.item = serverState.item
            player.data.sight = serverState.sight
            player.data.isAttacker = serverState.isAttacker
        }

        if (this.playerMe.getId() === serverState.id || this.playerSpectate.getId() === serverState.id) {
            if (this.playerSpectate.isInventoryChanged(serverState)) {
                this.equip(serverState.item.slot)
            }
        }
        if (this.playerMe.getId() !== serverState.id) {
            this.updateOtherPlayersModels(player, serverState)
        }
    }

    updateOtherPlayersModels(player, data) {
        const playerObject = player.get3DObject()
        playerObject.rotation.y = serverHorizontalRotationToThreeRadian(data.look.horizontal)

        const gunRotationVertical = (this.playerMe.isAlive() ? Math.max(Math.min(data.look.vertical, 50), -50) : data.look.vertical)
        const hand = playerObject.getObjectByName('hand')
        if (hand.children.length) {
            hand.children[0].rotation.y = serverVerticalRotationToThreeRadian(gunRotationVertical)
        }

        player.animate()
        if (player.isInventoryChanged(data)) {
            this.#otherPlayersInventoryChanged(player, data)
            player.equip(data.item.slot)
        }
    }

    #otherPlayersInventoryChanged(player, data) {
        const world = this.#world
        const hand = player.get3DObject().getObjectByName('hand')
        const belt = player.get3DObject().getObjectByName('belt');

        if (hand.children.length === 1) {
            const lastHandItemModel = hand.children[0]
            belt.getObjectByName(`slot-${lastHandItemModel.userData.slot}`).add(lastHandItemModel)
        } else if (hand.children.length > 1) {
            throw new Error("Too many item in hands?")
        }

        this.#playerSlotsVisibleModels.forEach(function (slotId) {
            const item = data.slots[slotId]
            const beltSlot = belt.getObjectByName(`slot-${slotId}`)
            beltSlot.children.forEach((model) => model.visible = false)
            if (!item) { // do not have slotID filled
                return
            }

            let itemModel = beltSlot.getObjectByName(`item-${item.id}`)
            if (!itemModel) {
                itemModel = world.getModelForItem(item)
                beltSlot.add(itemModel)
            }

            itemModel.position.set(0, 0, 0)
            itemModel.rotation.set(0, 0, 0)
            itemModel.visible = true
        })

        const modelInHand = belt.getObjectByName(`slot-${data.item.slot}`).getObjectByName(`item-${data.item.id}`)
        hand.add(modelInHand)
        modelInHand.position.set(0, 0, 0)
        modelInHand.rotation.set(0, 0, 0)
        modelInHand.visible = true
        modelInHand.userData.slot = data.item.slot
    }

    getMyTeamPlayers() {
        let meIsAttacker = this.playerMe.isAttacker()
        return this.players.filter((player) => player.isAttacker() === meIsAttacker)
    }

    meIsAlive() {
        return this.playerMe.isAlive()
    }

    meIsSpectating() {
        return (!this.meIsAlive())
    }

    setDependency(pointer, renderWorldInsideTick) {
        this.#pointer = pointer
        this.#shouldRenderInsideTick = renderWorldInsideTick
    }

    getPlayerMeRotation() {
        return threeRotationToServer(this.#pointer.getObject().rotation)
    }

    getPlayerSpectateRotation() {
        if (this.playerSpectate.getId() === this.playerMe.getId()) {
            return this.getPlayerMeRotation()
        }
        return [this.playerSpectate.data.look.horizontal, this.playerSpectate.data.look.vertical]
    }

    requestPointerLock() {
        if (this.#pointer.isLocked || (this.playerMe && this.playerMe.getId() !== this.playerSpectate.getId())) {
            return
        }
        this.#pointer.lock()
    }

    requestPointerUnLock() {
        if (!this.#pointer.isLocked) {
            return
        }
        this.#pointer.unlock()
    }

    #render() {
        if (this.#started && --this.#hudDebounceTicks === 0) {
            this.#hudDebounceTicks = 4
            this.#hud.updateHud(this.playerSpectate.data)
        }
        if (this.#shouldRenderInsideTick) {
            this.#world.render()
        }
    }
}
