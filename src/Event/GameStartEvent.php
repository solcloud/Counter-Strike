<?php

namespace cs\Event;

use cs\Core\GameProperty;
use cs\Core\Player;
use cs\Net\ServerSetting;

final class GameStartEvent extends TickEvent
{

    public function __construct(
        private Player        $player,
        private ServerSetting $setting,
        private GameProperty  $gameSetting,
    )
    {
    }

    public function serialize(): array
    {
        return [
            'playerId'     => $this->player->getId(),
            'warmupSec'    => $this->setting->warmupWaitSecRemains,
            'tickMs'       => $this->setting->tickMs,
            'playersCount' => $this->setting->playersMax,
            'setting'      => $this->gameSetting->toArray(),
            'player'       => $this->player->serialize(),
        ];
    }

}
