import {EventProcessor} from "./EventProcessor.js";

export class Game {
    #paused = false
    #round = 1
    #started = false
    #options = false
    #readyCallback
    #endCallback
    #hud
    #world
    eventProcessor
    score = {
        attackers: 0,
        defenders: 0
    }
    alivePlayers = {
        attackers: 0,
        defenders: 0
    }
    players = []
    playerMe = {}

    constructor(world, hud) {
        this.#world = world
        this.#hud = hud
        this.eventProcessor = new EventProcessor(this)
    }

    pause(msg, timeMs) {
        const game = this
        this.players.forEach(function (player) {
            if (player.data.id === game.playerMe.id) {
                return
            }
            player.object.visible = true
        })
        this.#started = true
        this.#paused = true
        console.log("Pause: " + msg + " for " + timeMs + "ms")
        this.#hud.pause(msg, timeMs)
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
        this.alivePlayers.attackers = aliveAttackers
        this.alivePlayers.defenders = aliveDefenders
        this.#hud.clearAlerts()
        this.#hud.roundStart(this.#options.setting.round_time_ms)
    }

    roundEnd(attackersWins, round) {
        let winner = attackersWins ? 'Attackers' : 'Defenders'
        console.log("Round " + this.#round + " ended. Round wins: " + winner)
        this.#round = round
        this.#hud.displayTopMessage(winner + ' wins')
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

        this.playerMe = options.player
        this.players[playerId] = this.#spawnPlayerMe(playerId, options.player.color, options.player.isAttacker)

        if (this.#readyCallback) {
            this.#readyCallback(this.#options)
        }
    }

    playerKilled(playerIdDead, playerIdCulprit, wasHeadshot, killItemId) {
        this.players[playerIdDead].object.visible = false
        this.#hud.showKill(
            this.players[playerIdCulprit].data,
            this.players[playerIdDead].data,
            wasHeadshot,
            this.playerMe,
            killItemId
        )
    }

    #spawnPlayerMe(id, colorIndex, isAttacker) {
        const me = this.#world.createPlayerMe()
        return {
            object: me,
            data: {
                id: id,
                color: colorIndex,
                isAttacker: isAttacker
            }
        }
    }

    spawnPlayer(id, colorIndex, isAttacker) {
        const player = this.#world.spawnPlayer(id, colorIndex, this.playerMe.isAttacker !== isAttacker)
        this.players[id] = {
            object: player,
            data: {
                id: id,
                color: colorIndex,
                isAttacker: isAttacker
            }
        }
        return this.players[id]
    }

    equip(slotId) {
        if (!this.playerMe.slots[slotId]) {
            return false
        }

        this.#hud.equip(slotId)
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
                player = game.spawnPlayer(playerState.id, playerState.color, playerState.isAttacker)
            }
            player.data.isAttacker = playerState.isAttacker
            player = player.object

            player.getObjectByName('head').position.y = playerState.heightSight
            player.position.set(playerState.position.x, playerState.position.y, -1 * (playerState.position.z))

            if (game.playerMe.id && playerState.id !== game.playerMe.id) {
                game.updatePlayerObject(player, playerState)
            }
            if (playerState.id === game.playerMe.id) {
                game.playerMe.money = playerState.money
                game.playerMe.health = playerState.health
                game.playerMe.item = playerState.item
                game.playerMe.slots = playerState.slots
                game.playerMe.ammo = playerState.ammo
                game.playerMe.ammoReserve = playerState.ammoReserve
            }
        })

        this.render()
    }

    playerIsAlive() {
        return this.playerMe.health > 0
    }

    updatePlayerObject(playerObject, data) {
        playerObject.rotation.y = serverRotationToThreeRadian(data.look.horizontal)
        this.#world.updatePlayerModel(playerObject, data)
    }

    render() {
        this.#hud.updateHud(this.playerMe)
        this.#world.render()
    }
}
