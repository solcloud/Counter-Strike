import {InventorySlot, SoundType} from "./Enums.js";

export class SoundRepository {

    getSoundName(type, item, playerId, surfaceStrength, playerSpectateId) {
        if (type === SoundType.PLAYER_STEP) {
            if (playerId === playerSpectateId) {
                return '422990__dkiller2204__sfxrunground1.wav'
            }
            return '221626__moodpie__body-impact.wav'
        }

        if (type === SoundType.ITEM_ATTACK) {
            if (item.slot === InventorySlot.SLOT_SECONDARY) {
                return '387480__cosmicembers__dart-thud-2.wav'
            } else if (item.slot === InventorySlot.SLOT_PRIMARY) {
                return '513421__pomeroyjoshua__anu-clap-09.wav'
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
            if (surfaceStrength) {
                if (surfaceStrength > 2000) {
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

        if (type === SoundType.ITEM_DROP) {
            return '12734__leady__dropping-a-gun.wav'
        }

        if (type === SoundType.ATTACK_NO_AMMO) {
            if (item.slot === InventorySlot.SLOT_SECONDARY) {
                return '323403__gosfx__sound-1.mp3'
            } else if (item.slot === InventorySlot.SLOT_PRIMARY) {
                return '448987__matrixxx__weapon-ready.wav'
            }
            return '369009__flying-deer-fx__hit-01-mouth-fx-impact-with-object.wav'
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

        return null
    }

}
