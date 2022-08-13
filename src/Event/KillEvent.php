<?php

namespace cs\Event;

use cs\Core\Player;

final class KillEvent extends TickEvent
{

    public function __construct(
        private Player  $playerDead,
        private Player $playerCulprit,
        private string  $attackItemId,
        private bool    $headShot,
    )
    {
    }

    public function getPlayerDead(): Player
    {
        return $this->playerDead;
    }

    public function getPlayerCulprit(): Player
    {
        return $this->playerCulprit;
    }

    public function wasHeadShot(): bool
    {
        return $this->headShot;
    }

    public function getAttackItemId(): string
    {
        return $this->attackItemId;
    }

    public function serialize(): array
    {
        return [
            'playerDead'    => $this->getPlayerDead()->getId(),
            'playerCulprit' => $this->getPlayerCulprit()->getId(),
            'itemId'        => $this->getAttackItemId(),
            'headshot'      => $this->wasHeadShot(),
        ];
    }

}
