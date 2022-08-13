<?php

namespace cs\Event;

use cs\Core\Game;

final class RoundEndEvent extends TickEvent
{
    public function __construct(private Game $game, public readonly bool $attackersWins)
    {
        parent::__construct(function (): void {
            $this->game->endRound($this->attackersWins);
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
