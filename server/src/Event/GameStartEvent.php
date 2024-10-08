<?php

namespace cs\Event;

use cs\Core\GameProperty;
use cs\Core\Player;
use cs\Net\ServerSetting;

final class GameStartEvent extends NoTickEvent
{

    public function __construct(
        private readonly Player        $player,
        private readonly ServerSetting $setting,
        private readonly GameProperty  $gameSetting,
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
