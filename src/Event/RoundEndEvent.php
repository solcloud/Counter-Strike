<?php

namespace cs\Event;

use cs\Core\Game;
use cs\Enum\RoundEndReason;

final class RoundEndEvent extends TickEvent
{
    public function __construct(private Game $game, public readonly bool $attackersWins, public readonly RoundEndReason $reason)
    {
        parent::__construct(function (): void {
            $this->game->endRound($this);
        });
    }

    public function serialize(): array
    {
        return [
            'attackersWins'  => $this->attackersWins,
            'round'          => $this->game->getRoundNumber(),
            'scoreAttackers' => $this->game->getScore()->getScoreAttackers(),
            'scoreDefenders' => $this->game->getScore()->getScoreDefenders(),
        ];
    }

}
