<?php

namespace cs\Core;

readonly class GameState
{

    public function __construct(private Game $game)
    {
    }

    public function getPlayer(int $id): Player
    {
        return $this->game->getPlayer($id);
    }

    public function getTickId(): int
    {
        return $this->game->getTickId();
    }

    public function isPaused(): bool
    {
        return $this->game->isPaused();
    }

}
