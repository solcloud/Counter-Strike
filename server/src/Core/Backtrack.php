<?php

namespace cs\Core;

final class Backtrack
{

    /** @var array<int,array<string,mixed>> */
    private array $saveState = [];
    /** @var array<int,array<string,mixed>> */
    private array $newestState = [];
    /** @var list<array<int,array<string,mixed>>> */
    private array $states = [];
    private Point $point;

    public function __construct(private Game $game, public readonly int $numberOfHistoryStates)
    {
        if ($numberOfHistoryStates < 0) {
            throw new GameException("Variable '{$numberOfHistoryStates}' needs to be bigger or equal zero"); // @codeCoverageIgnore
        }
        $this->point = new Point();
    }

    public function reset(): void
    {
        $this->states = [];
    }

    public function startState(): void
    {
        $this->newestState = [];
    }

    public function addStateData(Player $alivePlayer): void
    {
        if ($this->numberOfHistoryStates === 0) {
            return;
        }

        $this->newestState[$alivePlayer->getId()] = [
            'a' => $alivePlayer->getReferenceToPosition()->toFlatArray(),
            'b' => $alivePlayer->getSight()->getRotationHorizontal(),
            'c' => $alivePlayer->getSight()->getRotationVertical(),
            'd' => $alivePlayer->getHeadHeight(),
        ];
    }

    public function finishState(): void
    {
        if ($this->numberOfHistoryStates === 0) {
            return;
        }

        $count = array_unshift($this->states, $this->newestState);
        if ($count > $this->numberOfHistoryStates + 1) {
            array_pop($this->states);
        }
    }

    public function saveState(): void
    {
        if ($this->numberOfHistoryStates === 0) {
            return;
        }

        foreach ($this->game->getPlayers() as $player) {
            $this->saveState[$player->getId()] = [
                'a' => $player->getReferenceToPosition()->toFlatArray(),
                'b' => $player->getSight()->getRotationHorizontal(),
                'c' => $player->getSight()->getRotationVertical(),
                'd' => $player->getHeadHeight(),
            ];
        }
    }

    public function restoreState(): void
    {
        if ($this->numberOfHistoryStates === 0) {
            return;
        }

        foreach ($this->saveState as $playerId => $playerData) {
            $player = $this->game->getPlayer($playerId);
            $player->setPosition($this->point->set(...$playerData['a'])); // @phpstan-ignore-line
            $player->getSight()->look($playerData['b'], $playerData['c']); // @phpstan-ignore-line
            $player->setHeadHeight($playerData['d']); // @phpstan-ignore-line
        }
    }

    public function apply(int $state, int $playerId): void
    {
        if ($state === 0 || $this->numberOfHistoryStates === 0) {
            return;
        }

        $playerData = $this->states[$state][$playerId] ?? false;
        if (false === $playerData) {
            return;
        }

        $player = $this->game->getPlayer($playerId);
        $player->setPosition($this->point->set(...$playerData['a'])); // @phpstan-ignore-line
        $player->getSight()->look($playerData['b'], $playerData['c']); // @phpstan-ignore-line
        $player->setHeadHeight($playerData['d']); // @phpstan-ignore-line
    }

    /**
     * @return non-empty-array<int>
     */
    public function getStates(): array
    {
        $states = [];
        for ($i = 1; $i < count($this->states); $i++) {
            if ($this->states[$i] === []) {
                continue;
            }
            $states[] = $i;
        }
        if ($states === []) {
            return [0];
        }
        return $states;
    }

    /**
     * @return list<array{int, int, int}>
     */
    public function getAllPlayerPositions(int $playerId): array
    {
        $output = [];
        foreach ($this->states as $state) {
            if (isset($state[$playerId])) {
                $output[] = $state[$playerId]['a'];
            }
        }

        return $output; // @phpstan-ignore return.type
    }

}
