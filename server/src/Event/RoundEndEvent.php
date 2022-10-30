<?php

namespace cs\Event;

use cs\Core\Game;
use cs\Enum\RoundEndReason;

final class RoundEndEvent extends TickEvent
{
    public readonly int $roundNumberEnded;
    /** @var array<mixed> */
    private array $scoreData;

    public function __construct(Game $game, public readonly bool $attackersWins, public readonly RoundEndReason $reason)
    {
        $this->roundNumberEnded = $game->getRoundNumber();
        $this->scoreData = $game->getScore()->toArray();
    }

    public function serialize(): array
    {
        return [
            'roundNumber'    => $this->roundNumberEnded,
            'newRoundNumber' => $this->roundNumberEnded + 1,
            'attackersWins'  => $this->attackersWins,
            'score'          => $this->scoreData,
        ];
    }

}
