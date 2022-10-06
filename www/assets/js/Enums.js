export {
    EventList,
    GameOverReason,
    PauseReason,
    Color,
    ColorNames,
    RoundEndReasonIcon,
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
