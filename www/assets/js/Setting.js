import {Action} from "./Enums.js";

export class Setting {
    #setting = {
        fov: 70,
        radarZoom: 0.9,
        sprayTriggerDeltaMs: 80,
        preferPerformance: false,
        bind: {
            'KeyW': Action.MOVE_FORWARD,
            'KeyA': Action.MOVE_LEFT,
            'KeyS': Action.MOVE_BACK,
            'KeyD': Action.MOVE_RIGHT,
            'KeyE': Action.USE,
            'Space': Action.JUMP,
            'ControlLeft': Action.CROUCH,
            'ShiftLeft': Action.WALK,
            'KeyR': Action.RELOAD,
            'KeyG': Action.DROP,
            'KeyQ': Action.EQUIP_KNIFE,
            'Digit1': Action.EQUIP_PRIMARY,
            'Digit2': Action.EQUIP_SECONDARY,
            'Digit5': Action.EQUIP_BOMB,
            'KeyB': Action.BUY_MENU,
            'Tab': Action.SCORE_BOARD,
        },
    }

    loadSettings(settingObject) {
        this.#setting = settingObject
    }

    serialize() {
        return JSON.stringify(this.#setting)
    }

    getBinds() {
        return this.#setting.bind
    }

    getSprayTriggerDeltaMs() {
        return this.#setting.sprayTriggerDeltaMs
    }

    getRadarZoom() {
        return this.#setting.radarZoom
    }

}
