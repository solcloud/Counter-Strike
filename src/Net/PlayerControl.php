<?php

namespace cs\Net;

use cs\Core\GameState;
use cs\Core\Player;
use cs\Enum\BuyMenuItem;
use cs\Enum\InventorySlot;

class PlayerControl
{

    public function __construct(private Player $player, private GameState $gameState)
    {
    }

    private function gamePaused(): bool
    {
        return $this->gameState->isPaused();
    }

    public function stand(): void
    {
        $this->player->stand();
    }

    public function crouch(): void
    {
        $this->player->crouch();
    }

    public function walk(): void
    {
        if ($this->gamePaused()) {
            return;
        }

        $this->player->speedWalk();
    }

    public function run(): void
    {
        if ($this->gamePaused()) {
            return;
        }

        $this->player->speedRun();
    }

    public function jump(): void
    {
        if ($this->gamePaused()) {
            return;
        }

        $this->player->jump();
    }

    public function drop(): void
    {
        $this->player->dropEquippedItem();
    }

    public function reload(): void
    {
        if ($this->gamePaused()) {
            return;
        }

        $this->player->reload();
    }

    public function buy(int $buyMenuItemId): void
    {
        $this->player->buyItem(BuyMenuItem::from($buyMenuItemId));
    }

    public function lookAt(int $angleHorizontal, int $angleVertical): void
    {
        $this->player->getSight()->lookAt($angleHorizontal, $angleVertical);
    }

    public function attack(): void
    {
        if ($this->gamePaused()) {
            return;
        }

        $this->player->attack();
    }

    public function attack2(): void
    {
        if ($this->gamePaused()) {
            return;
        }

        $this->player->attackSecondary();
    }

    public function use(): void
    {
        // TODO
    }

    public function equip(int $slotId): void
    {
        $this->player->equip(InventorySlot::from($slotId));
    }

    public function forward(): void
    {
        if ($this->gamePaused()) {
            return;
        }

        $this->player->moveForward();
    }

    public function backward(): void
    {
        if ($this->gamePaused()) {
            return;
        }

        $this->player->moveBackward();
    }

    public function left(): void
    {
        if ($this->gamePaused()) {
            return;
        }

        $this->player->moveLeft();
    }

    public function right(): void
    {
        if ($this->gamePaused()) {
            return;
        }

        $this->player->moveRight();
    }

}
