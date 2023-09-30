import {Action, BuyMenuItem} from "./Enums.js";

export class Setting {
    #onUpdate = {}
    #setting = {
        base: {
            fov: 70,
            volume: 20,
            radarZoom: 0.9,
            sensitivity: 1.0,
            inScopeSensitivity: 0.5,
            sprayTriggerDeltaMs: 80,
            mouseClickTimeMs: 40,
            crosshair: '✛',
            crosshairColor: '#d31b1b',
            crosshairSize: 40,
            flashBangColor: '#FFFFFF',
            hudColor: '#d55151',
            hudColorShadow: '#626262',
            scopeSize: '2px',
            preferPerformance: false,
            showFps: true,
            matchServerFps: false,
            anisotropic: 16,
            exposure: 0.8,
        },
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
            'KeyF': Action.EQUIP_FLASH,
            'KeyZ': Action.EQUIP_MOLOTOV,
            'KeyX': Action.EQUIP_SMOKE,
            'KeyC': Action.EQUIP_HE,
            'KeyN': Action.EQUIP_DECOY,
            'KeyV': Action.EQUIP_KNIFE,
            'KeyT': Action.DROP_BOMB,
            'KeyH': Action.SWITCH_HANDS,
            'Digit1': Action.EQUIP_PRIMARY,
            'Digit2': Action.EQUIP_SECONDARY,
            'Digit3': Action.EQUIP_KNIFE,
            'Digit4': Action.EQUIP_BOMB,
            'Digit5': Action.EQUIP_BOMB,
            'KeyB': Action.BUY_MENU,
            'Tab': Action.SCORE_BOARD,
            'Backquote': Action.GAME_MENU,
            'Enter': Action.CLEAR_DECALS,
            'CapsLock': Action.CLEAR_DECALS,
            'ArrowLeft': `buy ${BuyMenuItem.RIFLE_AK},${BuyMenuItem.RIFLE_M4A4}`,
            'ArrowRight': `buy ${BuyMenuItem.RIFLE_AWP}`,
            'ArrowUp': `buy ${BuyMenuItem.DEFUSE_KIT}`,
            'ArrowDown': `buy ${BuyMenuItem.KEVLAR_BODY},${BuyMenuItem.KEVLAR_BODY_AND_HEAD}`,
            'Delete': `buy ${BuyMenuItem.KEVLAR_BODY}`,
            'End': `buy ${BuyMenuItem.PISTOL_P250}`,
            'Home': `buy ${BuyMenuItem.GRENADE_HE}`,
            'PageUp': `buy ${BuyMenuItem.GRENADE_FLASH}`,
            'PageDown': `buy ${BuyMenuItem.GRENADE_SMOKE}`,
            'Insert': `buy ${BuyMenuItem.GRENADE_MOLOTOV},${BuyMenuItem.GRENADE_INCENDIARY}`,
        },
    }

    constructor(settingString = null) {
        if (settingString) {
            this.loadSettings(settingString)
        }
    }

    loadSettings(json) {
        this.#setting = JSON.parse(json)
    }

    getSetting() {
        return JSON.parse(JSON.stringify(this.#setting))
    }

    getJson() {
        return JSON.stringify(this.#setting)
    }

    addUpdateCallback(key, callback) {
        this.#onUpdate[key] = callback
    }

    update(key, value) {
        const callback = this.#onUpdate[key]
        if (callback === undefined) {
            return
        }
        callback(value)
        this.#setting.base[key] = value
    }

    getBinds() {
        return this.#setting.bind
    }

    getSprayTriggerDeltaMs() {
        return this.#setting.base.sprayTriggerDeltaMs ?? 80
    }

    getMouseClickTimeMs() {
        return this.#setting.base.mouseClickTimeMs ?? 40
    }

    getRadarZoom() {
        return this.#setting.base.radarZoom ?? 0.9
    }

    getFieldOfView() {
        return this.#setting.base.fov ?? 70
    }

    getAnisotropicFiltering() {
        if (this.shouldPreferPerformance()) {
            return 1
        }
        return this.#setting.base.anisotropic ?? 16
    }

    shouldPreferPerformance() {
        return this.#setting.base.preferPerformance ?? false
    }

    shouldShowFps() {
        return this.#setting.base.showFps ?? true
    }

    shouldMatchServerFps() {
        return this.#setting.base.matchServerFps ?? false
    }

    getSensitivity() {
        return this.#setting.base.sensitivity ?? 1.0
    }

    getInScopeSensitivity() {
        return this.#setting.base.inScopeSensitivity ?? 0.5
    }

    getExposure() {
        return this.#setting.base.exposure ?? 0.8
    }

    getMasterVolume() {
        return this.#setting.base.volume ?? 20
    }

    getCrosshairSymbol() {
        return this.#setting.base.crosshair ?? '+'
    }

    getCrosshairColor() {
        return this.#setting.base.crosshairColor ?? '#d31b1b'
    }

    getCrosshairSize() {
        return this.#setting.base.crosshairSize ?? 40
    }

    getFlashBangColor() {
        return this.#setting.base.flashBangColor ?? '#FFFFFF';
    }

    getHudColor() {
        return this.#setting.base.hudColor ?? '#ff8e8e';
    }

    getHudColorShadow() {
        return this.#setting.base.hudColorShadow ?? '#626262';
    }

    getScopeSize() {
        return this.#setting.base.scopeSize ?? '2px';
    }
}
