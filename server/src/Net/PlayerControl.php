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

    private function isPlantingOrDefusing(): bool
    {
        return $this->player->isPlantingOrDefusing();
    }

    public function stand(): void
    {
        if ($this->isPlantingOrDefusing()) {
            return;
        }

        $this->player->stand();
    }

    public function crouch(): void
    {
        if ($this->isPlantingOrDefusing()) {
            return;
        }

        $this->player->crouch();
    }

    public function walk(): void
    {
        if ($this->gamePaused() || $this->isPlantingOrDefusing()) {
            return;
        }

        $this->player->speedWalk();
    }

    public function run(): void
    {
        if ($this->gamePaused() || $this->isPlantingOrDefusing()) {
            return;
        }

        $this->player->speedRun();
    }

    public function jump(): void
    {
        if ($this->gamePaused() || $this->isPlantingOrDefusing()) {
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
        $item = BuyMenuItem::tryFrom($buyMenuItemId);
        if ($item === null) {
            return;
        }
        $this->player->buyItem($item);
    }

    public function lookAt(float $angleHorizontal, float $angleVertical): void
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
        $this->player->use();
    }

    public function equip(int $slotId): void
    {
        $slot = InventorySlot::tryFrom($slotId);
        if ($slot === null) {
            return;
        }
        if ($this->player->getEquippedItem()->getSlot() === $slot) {
            return;
        }

        $this->player->equip($slot);
    }

    public function forward(): void
    {
        if ($this->gamePaused() || $this->isPlantingOrDefusing()) {
            return;
        }

        $this->player->moveForward();
    }

    public function backward(): void
    {
        if ($this->gamePaused() || $this->isPlantingOrDefusing()) {
            return;
        }

        $this->player->moveBackward();
    }

    public function left(): void
    {
        if ($this->gamePaused() || $this->isPlantingOrDefusing()) {
            return;
        }

        $this->player->moveLeft();
    }

    public function right(): void
    {
        if ($this->gamePaused() || $this->isPlantingOrDefusing()) {
            return;
        }

        $this->player->moveRight();
    }

}
