<?php

namespace cs\Core;

use cs\Event\RoundEndEvent;

class Score
{

    private int $roundNumber = 1;
    private int $scoreAttackers = 0;
    private int $scoreDefenders = 0;
    private int $lossBonusAttackers = 0;
    private int $lossBonusDefenders = 0;
    private ?bool $lastRoundAttackerWins = null;
    /** @var array<int,RoundEndEvent> */
    private array $roundEndEvents = [];

    public function swapTeams(): void
    {
        $attackerScore = $this->scoreAttackers;
        $this->scoreAttackers = $this->scoreDefenders;
        $this->scoreDefenders = $attackerScore;

        $this->lossBonusAttackers = 1;
        $this->lossBonusDefenders = 1;
        $this->lastRoundAttackerWins = null;
    }

    public function roundEnd(RoundEndEvent $event): void
    {
        $this->roundEndEvents[$this->roundNumber] = $event;

        $attackersWins = $event->attackersWins;
        $this->roundNumber++;
        if ($attackersWins) {
            $this->scoreAttackers++;
        } else {
            $this->scoreDefenders++;
        }
        if ($this->lastRoundAttackerWins === null) {
            if ($attackersWins) {
                $this->lossBonusDefenders++;
            } else {
                $this->lossBonusAttackers++;
            }
        } else {
            if ($this->lastRoundAttackerWins === true && $attackersWins) {
                $this->lossBonusDefenders++;
            }
            if ($this->lastRoundAttackerWins === true && !$attackersWins) {
                $this->lossBonusDefenders = 0;
                $this->lossBonusAttackers = 1;
            }
            if ($this->lastRoundAttackerWins === false && !$attackersWins) {
                $this->lossBonusAttackers++;
            }
            if ($this->lastRoundAttackerWins === false && $attackersWins) {
                $this->lossBonusDefenders = 1;
                $this->lossBonusAttackers = 0;
            }
        }

        $this->lastRoundAttackerWins = $attackersWins;
    }

    public function attackersIsWinning(): bool
    {
        if ($this->isTie()) {
            return false;
        }
        return ($this->scoreAttackers > $this->scoreDefenders);
    }

    public function defendersIsWinning(): bool
    {
        if ($this->isTie()) {
            return false;
        }
        return ($this->scoreDefenders > $this->scoreAttackers);
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

    public function getNumberOfLossRoundsInRow(bool $isAttacker): int
    {
        if ($isAttacker) {
            return $this->lossBonusAttackers;
        }

        return $this->lossBonusDefenders;
    }

    /**
     * @return RoundEndEvent[]
     */
    public function getRoundEndEvents(): array
    {
        return $this->roundEndEvents;
    }

}
