import {Action} from "./Enums.js";

export class Setting {
    #setting = {
        fov: 70,
        radarZoom: 0.9,
        sprayTriggerDeltaMs: 80,
        preferPerformance: false,
        bind: {
            'w': Action.MOVE_FORWARD,
            'a': Action.MOVE_LEFT,
            's': Action.MOVE_BACK,
            'd': Action.MOVE_RIGHT,
            ' ': Action.JUMP,
            'Control': Action.CROUCH,
            'Shift': Action.WALK,
            'r': Action.RELOAD,
            'q': Action.EQUIP_KNIFE,
            '1': Action.EQUIP_PRIMARY,
            '2': Action.EQUIP_SECONDARY,
            '5': Action.EQUIP_BOMB,
            'b': Action.BUY_MENU,
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
