<?php

namespace cs\Event;

use cs\Core\Game;
use cs\Enum\RoundEndReason;

final class RoundEndEvent extends TickEvent
{
    public readonly int $roundNumberEnded;

    public function __construct(private Game $game, public readonly bool $attackersWins, public readonly RoundEndReason $reason)
    {
        $this->roundNumberEnded = $this->game->getRoundNumber();
        parent::__construct(function (): void {
            $this->game->endRound($this);
        });
    }

    public function serialize(): array
    {
        return [
            'roundNumber'    => $this->roundNumberEnded,
            'newRoundNumber' => $this->game->getRoundNumber(),
            'attackersWins'  => $this->attackersWins,
            'score'          => $this->game->getScore()->toArray(),
        ];
    }

}
