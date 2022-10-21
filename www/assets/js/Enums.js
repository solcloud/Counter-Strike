export {
    EventList,
    GameOverReason,
    PauseReason,
    Color,
    ColorNames,
    RoundEndReasonIcon,
    SoundType,
    BuyMenuItem,
    InventorySlot,
    ItemId,
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
    ITEM_DROP: 4,
    ITEM_BUY: 5,
    BULLET_HIT: 6,
    PLAYER_DEAD: 7,
    ITEM_RELOAD: 8,
    BULLET_HIT_HEADSHOT: 9,
    ATTACK_NO_AMMO: 10,
    BOMB_PLANTED: 11,
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
    Decoy: 30,
    Flashbang: 31,
    HighExplosive: 32,
    Incendiary: 33,
    Kevlar: 34,
    Molotov: 35,
    Smoke: 36,
}
