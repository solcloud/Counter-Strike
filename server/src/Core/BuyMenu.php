<?php

namespace cs\Core;

use cs\Enum\BuyMenuItem;
use cs\Enum\ItemType;
use cs\Equipment;
use cs\Weapon;

class BuyMenu
{
    /** @var array<int,int> */
    private array $itemBuyCount = [];
    private int $grenadeCount = 0;
    private int $grenadeCountMax = 4;

    public function __construct(private bool $forAttackerStore)
    {
    }

    public function reset(): void
    {
        $this->itemBuyCount = [];
        $this->grenadeCount = 0;
    }

    public function get(BuyMenuItem $buyCandidate): ?Item
    {
        $item = match ($buyCandidate) {
            BuyMenuItem::RIFLE_AK => $this->forAttackerStore ? new Weapon\RifleAk() : null,
            BuyMenuItem::RIFLE_M4A4 => !$this->forAttackerStore ? new  Weapon\RifleM4A4() : null,
            BuyMenuItem::PISTOL_USP => !$this->forAttackerStore ? new  Weapon\PistolUsp() : null,
            BuyMenuItem::PISTOL_GLOCK => $this->forAttackerStore ? new  Weapon\PistolGlock() : null,
            BuyMenuItem::PISTOL_P250 => new  Weapon\PistolP250(),
            BuyMenuItem::GRENADE_FLASH => new Equipment\Flashbang(),
            BuyMenuItem::GRENADE_SMOKE => new Equipment\Smoke(),
            BuyMenuItem::GRENADE_DECOY => new Equipment\Decoy(),
            BuyMenuItem::GRENADE_HE => new Equipment\HighExplosive(),
            BuyMenuItem::GRENADE_MOLOTOV => $this->forAttackerStore ? new Equipment\Molotov() : null,
            BuyMenuItem::GRENADE_INCENDIARY => $this->forAttackerStore ? null : new Equipment\Incendiary(),
            BuyMenuItem::KEVLAR_BODY_AND_HEAD => new Equipment\Kevlar(true),
            BuyMenuItem::KEVLAR_BODY => new Equipment\Kevlar(false),
            default => throw new GameException("Unknown buy request for side '{$this->forAttackerStore}' and item '{$buyCandidate->name}'"),
        };

        if (!$item) {
            return null;
        }

        if (!isset($this->itemBuyCount[$item->getId()])) {
            $this->itemBuyCount[$item->getId()] = 0;
        }
        if (($this->itemBuyCount[$item->getId()] + 1) > $item->getMaxBuyCount()) {
            return null;
        }
        if ($item->getType() === ItemType::TYPE_GRENADE && $this->grenadeCount + 1 > $this->grenadeCountMax) {
            return null;
        }

        return $item;
    }

    public function buy(Item $item): void
    {
        $this->itemBuyCount[$item->getId()]++;
        if ($item->getType() === ItemType::TYPE_GRENADE) {
            $this->grenadeCount++;
        }
    }

}
