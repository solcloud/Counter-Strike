import {EventProcessor} from "./EventProcessor.js";
import {Player} from "./Player.js";

export class Game {
    #round = 1
    #paused = false
    #started = false
    #options = false
    #readyCallback
    #endCallback
    #hud
    #stats
    #world
    #hudDebounceTicks = 1
    eventProcessor
    score = null
    alivePlayers = [0, 0]
    buyList = []
    players = []
    playerMe = null

    constructor(world, hud, stats) {
        this.#world = world
        this.#hud = hud
        this.#stats = stats
        this.eventProcessor = new EventProcessor(this)
    }

    pause(msg, score, timeMs) {
        console.log("Pause: " + msg + " for " + timeMs + "ms")
        const game = this
        this.players.forEach(function (player) {
            if (player.getId() !== game.playerMe.getId()) {
                player.get3DObject().visible = true // respawn (show) all beside me
            }
        })
        this.#started = true
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

    playSound(data) {
        let soundPath = './resources/sound/pong.mp3' // TODO
        this.#world.playSound(soundPath, data.position)
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

    setOptions(options) {
        this.#options = options
        this.#hud.startWarmup(options.warmupSec * 1000)

        const playerId = options.playerId
        if (this.players[playerId]) {
            throw new Error("My Player is already set!")
        }

        this.playerMe = new Player(options.player, this.#world.createPlayerMe())
        this.players[playerId] = this.playerMe;

        if (this.#readyCallback) {
            this.#readyCallback(this.#options)
        }
    }

    playerKilled(playerIdDead, playerIdCulprit, wasHeadshot, killItemId) {
        const culpritPlayer = this.players[playerIdCulprit];
        const deadPlayer = this.players[playerIdDead];

        deadPlayer.get3DObject().visible = false
        this.alivePlayers[deadPlayer.getTeamIndex()]--

        this.#hud.showKill(
            culpritPlayer.data,
            deadPlayer.data,
            wasHeadshot,
            this.playerMe.data,
            killItemId
        )
    }

    createPlayer(data) {
        const player = new Player(data, this.#world.spawnPlayer(data.color, this.playerMe.isAttacker !== data.isAttacker))
        if (this.players[data.id]) {
            throw new Error('Player already exist with id ' + data.id)
        }
        this.players[data.id] = player
        return player
    }

    attack() {
        this.#hud.showShot()
    }

    equip(slotId) {
        if (!this.playerMe.data.slots[slotId]) {
            return false
        }

        this.playerMe.equip(slotId)
        this.#hud.equip(slotId, this.playerMe.data.slots)
        return true
    }

    tick(state) {
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

        this.render()
    }

    updatePlayerData(player, serverState) {
        if (this.playerMe.getId() === serverState.id) { // if me
            if (this.playerMe.getEquippedSlotId() !== serverState.item.slot) {
                this.equip(serverState.item.slot)
            }
        }

        if (player.data.isAttacker === this.playerMe.data.isAttacker) { // if player on my team
            if (player.data.money !== serverState.money) {
                this.#hud.updateMyTeamPlayerMoney(player.data, serverState.money)
            }
            player.updateData(serverState)
        } else {
            player.data.item = serverState.item
            player.data.isAttacker = serverState.isAttacker
            this.updateOtherPlayersModels(player.get3DObject(), serverState)
        }
    }

    meIsAlive() {
        return this.playerMe.isAlive()
    }

    updateOtherPlayersModels(playerObject, data) {
        playerObject.rotation.y = serverRotationToThreeRadian(data.look.horizontal)

        const body = playerObject.getObjectByName('body')
        if (body.position.y !== data.heightBody) { // update body height position if changed
            // TODO probably keyframe time based animation from userData like body.position = body.userData.animation[data.playerAnimationId]
            body.position.y = data.heightBody
        }
    }

    render() {
        this.#stats.begin()
        if (this.#started && --this.#hudDebounceTicks === 0) {
            this.#hudDebounceTicks = 4
            this.#hud.updateHud(this.playerMe.data)
        }
        this.#world.render()
        this.#stats.end()
    }
}
