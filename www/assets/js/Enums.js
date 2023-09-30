export {
    EventList,
    GameOverReason,
    PauseReason,
    Color,
    ColorNames,
    RoundEndReasonIcon,
    SoundType,
    ArmorType,
    ArmorTypeIcon,
    BuyMenuItem,
    InventorySlot,
    ItemId,
    ItemIdToName,
    ItemIdToIcon,
    Action,
}

// server/src/Event/EventList.php
const EventList = {
    unknown: 0,
    GameOverEvent: 1,
    PauseStartEvent: 2,
    PauseEndEvent: 3,
    RoundStartEvent: 4,
    RoundEndEvent: 5,
    GameStartEvent: 6,
    RoundEndCoolDownEvent: 7,
    KillEvent: 8,
    SoundEvent: 9,
    PlantEvent: 10,
    ThrowEvent: 11,
    DropEvent: 12,
}

// server/src/Enum/GameOverReason.php
const GameOverReason = {
    REASON_NOT_ALL_PLAYERS_CONNECTED: 1,
    ATTACKERS_WINS: 2,
    DEFENDERS_WINS: 3,
    TIE: 4,
    ATTACKERS_SURRENDER: 5,
    DEFENDERS_SURRENDER: 6,
    SERVER_ERROR: 9,
}

// server/src/Enum/PauseReason.php
const PauseReason = {
    FREEZE_TIME: 1,
    TIMEOUT_ATTACKERS: 2,
    TIMEOUT_DEFENDERS: 3,
    HALF_TIME: 4,
}

// server/src/Enum/Color.php
const Color = {
    1: 0x0000FF,
    2: 0x00FF00,
    3: 0xFFFF00,
    4: 0x800080,
    5: 0xFFA500,
}

// server/src/Enum/Color.php
const ColorNames = {
    1: 'Blue',
    2: 'Green',
    3: 'Yellow',
    4: 'Purple',
    5: 'Orange',
}

// server/src/Enum/RoundEndReason.php
const RoundEndReasonIcon = {
    0: '☠',
    1: '⏱',
    2: '✂',
    3: '💣',
}

// server/src/Enum/SoundType.php
const SoundType = {
    PLAYER_GROUND_TOUCH: 0,
    PLAYER_STEP: 1,
    ITEM_ATTACK: 2,
    ITEM_ATTACK2: 3,
    ITEM_BUY: 5,
    BULLET_HIT: 6,
    PLAYER_DEAD: 7,
    ITEM_RELOAD: 8,
    BULLET_HIT_HEADSHOT: 9,
    ATTACK_NO_AMMO: 10,
    BOMB_PLANTED: 11,
    BOMB_PLANTING: 12,
    BOMB_EXPLODED: 13,
    BOMB_DEFUSING: 14,
    BOMB_DEFUSED: 15,
    ITEM_PICKUP: 16,
    GRENADE_LAND: 17,
    GRENADE_BOUNCE: 18,
    GRENADE_AIR: 19,
    ITEM_DROP_AIR: 20,
    ITEM_DROP_LAND: 21,
}

// server/src/Enum/ArmorType.php
const ArmorType = {
    NONE: 0,
    BODY: 1,
    BODY_AND_HEAD: 2,
}

// server/src/Enum/ArmorType.php
const ArmorTypeIcon = {
    0: '♢',
    1: '🛡️',
    2: '⛑️',
}

// server/src/Enum/BuyMenuItem.php
const BuyMenuItem = {
    RIFLE_AK: 1,
    GRENADE_FLASH: 2,
    GRENADE_SMOKE: 3,
    GRENADE_MOLOTOV: 4,
    GRENADE_HE: 5,
    GRENADE_DECOY: 6,
    RIFLE_M4A4: 7,
    PISTOL_USP: 8,
    PISTOL_P250: 9,
    KEVLAR_BODY_AND_HEAD: 10,
    PISTOL_GLOCK: 11,
    KEVLAR_BODY: 12,
    GRENADE_INCENDIARY: 13,
    DEFUSE_KIT: 14,
    RIFLE_AWP: 15,
}

// server/src/Enum/InventorySlot.php
const InventorySlot = {
    SLOT_KNIFE: 0,
    SLOT_PRIMARY: 1,
    SLOT_SECONDARY: 2,
    SLOT_BOMB: 3,
    SLOT_GRENADE_DECOY: 4,
    SLOT_GRENADE_MOLOTOV: 5,
    SLOT_GRENADE_SMOKE: 6,
    SLOT_GRENADE_FLASH: 7,
    SLOT_GRENADE_HE: 8,
    SLOT_TASER: 9,
    SLOT_KEVLAR: 10,
    SLOT_KIT: 11,
}

// server/src/Enum/ItemId.php
const ItemId = {
    SolidSurface: 0,
    Knife: 1,
    PistolGlock: 2,
    PistolP250: 3,
    PistolUsp: 4,
    RifleAk: 5,
    RifleM4A4: 6,
    RifleAWP: 7,
    Decoy: 30,
    Flashbang: 31,
    HighExplosive: 32,
    Incendiary: 33,
    Kevlar: 34,
    Molotov: 35,
    Smoke: 36,
    Bomb: 50,
    DefuseKit: 51,
}

const ItemIdToName = {
    0: 'Wall',
    1: 'Knife',
    2: 'Glock',
    3: 'P-250',
    4: 'USP',
    5: 'AK-47',
    6: 'M4-A1',
    7: 'AWP',
    30: 'Decoy',
    31: 'Flashbang',
    32: 'High explosive',
    33: 'Incendiary',
    34: 'Kevlar',
    35: 'Molotov',
    36: 'Smoke',
    50: 'Bomb',
    51: 'Defuse Kit',
}

const ItemIdToIcon = {
    0: '\uE000',
    1: '\uE03B',
    2: '\uE004',
    3: '\uE013',
    4: '\uE00E',
    5: '\uE007',
    6: '\uE00E',
    7: '\uE009',
    30: '\uE02F',
    31: '\uE02B',
    32: '\uE02C',
    33: '\uE030',
    34: '\uE064',
    35: '\uE02E',
    36: '\uE02D',
    50: '\uE031',
    51: '\uE066',
}

// PlayerAction.js
const Action = {
    MOVE_FORWARD: 'forward',
    MOVE_LEFT: 'left',
    MOVE_BACK: 'back',
    MOVE_RIGHT: 'right',
    JUMP: 'jump',
    CROUCH: 'crouch',
    WALK: 'walk',
    RELOAD: 'reload',
    EQUIP_KNIFE: 'equip_knife',
    EQUIP_PRIMARY: 'equip_primary',
    EQUIP_SECONDARY: 'equip_secondary',
    EQUIP_BOMB: 'equip_bomb',
    EQUIP_SMOKE: 'equip_smoke',
    EQUIP_FLASH: 'equip_flash',
    EQUIP_HE: 'equip_he',
    EQUIP_MOLOTOV: 'equip_molotov',
    EQUIP_DECOY: 'equip_decoy',
    BUY_MENU: 'buy_menu',
    GAME_MENU: 'game_menu',
    SCORE_BOARD: 'score_board',
    DROP: 'drop',
    DROP_BOMB: 'drop_bomb',
    SWITCH_HANDS: 'switch_hands',
    USE: 'use',
    CLEAR_DECALS: 'clear_decals',
}
