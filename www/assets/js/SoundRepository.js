import {InventorySlot, SoundType, ItemId, GrenadeSlots} from "./Enums.js";

export class SoundRepository {
    #alwaysInHeadTypes = [
        SoundType.BOMB_PLANTED, SoundType.BOMB_DEFUSED,
    ]
    #myPlayerTypes = [
        SoundType.ITEM_RELOAD, SoundType.ITEM_PICKUP, SoundType.ITEM_ATTACK, SoundType.ITEM_ATTACK2, SoundType.ITEM_BUY,
        SoundType.PLAYER_STEP, SoundType.ATTACK_NO_AMMO,
        SoundType.BOMB_PLANTING, SoundType.BOMB_DEFUSING,
    ]
    #soundPlayer
    #lastSpectatorMoveSoundTick = 0
    #lastOtherPlayerMoveSoundTick = 0

    constructor(soundPlayer) {
        this.#soundPlayer = soundPlayer
    }

    play(data, spectatorId, tickId) {
        let soundName = this.#getSoundName(data.type, data.item, data.player, data.surface, spectatorId, tickId)
        if (!soundName) {
            return
        }

        let inPlayerSpectateHead = (this.#alwaysInHeadTypes.includes(data.type) || (data.player && data.player === spectatorId && this.#myPlayerTypes.includes(data.type)))
        this.#soundPlayer(soundName, data.position, inPlayerSpectateHead)
    }

    #getSoundName(type, item, playerId, surface, playerSpectateId, tickId) {
        if (type === SoundType.PLAYER_STEP) {
            if (playerId === playerSpectateId) {
                if (tickId > this.#lastSpectatorMoveSoundTick + msToTick(400)) {
                    this.#lastSpectatorMoveSoundTick = tickId
                    return '422990__dkiller2204__sfxrunground1.wav'
                }
                return null
            }
            if (tickId > this.#lastOtherPlayerMoveSoundTick + msToTick(300)) {
                this.#lastOtherPlayerMoveSoundTick = tickId
                return '221626__moodpie__body-impact.wav'
            }
            return null
        }

        if (type === SoundType.ITEM_ATTACK) {
            return this.getItemAttackSound(item)
        }
        if (type === SoundType.ITEM_ATTACK2) {
            if (item.slot === InventorySlot.SLOT_KNIFE) {
                return '435238__aris621__nasty-knife-stab.wav'
            }
            return '558117__abdrtar__move.mp3'
        }

        if (type === SoundType.ITEM_RELOAD) {
            if (item.slot === InventorySlot.SLOT_SECONDARY) {
                return '618047__mono832__reload.mp3'
            }
            // shotgun 621155__ktfreesound__reload-escopeta-m7.wav
            return '15545__lagthenoggin__reload.mp3'
        }

        if (type === SoundType.BULLET_HIT_HEADSHOT) {
            return (playerId === playerSpectateId) ? '249821__spookymodem__weapon-blow.wav' : '632704__adh-dreaming__fly-on-the-wall-snare.wav'
        }

        if (type === SoundType.PLAYER_DEAD) {
            return '387161__rdaly95__hero-death.wav'
        }

        if (type === SoundType.BULLET_HIT) {
            if (surface) {
                if (surface.force > 1000) {
                    return '51381__robinhood76__00119-trzepak-3.wav'
                }
                return '108737__branrainey__boing.wav'
            } else if (playerId) {
                return '512138__beezlefm__item-sound.wav'
            }
        }

        if (type === SoundType.PLAYER_GROUND_TOUCH) {
            return '211500__taira-komori__knocking-wall.mp3'
        }

        if (type === SoundType.ITEM_DROP_AIR) {
            return null
        }
        if (type === SoundType.ITEM_DROP_LAND) {
            return '12734__leady__dropping-a-gun.wav'
        }

        if (type === SoundType.ATTACK_NO_AMMO) {
            if (item.slot === InventorySlot.SLOT_SECONDARY) {
                return '323403__gosfx__sound-1.mp3'
            }
            if (item.slot === InventorySlot.SLOT_PRIMARY) {
                return '448987__matrixxx__weapon-ready.wav'
            }
            return '369009__flying-deer-fx__hit-01-mouth-fx-impact-with-object.wav'
        }

        if (type === SoundType.BOMB_DEFUSING) {
            if (playerId === playerSpectateId) {
                return '162900__qubodup__scissors-snap.flac'
            }
            return '349662__insanity54__c4armed.ogg'
        }
        if (type === SoundType.BOMB_DEFUSED) {
            return '338133__spectre-of-pain__bombdefused.mp3'
        }
        if (type === SoundType.BOMB_PLANTING) {
            if (playerId === playerSpectateId) {
                return '536422__rudmer-rotteveel__setting-electronic-timer-1-beep.wav'
            }
            return '36106__jak-damage__digi-code-door-uncatch.wav'
        }
        if (type === SoundType.BOMB_PLANTED) {
            return '555042__bittermelonheart__soccer-ball-kick.wav'
        }
        if (type === SoundType.BOMB_EXPLODED) {
            return '209984__zimbot__explosionbombblastambiente.wav'
        }

        if (type === SoundType.ITEM_BUY) {
            return '434781__stephenbist__luggage-drop-1.wav'
        }
        if (type === SoundType.ITEM_PICKUP) {
            if (playerId === playerSpectateId || item.slot === InventorySlot.SLOT_BOMB) {
                return '434781__stephenbist__luggage-drop-1.wav'
            }
            return null
        }

        if (type === SoundType.FLAME_PLAYER_HIT) {
            return '512138__beezlefm__item-sound.wav'
        }
        if (type === SoundType.FLAME_SPAWN || type === SoundType.FLAME_EXTINGUISH || type === SoundType.SMOKE_SPAWN || type === SoundType.SMOKE_FADE) { // fixme make some noise
            return null
        }
        if (type === SoundType.GRENADE_AIR) {
            return '575509__awildfilli__granada_tiro.wav'
        }
        if (type === SoundType.GRENADE_BOUNCE) {
            return '471642__puerta118m__bomb-grenade-shot-at-enemy.wav'
        }
        if (type === SoundType.GRENADE_LAND) {
            return '151077__vabadus__m16a2-with-m203-fires-a-m406-he-round.wav'
        }

        console.warn("No song defined for", arguments)
        return null
    }

    getItemAttackSound(item) {
        if (item.id === ItemId.RifleAWP) {
            return '371574__matrixxx__rifle-gun-tikka-t3-tactical-shot-04.wav'
        }

        if (item.slot === InventorySlot.SLOT_SECONDARY) {
            return '387480__cosmicembers__dart-thud-2.wav'
        }
        if (item.slot === InventorySlot.SLOT_PRIMARY) {
            return '513421__pomeroyjoshua__anu-clap-09.wav'
        }
        if (item.slot === InventorySlot.SLOT_KNIFE) {
            return '240788__f4ngy__knife-hitting-wood.wav'
        }
        if (GrenadeSlots.includes(item.slot)) {
            return '163458__lemudcrab__grenade-launcher.wav'
        }
        return '558117__abdrtar__move.mp3'
    }

}
