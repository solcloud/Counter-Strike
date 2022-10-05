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
    private ?int $halfTimeRoundNumber = null;
    private ?bool $lastRoundAttackerWins = null;
    /** @var array<int,mixed> */
    private array $roundsHistory = [];

    /**
     * @param int[] $lossBonuses
     */
    public function __construct(private array $lossBonuses)
    {
    }

    public function swapTeams(): void
    {
        $attackerScore = $this->scoreAttackers;
        $this->scoreAttackers = $this->scoreDefenders;
        $this->scoreDefenders = $attackerScore;

        $this->lossBonusAttackers = 1;
        $this->lossBonusDefenders = 1;
        $this->lastRoundAttackerWins = null;
        $this->halfTimeRoundNumber = $this->roundNumber;
    }

    public function roundEnd(RoundEndEvent $event): void
    {
        $this->roundNumber = $event->roundNumberEnded;

        $attackersWins = $event->attackersWins;
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
        $this->roundsHistory[$this->roundNumber] = [
            'attackersWins'  => $attackersWins,
            'reason'         => $event->reason->value,
            'scoreAttackers' => $this->scoreAttackers,
            'scoreDefenders' => $this->scoreDefenders,
        ];
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

    public function getMoneyLossBonus(bool $isAttacker): int
    {
        return $this->lossBonuses[min(count($this->lossBonuses) - 1, $this->getNumberOfLossRoundsInRow($isAttacker))];
    }

    public function getNumberOfLossRoundsInRow(bool $isAttacker): int
    {
        if ($isAttacker) {
            return $this->lossBonusAttackers;
        }

        return $this->lossBonusDefenders;
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return [
            'scoreAttackers'      => $this->scoreAttackers,
            'scoreDefenders'      => $this->scoreDefenders,
            'lossBonusAttackers'  => $this->getMoneyLossBonus(true),
            'lossBonusDefenders'  => $this->getMoneyLossBonus(false),
            'history'             => $this->roundsHistory,
            'halfTimeRoundNumber' => $this->halfTimeRoundNumber,
        ];
    }

}
