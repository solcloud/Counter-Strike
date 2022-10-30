import {EventList, GameOverReason, PauseReason} from "./Enums.js";

export class EventProcessor {
    #callbacks

    constructor(game) {
        this.#loadCallbacks(game)
    }

    process(event) {
        let callback = this.#callbacks[event.code];
        if (callback === undefined) {
            console.log("Unknown event callback for event.code " + event.code)
            return false
        }
        callback(event.data)
        return true
    }

    #loadCallbacks(game) {
        const eventsCallback = {}

        eventsCallback[EventList.unknown] = function (data) {
            console.log("Common server, fix yourself, event 0 - unknown")
            console.log(data)
        }

        eventsCallback[EventList.GameOverEvent] = function (data) {
            let gameOverReason = data.reason
            let msg = ''
            switch (gameOverReason) {
                case GameOverReason.REASON_NOT_ALL_PLAYERS_CONNECTED:
                    msg = "Not all players connected during warmup"
                    break
                case GameOverReason.ATTACKERS_WINS:
                    msg = "Attackers wins!"
                    break
                case GameOverReason.DEFENDERS_WINS:
                    msg = "Defenders wins!"
                    break
                case GameOverReason.TIE:
                    msg = "Max round reached. Tie!"
                    break
                case GameOverReason.SERVER_ERROR:
                    msg = "Server error!"
                    break
            }
            game.end(msg)
        }

        eventsCallback[EventList.PauseStartEvent] = function (data) {
            let pauseReason = data.reason
            let msg = ''
            switch (pauseReason) {
                case PauseReason.FREEZE_TIME:
                    msg = "Freeze time"
                    break
                case PauseReason.TIMEOUT_ATTACKERS:
                    msg = "Timeout attackers"
                    break
                case PauseReason.TIMEOUT_DEFENDERS:
                    msg = "Timeout defenders"
                    break
                case PauseReason.HALF_TIME:
                    game.gameStartOrHalfTimeOrEnd()
                    msg = "Half time"
                    break
            }
            game.pause(msg, data.score, data.ms)
        }

        eventsCallback[EventList.PauseEndEvent] = function () {
            game.unpause()
        }

        eventsCallback[EventList.RoundStartEvent] = function (data) {
            game.roundStart(data.attackers, data.defenders)
        }

        eventsCallback[EventList.RoundEndEvent] = function (data) {
            game.roundEnd(data.attackersWins, data.newRoundNumber, data.score)
        }

        eventsCallback[EventList.GameStartEvent] = function (options) {
            game.gameStart(options)
        }

        eventsCallback[EventList.RoundEndCoolDownEvent] = function () {
            // do nothing, maybe show MVP and play music kit
        }

        eventsCallback[EventList.KillEvent] = function (data) {
            game.playerKilled(data.playerDead, data.playerCulprit, data.headshot, data.itemId)
        }

        eventsCallback[EventList.SoundEvent] = function (data) {
            game.playSound(data)
        }

        eventsCallback[EventList.PlantEvent] = function (data) {
            game.bombPlanted(data.timeMs, data.position)
        }

        this.#callbacks = eventsCallback
    }
}
