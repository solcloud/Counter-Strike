<?php

namespace cs\Event;

use cs\Core\Game;
use cs\Enum\RoundEndReason;

final class RoundEndEvent extends TickEvent
{
    public readonly int $roundNumberEnded;

    public function __construct(private Game $game, public readonly bool $attackersWins, public readonly RoundEndReason $reason)
    {
        parent::__construct();
        $this->roundNumberEnded = $game->getRoundNumber();
    }

    public function serialize(): array
    {
        return [
            'roundNumber'    => $this->roundNumberEnded,
            'newRoundNumber' => $this->roundNumberEnded + 1,
            'attackersWins'  => $this->attackersWins,
            'score'          => $this->game->getScore()->toArray(),
        ];
    }

}
