<?php

namespace cs\Core;

use cs\Enum\InventorySlot;
use cs\Enum\ItemName;
use cs\Enum\ItemType;
use cs\Equipment\Flashbang;
use cs\Event\EquipEvent;

abstract class Item
{
    public const equipReadyTimeMs = 0;

    private int $skinId;
    protected bool $equipped = false;
    protected int $price = 9999;
    private ?EquipEvent $eventEquip = null;

    public function __construct(bool $instantlyEquip = false)
    {
        $this->equipped = $instantlyEquip;
    }

    public function isUserDroppable(): bool
    {
        if ($this->getType() === ItemType::TYPE_KNIFE) {
            return false;
        }
        if ($this->getType() === ItemType::TYPE_DEFUSE_KIT) {
            return false;
        }

        return true;
    }

    public function getMaxBuyCount(): int
    {
        return 5;
    }

    public function getQuantity(): int
    {
        return 1;
    }

    public abstract function getType(): ItemType;

    public abstract function getSlot(): InventorySlot;

    public function getName(): string
    {
        return ItemName::map[$this->getId()] ?? 'Unknown';
    }

    public function getId(): string
    {
        return get_class($this);
    }

    public function setSkinId(int $skinId): void
    {
        $this->skinId = $skinId;
    }

    public function getSkinId(): int
    {
        return $this->skinId;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function canPurchaseMultipleTime(): bool
    {
        if ($this->getType() === ItemType::TYPE_WEAPON_PRIMARY) {
            return true;
        }
        if ($this->getType() === ItemType::TYPE_WEAPON_SECONDARY) {
            return true;
        }
        if ($this->getId() === Flashbang::class && $this->getQuantity() < 2) {
            return true;
        }

        return false;
    }

    public function equip(): ?EquipEvent
    {
        if ($this->eventEquip === null) {
            $this->eventEquip = new EquipEvent(function () {
                $this->equipped = true;
            }, static::equipReadyTimeMs);
        }

        $this->eventEquip->reset();
        return $this->eventEquip;
    }

    public function unEquip(): void
    {
        $this->equipped = false;
    }

    public function isEquipped(): bool
    {
        return $this->equipped;
    }

    /**
     * @return array<string,int|string>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->getType()->value,
            'slot' => $this->getSlot()->value,
            'name' => $this->getName(),
        ];
    }

}
