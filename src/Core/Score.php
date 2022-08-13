<?php

namespace cs\Core;

class Score
{
    // TODO swap teams in halftime

    private int $scoreAttackers = 0;
    private int $scoreDefenders = 0;

    public function defendersWinsRound(): void
    {
        $this->scoreDefenders++;
    }

    public function attackersWinsRound(): void
    {
        $this->scoreAttackers++;
    }

    public function attackersIsWinning(): bool
    {
        return ($this->scoreAttackers > $this->scoreDefenders);
    }

    public function isTie(): bool
    {
        return ($this->scoreAttackers === $this->scoreDefenders);
    }

    public function getScoreAttackers(): int
    {
        return $this->scoreAttackers;
    }

    public function getScoreDefenders(): int
    {
        return $this->scoreDefenders;
    }

}
