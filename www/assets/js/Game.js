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
    eventProcessor
    score = null
    alivePlayers = [0, 0]
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
        this.#hud.updateRoundsHistory(this.score)
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
        this.#hud.updateRoundsHistory(this.score)
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
        const deadPlayer = this.players[playerIdDead];
        deadPlayer.get3DObject().visible = false
        this.alivePlayers[deadPlayer.getTeamIndex()]--
        // TODO update scoreboard player row

        this.#hud.showKill(
            this.players[playerIdCulprit].data,
            deadPlayer.data,
            wasHeadshot,
            this.playerMe.data,
            killItemId
        )
    }

    createPlayer(data) {
        const player = new Player(data, this.#world.spawnPlayer(data.id, data.color, this.playerMe.isAttacker !== data.isAttacker))
        if (this.players[data.id]) {
            throw new Error('Player already exist with id ' + data.id)
        }
        this.players[data.id] = player
        return player
    }

    attack() {
        // TODO attack feedback (audiovisual)
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

        if (state.events.length) {
            state.events.forEach(function (event) {
                game.eventProcessor.process(event)
            })
        }

        if (this.#options === false) {
            return
        }

        state.players.forEach(function (playerState) {
            let player = game.players[playerState.id]
            if (player === undefined) {
                player = game.createPlayer(playerState)
            }

            const player3DObject = player.get3DObject()
            player3DObject.getObjectByName('head').position.y = playerState.heightSight
            player3DObject.position.set(playerState.position.x, playerState.position.y, -1 * (playerState.position.z))

            if (game.playerMe.getId() === playerState.id) {
                player.updateData(playerState)
                if (game.playerMe.getEquippedSlotId() !== playerState.item.slot) {
                    game.equip(playerState.item.slot)
                }
            } else {
                player.data.item = playerState.item
                player.data.isAttacker = playerState.isAttacker
                game.updateOtherPlayersModels(player3DObject, playerState)
            }
        })

        this.render()
    }

    meIsAlive() {
        return this.playerMe.isAlive()
    }

    updateOtherPlayersModels(playerObject, data) {
        playerObject.rotation.y = serverRotationToThreeRadian(data.look.horizontal)
        this.#world.updatePlayerModel(playerObject, data)
    }

    render() {
        this.#stats.begin()
        if (this.#started) {
            this.#hud.updateHud(this.playerMe.data) // TODO check performance and if heavy debounce to every x tick
        }
        this.#world.render()
        this.#stats.end()
    }
}
