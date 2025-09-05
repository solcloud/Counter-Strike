import * as THREE from 'three' // fixme try remove from game, maybe only allow import Vec3...
import {EventProcessor} from "./EventProcessor.js"
import {Player} from "./Player.js"
import {InventorySlot, SoundType} from "./Enums.js"
import {SoundRepository} from "./SoundRepository.js"
import {Utils} from "./Utils.js";

export class Game {
    #world
    #hud
    #stats
    #pointer
    #setting
    #playerAction
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
    #bombTimerId = null
    #eventProcessor
    #dropItems = {}
    #throwables = {}
    #volumetrics = {}
    #roundIntervalIds = []
    #roundDamage = {did: {}, got: {}}
    #playerSlotsVisibleModels = [
        InventorySlot.SLOT_KNIFE, InventorySlot.SLOT_PRIMARY, InventorySlot.SLOT_SECONDARY,
        InventorySlot.SLOT_BOMB, InventorySlot.SLOT_GRENADE_DECOY, InventorySlot.SLOT_GRENADE_MOLOTOV,
        InventorySlot.SLOT_GRENADE_SMOKE, InventorySlot.SLOT_GRENADE_FLASH, InventorySlot.SLOT_GRENADE_HE,
        InventorySlot.SLOT_TASER, InventorySlot.SLOT_KIT,
    ]
    score = null
    bombDropPosition = null
    alivePlayers = [0, 0]
    buyList = []
    players = []
    playerMe = null
    playerSpectate = null

    constructor(world, hud, stats) {
        this.#world = world
        this.#hud = hud
        this.#stats = stats
        this.#eventProcessor = new EventProcessor(this)
        this.#soundRepository = new SoundRepository((...args) => world.playSound(...args))
    }

    #roundReset() {
        clearInterval(this.#bombTimerId)
        this.#roundIntervalIds.forEach((id) => clearInterval(id))
        this.#roundIntervalIds = []
        Object.keys(this.#dropItems).forEach((id) => this.itemPickUp(id))
        Object.keys(this.#throwables).forEach((id) => this.removeGrenade(id))
        Object.keys(this.#volumetrics).forEach((groupId) => Object.keys(this.#volumetrics[groupId]).forEach((itemId) => this.#world.destroyObject(this.#volumetrics[groupId][itemId])))
        this.#volumetrics = {}
        this.#world.reset()
    }

    pause(msg, score, timeMs) {
        this.#paused = true
        console.log("Pause: " + msg + " for " + timeMs + "ms")
        this.#roundReset()

        const game = this
        this.players.forEach(function (player) {
            if (player.getId() === game.playerMe.getId()) { // reset spectate camera to our player
                const camera = game.#world.getCamera()
                camera.rotation.set(0, Utils.serverHorizontalRotationToThreeRadian(player.data.look.horizontal), 0)
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
        this.#hud.updateRoundDamage(null)
        this.#roundDamage = {did: {}, got: {}}
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
        this.score = score
        this.#round = newRoundNumber
        this.#hud.displayTopMessage(winner + ' wins')
        this.#hud.requestFullScoreBoardUpdate(this.score)
        this.#hud.updateRoundDamage(this.#roundDamage, this.getEnemyPlayers())
    }

    halfTime() {
        this.#gameStartOrHalfTimeOrEnd()
    }

    #gameStartOrHalfTimeOrEnd() {
        this.#world.playSound('538422__rosa-orenes256__referee-whistle-sound.wav', null, true)
    }

    playerHit(data, wasHeadshot) {
        const playerHitId = data.player
        if (playerHitId === this.playerSpectate.getId()) {
            const anglePlayer = Math.round(this.getPlayerSpectateRotation()[0])
            const camera = this.#world.getCamera()

            const cameraPosition = new THREE.Vector3()
            camera.getWorldPosition(cameraPosition)
            cameraPosition.z = Math.abs(cameraPosition.z)

            const angleHit = Utils.radianToDegree(Math.atan2(data.extra.origin.x - cameraPosition.x, data.extra.origin.z - cameraPosition.z))
            const delta = Utils.smallestDeltaAngle(anglePlayer, angleHit)
            this.#hud.spectatorHit(
                (delta < -30 && delta > -150),
                (delta > 30 && delta < 150),
                (Math.abs(delta) <= 40),
                (Math.abs(delta) >= 120),
            )

            // Update hit position for better audio feedback
            const rotate = Utils.rotatePointY(angleHit, 0, 10)
            data.position.x = cameraPosition.x + rotate[0]
            data.position.y = cameraPosition.y + (Math.sign(data.extra.origin.y - cameraPosition.y) * 3)
            data.position.z = cameraPosition.z + rotate[1]
        } else {
            this.#world.bulletPlayerHit(data.position, wasHeadshot)
        }

        const damage = data.extra.damage
        const myId = this.playerMe.getId()
        const attackerId = data.extra.shooter
        if (playerHitId === myId) {
            if (!this.#roundDamage.got[attackerId]) {
                this.#roundDamage.got[attackerId] = []
            }
            this.#roundDamage.got[attackerId].push(damage)
        } else if (attackerId === myId) {
            if (!this.#roundDamage.did[playerHitId]) {
                this.#roundDamage.did[playerHitId] = []
            }
            this.#roundDamage.did[playerHitId].push(damage)
        }
    }

    processSound(data) {
        const spectatorId = this.playerSpectate.getId()
        if (data.type === SoundType.ITEM_ATTACK) {
            this.#world.itemAttack(this.players[data.player], data.item, (data.player === spectatorId))
        }
        if (data.type === SoundType.BULLET_HIT) {
            if (data.player) {
                this.playerHit(data, false)
            } else {
                this.#world.bulletWallHit(data.extra.origin, data.position, data.item)
            }
        } else if (data.type === SoundType.BULLET_HIT_HEADSHOT) {
            this.playerHit(data, true)
        }
        if (data.type === SoundType.FLAME_PLAYER_HIT) {
            this.playerHit(data, false)
        }
        if (data.type === SoundType.ITEM_PICKUP) {
            this.itemPickUp(data.extra.id)
        }
        if (data.type === SoundType.FLAME_SPAWN) {
            this.spawnFlame(data.position, data.extra.height, data.extra.id, `${data.position.x}-${data.position.y}-${data.position.z}`)
        }
        if (data.type === SoundType.SMOKE_SPAWN) {
            this.spawnSmoke(data.position, data.extra.height, data.extra.id, `${data.position.x}-${data.position.y}-${data.position.z}`)
        }
        if (data.type === SoundType.FLAME_EXTINGUISH) {
            this.destroyFlame(data.extra.id, `${data.position.x}-${data.position.y}-${data.position.z}`)
        }
        if (data.type === SoundType.SMOKE_FADE) {
            this.smokeFade(data.extra.id)
        }
        if (data.type === SoundType.ITEM_DROP_AIR) {
            const item = this.#dropItems[data.extra.id]
            item.rotation.x -= 0.1
            item.rotation.y -= 0.1
            item.rotation.z -= 0.1
            item.position.set(data.position.x, data.position.y, -data.position.z)
        }
        if (data.type === SoundType.ITEM_DROP_LAND) {
            if (data.item.slot === InventorySlot.SLOT_BOMB) {
                this.bombDropPosition = data.position
            }
            const item = this.#dropItems[data.extra.id]
            item.rotation.set(0, 0, 0)
            item.rotateOnWorldAxis(new THREE.Vector3(0, 1, 0), Math.random() * 6.28)
            item.position.set(data.position.x, data.position.y, -data.position.z)
        }
        if (data.type === SoundType.BOMB_DEFUSED || data.type === SoundType.BOMB_EXPLODED) {
            clearInterval(this.#bombTimerId)
        }
        if (data.type === SoundType.GRENADE_AIR || data.type === SoundType.GRENADE_BOUNCE || data.type === SoundType.GRENADE_LAND) {
            const grenade = this.#throwables[data.extra.id]
            grenade.rotation.x += 0.1
            grenade.rotation.y += 0.1
            grenade.rotation.z += 0.1
            grenade.position.set(data.position.x, data.position.y, -data.position.z)

            if (data.type === SoundType.GRENADE_LAND) {
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
                return
            }

            const ray = new THREE.Raycaster(sightPosition, direction)
            ray.layers.set(Utils.LAYER_WORLD)
            grenade.layers.set(Utils.LAYER_WORLD)
            const intersects = ray.intersectObjects([grenade, this.getScene()])
            if (intersects.length >= 1 && intersects[0].object === grenade) {
                this.#hud.showFlashBangScreen()
            }
            this.removeGrenade(throwableId)
            return
        }
        if (item.slot === InventorySlot.SLOT_GRENADE_HE) {
            this.removeGrenade(throwableId) // fixme add some cool effect
            return
        }

        game.#roundIntervalIds.push(setTimeout(() => this.removeGrenade(throwableId), 500))
    }

    itemDrop(item, id) {
        const model = this.#world.itemDropped(item)
        this.#dropItems[id] = model
    }

    itemPickUp(id) {
        const dropItem = this.#dropItems[id]
        this.#world.destroyObject(dropItem)
        delete this.#dropItems[id]
    }

    spawnGrenade(item, id, radius) {
        this.#throwables[id] = this.#world.spawnGrenade(item, radius)
    }

    removeGrenade(id) {
        const grenade = this.#throwables[id]
        this.#world.destroyObject(grenade)
        delete this.#throwables[id]
    }

    grillStart(fireId, position, size, maxTimeMs, maxPartCount) {
        this.#volumetrics[fireId] = {}
        this.#volumetrics[fireId]['size'] = size
        this.#world.playSound('338301_4811732-lq.mp3', position, false)
    }

    spawnFlame(point, height, fireId, partId) {
        const size = this.#volumetrics[fireId]['size']
        height = Utils.lerp(height, Utils.randomInt(16, 26), Math.min(Math.sqrt(Object.keys(this.#volumetrics[fireId]).length) / Utils.randomInt(7, 9), 1))
        this.#volumetrics[fireId][partId] = this.#world.spawnFlame(point, size, height)
    }

    destroyFlame(fireId, flameId) {
        const flame = this.#volumetrics[fireId][flameId]
        this.#world.destroyObject(flame)
        delete this.#volumetrics[fireId][flameId]
    }

    smokeStart(smokeId, position, size, maxTimeMs, maxPartCount) {
        this.#volumetrics[smokeId] = {'mesh': this.#world.initSmoke(smokeId, size, maxPartCount)}
    }

    spawnSmoke(point, height, smokeId, partId) {
        this.#world.spawnSmoke(point, height, smokeId)
    }

    smokeFade(smokeId) {
        const game = this
        const intervalId = setInterval(function () {
            if (game.#world.fadeSmoke(smokeId)) {
                clearInterval(intervalId)
            }
        }, 50)
        this.#roundIntervalIds.push(intervalId)
    }

    bombPlanted(timeMs, position) {
        const world = this.#world
        world.spawnBomb(position)
        this.bombDropPosition = position

        const bombSecCount = Math.ceil(timeMs / 1000)
        this.#hud.bombPlanted(bombSecCount)

        const tenSecWarningSecCount = Math.round(timeMs / 1000 - 10)
        let tickSecondsCount = 0
        let bombTimerId = setInterval(function () {
            if (tickSecondsCount === bombSecCount) {
                clearInterval(bombTimerId)
            }
            if (tickSecondsCount === tenSecWarningSecCount) {
                world.playSound('88532__northern87__woosh-northern87.wav', null, true)
            }
            world.playSound('536422__rudmer-rotteveel__setting-electronic-timer-1-beep.wav', position, false)
            tickSecondsCount++
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
        Utils.tickMs = options.tickMs
        this.#roundHalfTime = Math.floor(options.setting.max_rounds / 2) + 1
        this.#hud.startWarmup(options.warmupSec * 1000)

        const playerId = options.playerId
        if (this.players[playerId]) {
            throw new Error("My Player is already set!")
        }

        this.playerMe = new Player(options.player, this.#world.createPlayerMe())
        this.players[playerId] = this.playerMe
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
            return
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
        this.#hud.changeSpectatePlayer(player)
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

        this.#hud.equip(slotId, item.id, this.playerSpectate.data.slots)
        return true
    }

    switchHands() {
        const povItem = this.#world.getCamera().getObjectByName('pov-item')
        povItem.scale.x *= -1
        povItem.position.x *= -1
    }

    clearDecals() {
        this.#world.clearDecals()
    }

    getScene() {
        return this.#world.getScene()
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

        this.#updateScopeState(player, serverState.scopeLevel)
        if (player.data.isAttacker === this.playerMe.data.isAttacker && player.data.money !== serverState.money) {
            this.#hud.updateMyTeamPlayerMoney(player.data, serverState.money)
        }
        player.updateData(serverState)

        if (this.playerSpectate.getId() === serverState.id && this.playerSpectate.isInventoryChanged(serverState)) {
            this.equip(serverState.item.slot)
        }
        if (this.playerMe.getId() !== serverState.id) {
            this.updateOtherPlayersModels(player, serverState)
        }
    }

    updateOtherPlayersModels(player, data) {
        const playerObject = player.get3DObject()
        playerObject.rotation.y = Utils.serverHorizontalRotationToThreeRadian(data.look.horizontal)
        playerObject.getObjectByName('sight').rotation.x = Utils.serverVerticalRotationToThreeRadian(data.look.vertical)

        const hand = playerObject.getObjectByName('hand')
        if (hand.children.length) {
            hand.children[0].rotation.y = Utils.serverVerticalRotationToThreeRadian(Math.max(Math.min(data.look.vertical, 50), -50)) // cap hand item vertical look rotation
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
        const belt = player.get3DObject().getObjectByName('belt')

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

    #updateScopeState(player, scopeLevel) {
        const isPlayerSpectate = (this.playerSpectate.getId() === player.getId())

        if (isPlayerSpectate) {
            this.#hud.updateCrossHair(scopeLevel, this.playerSpectate.data.item.id)
            scopeLevel > 0 && this.#hud.scopeBlur((this.#playerAction.isMoving() && !this.#playerAction.isCrouching()) ? 3 : 0)
        }
        if (player.data.scopeLevel === scopeLevel) {
            return
        }

        if (scopeLevel > 0) {
            this.#world.playSound('210018__supakid13__sniper-scope-zoom-in.wav', player.getSightPosition(), isPlayerSpectate)
        }
        if (isPlayerSpectate) {
            const isNotScopedIn = (scopeLevel === 0)
            this.#world.getCamera().getObjectByName('pov-item').visible = isNotScopedIn
            this.#world.updateCameraZoom(Utils.scopeLevelToZoom(scopeLevel))
            if (this.meIsAlive()) {
                this.#pointer.pointerSpeed = (isNotScopedIn ? this.#setting.getSensitivity() : this.#setting.getInScopeSensitivity() / scopeLevel)
            }
        }
    }

    getMyTeamPlayers() {
        let meIsAttacker = this.playerMe.isAttacker()
        return this.players.filter((player) => player.isAttacker() === meIsAttacker)
    }

    getEnemyPlayers() {
        let meIsAttacker = this.playerMe.isAttacker()
        return this.players.filter((player) => player.isAttacker() !== meIsAttacker)
    }

    meIsAlive() {
        return this.playerMe.isAlive()
    }

    meIsSpectating() {
        return (!this.meIsAlive())
    }

    setDependency(pointer, setting, action) {
        this.#pointer = pointer
        this.#setting = setting
        this.#playerAction = action
        this.#shouldRenderInsideTick = setting.shouldMatchServerFps()
    }

    getPlayerMeRotation() {
        return Utils.threeRotationToServer(this.#pointer.getObject().rotation)
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
            this.#hudDebounceTicks = Utils.msToTick(40)
            this.#hud.updateHud(this.playerSpectate.data)
        }
        if (this.#shouldRenderInsideTick) {
            this.#world.render()
        }
    }
}
