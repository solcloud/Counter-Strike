import {EventProcessor} from "./EventProcessor.js";
import {Player} from "./Player.js";
import {SoundType} from "./Enums.js";
import {SoundRepository} from "./SoundRepository.js";

export class Game {
    #world
    #hud
    #stats
    #pointer
    #round = 1
    #paused = false
    #started = false
    #options = false
    #readyCallback
    #endCallback
    #soundRepository
    #hudDebounceTicks = 1
    #bombTimerId = null;
    eventProcessor
    score = null
    alivePlayers = [0, 0]
    buyList = []
    players = []
    playerMe = null
    playerSpectate = null

    constructor(world, hud, stats) {
        this.#world = world
        this.#hud = hud
        this.#stats = stats
        this.eventProcessor = new EventProcessor(this)
        this.#soundRepository = new SoundRepository()
    }

    pause(msg, score, timeMs) {
        console.log("Pause: " + msg + " for " + timeMs + "ms")
        const game = this
        this.players.forEach(function (player) {
            if (player.getId() === game.playerMe.getId()) {
                if (game.playerSpectate.getId() !== game.playerMe.getId()) { // reset spectate camera to our player
                    const camera = game.#world.getCamera()
                    camera.removeFromParent()
                    player.get3DObject().getObjectByName('head').add(camera)
                    game.playerSpectate = game.playerMe
                }
            } else {
                player.get3DObject().visible = true // respawn (show) all beside me
            }
        })
        if (!this.#started) {
            this.gameStartOrHalfTimeOrEnd()
            this.#started = true
        }
        this.#paused = true
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
        this.gameStartOrHalfTimeOrEnd()
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
        clearInterval(this.#bombTimerId)
        this.#world.removeBomb()

        let winner = attackersWins ? 'Attackers' : 'Defenders'
        console.log("Round " + this.#round + " ended. Round wins: " + winner)
        this.score = score;
        this.#round = newRoundNumber
        this.#hud.displayTopMessage(winner + ' wins')
        this.#hud.requestFullScoreBoardUpdate(this.score)
    }

    gameStartOrHalfTimeOrEnd() {
        this.#world.playSound('538422__rosa-orenes256__referee-whistle-sound.wav', null, true)
    }

    playSound(data) {
        if (data.type === SoundType.ITEM_ATTACK && data.player === this.playerSpectate.getId()) {
            this.attackFeedback(data.item)
        }

        let soundName = this.#soundRepository.getSoundName(data.type, data.item, data.player, data.surface, this.playerSpectate.getId())
        if (!soundName) {
            console.warn("No song defined for", data)
            return
        }

        let myPlayerTypes = [SoundType.ITEM_RELOAD, SoundType.PLAYER_STEP, SoundType.ITEM_ATTACK, SoundType.ITEM_BUY, SoundType.BOMB_PLANTED]
        let myPlayerSound = (data.player && data.player === this.playerSpectate.getId() && myPlayerTypes.includes(data.type))
        this.#world.playSound(soundName, data.position, myPlayerSound)
    }

    bombPlanted(timeMs, position) {
        const world = this.#world
        this.#hud.bombPlanted(timeMs / 1000)
        world.spawnBomb(position)

        const tenSecWarningSecCount = timeMs / 1000 - 10
        let tickSecondsCount = 0;
        this.#bombTimerId = setInterval(function () {
            if (tickSecondsCount === tenSecWarningSecCount) {
                world.playSound('88532__northern87__woosh-northern87.wav', null, true)
            }
            world.playSound('536422__rudmer-rotteveel__setting-electronic-timer-1-beep.wav', position, false)
            tickSecondsCount++;
        }, 1000)
    }

    bombDefused() {
        clearInterval(this.#bombTimerId)
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
        deadPlayer.data.health = 0

        deadPlayer.get3DObject().visible = false
        this.alivePlayers[deadPlayer.getTeamIndex()]--

        this.#hud.showKill(
            culpritPlayer.data,
            deadPlayer.data,
            wasHeadshot,
            this.playerSpectate.data,
            killItemId
        )

        if (playerIdDead === this.playerSpectate.getId()) {
            this.spectatePlayer()
        }
    }

    spectatePlayer(directionNext = true) {
        if (this.playerMe.isAlive() || this.alivePlayers[this.playerMe.getTeamIndex()] === 0) {
            return
        }

        let teammates = this.getMyTeamPlayers()
        if (!directionNext) {
            teammates = [...teammates].reverse()
        }

        const startId = this.playerSpectate.getId()
        let match = teammates.find((player) => startId > player.getId() && player.isAlive())
        if (!match) {
            match = teammates.find((player) => player.isAlive())
        }

        const camera = this.#world.getCamera()
        camera.removeFromParent()
        match.get3DObject().getObjectByName('head').add(camera)
        this.playerSpectate = match
    }

    createPlayer(data) {
        const player = new Player(data, this.#world.spawnPlayer(data.color, this.playerMe.isAttacker() !== data.isAttacker))
        if (this.players[data.id]) {
            throw new Error('Player already exist with id ' + data.id)
        }
        this.players[data.id] = player
        return player
    }

    attackFeedback(item) {
        if (this.playerSpectate.data.ammo > 0) {
            this.#hud.showShot()
        }
    }

    equip(slotId) {
        if (!this.playerSpectate.data.slots[slotId]) {
            return false
        }

        this.playerSpectate.equip(slotId)
        this.#hud.equip(slotId, this.playerSpectate.data.slots)
        return true
    }

    tick(state) {
        this.#stats.begin()
        const game = this

        state.events.forEach(function (event) {
            game.eventProcessor.process(event)
        })

        if (this.#options === false) {
            return
        }

        state.players.forEach(function (playerState) {
            let player = game.players[playerState.id]
            if (player === undefined) {
                player = game.createPlayer(playerState)
            }

            player.get3DObject().getObjectByName('head').position.y = playerState.heightSight
            player.get3DObject().position.set(playerState.position.x, playerState.position.y, -1 * (playerState.position.z))

            game.updatePlayerData(player, playerState)
        })

        this.#render()
        this.#stats.end()
    }

    updatePlayerData(player, serverState) {
        if (player.data.isAttacker === this.playerMe.data.isAttacker) { // if player on my team
            if (player.data.money !== serverState.money) {
                this.#hud.updateMyTeamPlayerMoney(player.data, serverState.money)
            }
            player.updateData(serverState)
        } else {
            player.data.item = serverState.item
            player.data.isAttacker = serverState.isAttacker
        }

        if (this.playerMe.getId() === serverState.id || this.playerSpectate.getId() === serverState.id) {
            if (this.playerSpectate.isInventoryChanged(serverState)) {
                this.equip(serverState.item.slot)
            }
        } else {
            this.updateOtherPlayersModels(player.get3DObject(), serverState)
        }
    }

    updateOtherPlayersModels(playerObject, data) {
        playerObject.rotation.y = serverHorizontalRotationToThreeRadian(data.look.horizontal)

        const body = playerObject.getObjectByName('body')
        if (body.position.y !== data.heightBody) { // update body height position if changed
            body.position.y = data.heightBody
        }
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

    setPointer(pointer) {
        this.#pointer = pointer
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
        if (this.#pointer.isLocked) {
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
        this.#world.render()
    }
}
