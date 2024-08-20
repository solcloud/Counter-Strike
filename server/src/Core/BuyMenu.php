<?php

namespace cs\Core;

use cs\Enum\BuyMenuItem;
use cs\Enum\ItemType;
use cs\Equipment;
use cs\Weapon;

class BuyMenu
{
    /** @var array<int,int> [itemId => count] */
    private array $itemBuyCount;
    private int $grenadeCount;
    private int $grenadeCountMax = 4;

    /** @param Item[] $alreadyHaveItems */
    public function __construct(public bool $forAttackerStore, array $alreadyHaveItems)
    {
        $this->reset($this->forAttackerStore, $alreadyHaveItems);
    }

    /** @param Item[] $alreadyHaveItems */
    public function reset(bool $forAttackerStore, array $alreadyHaveItems): void
    {
        $this->grenadeCount = 0;
        $this->itemBuyCount = [];
        $this->forAttackerStore = $forAttackerStore;

        foreach ($alreadyHaveItems as $item) {
            if ($item->getType() === ItemType::TYPE_GRENADE) {
                $this->grenadeCount++;
            }

            if (!isset($this->itemBuyCount[$item->getId()])) {
                $this->itemBuyCount[$item->getId()] = 0;
            }
            $this->itemBuyCount[$item->getId()]++;
        }
    }

    private function getItem(BuyMenuItem $buyCandidate): ?Item
    {
        return match ($buyCandidate) {
            BuyMenuItem::RIFLE_AK => $this->forAttackerStore ? new Weapon\RifleAk() : null,
            BuyMenuItem::RIFLE_M4A4 => !$this->forAttackerStore ? new Weapon\RifleM4A4() : null,
            BuyMenuItem::RIFLE_AWP => new Weapon\RifleAWP(),
            BuyMenuItem::PISTOL_USP => !$this->forAttackerStore ? new Weapon\PistolUsp() : null,
            BuyMenuItem::PISTOL_GLOCK => $this->forAttackerStore ? new Weapon\PistolGlock() : null,
            BuyMenuItem::PISTOL_P250 => new Weapon\PistolP250(),
            BuyMenuItem::GRENADE_FLASH => new Equipment\Flashbang(),
            BuyMenuItem::GRENADE_SMOKE => new Equipment\Smoke(),
            BuyMenuItem::GRENADE_DECOY => new Equipment\Decoy(),
            BuyMenuItem::GRENADE_HE => new Equipment\HighExplosive(),
            BuyMenuItem::GRENADE_MOLOTOV => $this->forAttackerStore ? new Equipment\Molotov() : null,
            BuyMenuItem::GRENADE_INCENDIARY => !$this->forAttackerStore ? new Equipment\Incendiary() : null,
            BuyMenuItem::DEFUSE_KIT => !$this->forAttackerStore ? new Equipment\DefuseKit() : null,
            BuyMenuItem::KEVLAR_BODY_AND_HEAD => new Equipment\Kevlar(true),
            BuyMenuItem::KEVLAR_BODY => new Equipment\Kevlar(false),
        };
    }

    public function get(BuyMenuItem $buyCandidate): ?Item
    {
        $item = $this->getItem($buyCandidate);
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

    public function confirmPurchase(Item $item): void
    {
        $this->itemBuyCount[$item->getId()]++;
        if ($item->getType() === ItemType::TYPE_GRENADE) {
            $this->grenadeCount++;
        }
    }

}
