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
    #throwables = [];
    #roundIntervalIds = [];
    score = null
    bombDropPosition = null
    alivePlayers = [0, 0]
    buyList = []
    players = []
    playerMe = null
    playerSpectate = null
    #playerSlotsVisibleModels = [
        InventorySlot.SLOT_KNIFE,
        InventorySlot.SLOT_PRIMARY,
        InventorySlot.SLOT_SECONDARY,
        InventorySlot.SLOT_BOMB,
        InventorySlot.SLOT_GRENADE_DECOY,
        InventorySlot.SLOT_GRENADE_MOLOTOV,
        InventorySlot.SLOT_GRENADE_SMOKE,
        InventorySlot.SLOT_GRENADE_FLASH,
        InventorySlot.SLOT_GRENADE_HE,
        InventorySlot.SLOT_TASER,
        InventorySlot.SLOT_KIT,
    ]

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
        this.#roundIntervalIds.forEach((id) => clearInterval(id))
        this.#roundIntervalIds = []
        Object.keys(this.#throwables).forEach((id) => this.removeGrenade(id))
        this.#throwables = []
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
            data.position.y = cameraPosition.y + (Math.sign(data.extra.origin.y - cameraPosition.y) * 3)
            data.position.z = cameraPosition.z + rotate[1]
        } else {
            this.#world.bulletPlayerHit(data.position, wasHeadshot)
        }
    }

    processSound(data) {
        const spectatorId = this.playerSpectate.getId()
        if (data.type === SoundType.ITEM_ATTACK) {
            this.#world.itemAttack(this.players[data.player], data.item, (data.player === spectatorId))
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
        if (data.type === SoundType.BOMB_DEFUSED || data.type === SoundType.BOMB_EXPLODED) {
            clearInterval(this.#bombTimerId)
        }
        if (data.type === SoundType.GRENADE_AIR || data.type === SoundType.GRENADE_BOUNCE || data.type === SoundType.GRENADE_LAND) {
            const grenade = this.#throwables[data.extra.id];
            grenade.rotation.x += 0.1
            grenade.rotation.y += 0.1
            grenade.rotation.z += 0.1
            grenade.position.set(data.position.x, data.position.y, -data.position.z)

            if (data.type === SoundType.GRENADE_LAND) {
                grenade.rotation.set(0, 0, 0)
                this.#grenadeLand(data.extra.id, data.item, data.player, data.position)
            }
        }

        this.#soundRepository.play(data, spectatorId, this.#tick)
    }

    #grenadeLand(throwableId, item, playerId, position) {
        const game = this
        if (item.slot === InventorySlot.SLOT_GRENADE_DECOY) {
            const player = this.players[playerId]
            const soundItem = player.data.slots[InventorySlot.SLOT_PRIMARY] ? player.data.slots[InventorySlot.SLOT_PRIMARY] : (player.data.slots[InventorySlot.SLOT_SECONDARY] ? player.data.slots[InventorySlot.SLOT_SECONDARY] : player.data.slots[InventorySlot.SLOT_KNIFE])
            const soundName = this.#soundRepository.getItemAttackSound(soundItem)

            const world = this.#world
            let endTime = Date.now() + 15 * 1E3
            const callback = function () {
                world.playSound(soundName, position, false)
                if (Date.now() > endTime) {
                    game.removeGrenade(throwableId)
                    return
                }
                game.#roundIntervalIds.push(setTimeout(callback, Math.random() * 1000))
            }
            game.#roundIntervalIds.push(setTimeout(callback, 100))
            return
        }
        if (item.slot === InventorySlot.SLOT_GRENADE_FLASH) {
            const grenade = this.#throwables[throwableId].getObjectByName('collider')
            const sight = this.playerSpectate.get3DObject().getObjectByName('sight')
            const sightPosition = sight.getWorldPosition(new THREE.Vector3())
            const direction = grenade.getWorldPosition(new THREE.Vector3()).sub(sightPosition).normalize()
            if (this.#world.getCamera().getWorldDirection(new THREE.Vector3()).dot(direction) <= 0) { // flash behind spectator
                this.removeGrenade(throwableId)
                return;
            }

            const ray = new THREE.Raycaster(sightPosition, direction)
            const intersects = ray.intersectObjects([grenade, ...this.#world.getMapObjects()], false);
            if (intersects.length >= 1 && intersects[0].object === grenade) {
                this.#hud.showFlashBangScreen()
            }
            this.removeGrenade(throwableId)
            return;
        }
        if (item.slot === InventorySlot.SLOT_GRENADE_SMOKE) {
            const grenade = this.#throwables[throwableId]
            const smoke = new THREE.Mesh(new THREE.DodecahedronGeometry(300, 1), new THREE.MeshStandardMaterial({color: 0xdadada}))
            smoke.material.side = THREE.DoubleSide
            smoke.position.y = 150
            grenade.add(smoke)
            game.#roundIntervalIds.push(setTimeout(() => this.removeGrenade(throwableId), 18000))
            return;
        }

        console.warn("No handler for grenade: ", item)
        game.#roundIntervalIds.push(setTimeout(() => this.removeGrenade(throwableId), 1000)) // todo responsive volumetric smokes, flashes, fire etc.
    }

    spawnGrenade(item, id, radius) {
        this.#throwables[id] = this.#world.spawnGrenade(item, radius)
    }

    removeGrenade(id) {
        const grenade = this.#throwables[id]
        this.#world.destroyObject(grenade)
        delete this.#throwables[id]
    }

    bombPlanted(timeMs, position) {
        const world = this.#world
        world.spawnBomb(position)
        this.bombDropPosition = position

        const bombSecCount = Math.ceil(timeMs / 1000)
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
        camera.rotation.set(0, 0, 0)

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
        model.children.forEach((root) => root.visible = false)
        model.getObjectByName('pov').visible = true
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
        playerObject.getObjectByName('sight').rotation.x = serverVerticalRotationToThreeRadian(data.look.vertical)

        const hand = playerObject.getObjectByName('hand')
        if (hand.children.length) {
            hand.children[0].rotation.y = serverVerticalRotationToThreeRadian(Math.max(Math.min(data.look.vertical, 50), -50)) // cap hand item vertical look rotation
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
            lastHandItemModel.rotation.set(0, 0, 0)
            belt.getObjectByName(`slot-${lastHandItemModel.userData.slot}`).add(lastHandItemModel)
        } else if (hand.children.length > 1) {
            throw new Error("Too many items in hands?")
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
            itemModel.children.forEach((root) => root.visible = false)
            itemModel.getObjectByName('item').visible = true
            itemModel.visible = true
        })

        const modelInHand = belt.getObjectByName(`slot-${data.item.slot}`).getObjectByName(`item-${data.item.id}`)
        hand.add(modelInHand)
        modelInHand.userData.slot = data.item.slot
        modelInHand.children.forEach((root) => root.visible = false)
        modelInHand.getObjectByName('item').visible = true
        modelInHand.visible = true
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
