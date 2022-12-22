<?php

namespace cs\Core;

use cs\Enum\InventorySlot;
use cs\Enum\ItemId;
use cs\Enum\ItemName;
use cs\Enum\ItemType;
use cs\Event\EquipEvent;

abstract class Item
{
    public const equipReadyTimeMs = 0;

    private int $id;
    private int $skinId;
    protected bool $equipped = false;
    protected int $price = 9999;
    private ?EquipEvent $eventEquip = null;
    /** @var array<string,int> */
    public readonly array $toArrayCache;

    public function __construct(bool $instantlyEquip = false)
    {
        $this->equipped = $instantlyEquip;
        $this->id = ItemId::$map[get_class($this)];
        $this->toArrayCache = [
            'id'   => $this->id,
            'slot' => $this->getSlot()->value,
        ];
    }

    public function canAttack(int $tickId): bool
    {
        return ($this->equipped);
    }

    public function canBeEquipped(): bool
    {
        return true;
    }

    public function reset(): void
    {
        // empty hook
    }

    public function isUserDroppable(): bool
    {
        return true;
    }

    public function getMaxBuyCount(): int
    {
        return 5;
    }

    public function getMaxQuantity(): int
    {
        return 1;
    }

    public function getQuantity(): int
    {
        return 1;
    }

    public function incrementQuantity(): void
    {
        // empty hook
    }

    public abstract function getType(): ItemType;

    public abstract function getSlot(): InventorySlot;

    public final function getId(): int
    {
        return $this->id;
    }

    public function setSkinId(int $skinId): void
    {
        $this->skinId = $skinId;
    }

    public function getSkinId(): int
    {
        return $this->skinId;
    }

    public function getPrice(?self $alreadyHaveSlotItem = null): int
    {
        return $this->price;
    }

    public function canPurchaseMultipleTime(self $newSlotItem): bool
    {
        if ($this->getType() === ItemType::TYPE_WEAPON_PRIMARY) {
            return true;
        }
        if ($this->getType() === ItemType::TYPE_WEAPON_SECONDARY) {
            return true;
        }

        return false;
    }

    public function equip(): ?EquipEvent
    {
        if (!$this->canBeEquipped()) {
            return null;
        }

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
     * @return array<string,int>
     */
    public function toArray(): array
    {
        return $this->toArrayCache;
    }

}
