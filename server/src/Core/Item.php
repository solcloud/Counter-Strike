<?php

namespace cs\Core;

use cs\Enum\InventorySlot;
use cs\Enum\ItemId;
use cs\Enum\ItemType;
use cs\Event\EquipEvent;

abstract class Item
{
    public const equipReadyTimeMs = 0;

    private int $id;
    private int $skinId;
    protected bool $equipped = false;
    /** @var non-negative-int */
    protected int $price = 9999;
    /** @var non-negative-int */
    protected int $scopeLevel = 0;
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
        $this->scopeLevel = 0;
    }

    public function isUserDroppable(): bool
    {
        return true;
    }

    /** @return non-negative-int */
    public function getMaxBuyCount(): int
    {
        return 5;
    }

    /** @return positive-int */
    public function getMaxQuantity(): int
    {
        return 1;
    }

    /** @return positive-int */
    public function getQuantity(): int
    {
        return 1;
    }

    /** @return non-negative-int */
    public function getScopeLevel(): int
    {
        return $this->scopeLevel;
    }

    /** @codeCoverageIgnore */
    public function decrementQuantity(): void
    {
        // empty hook
    }

    /** @codeCoverageIgnore */
    public function incrementQuantity(): void
    {
        // empty hook
    }

    /** @codeCoverageIgnore */
    public function clone(): static
    {
        throw new GameException('Override clone() method if makes sense for item: ' . get_class($this));
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

    /** @return non-negative-int */
    public function getPrice(?self $alreadyHaveSlotItem = null): int
    {
        return $this->price;
    }

    public function canPurchaseMultipleTime(self $newSlotItem): bool
    {
        return match ($this->getType()) {
            ItemType::TYPE_WEAPON_PRIMARY, ItemType::TYPE_WEAPON_SECONDARY => true,
            default => GameException::invalid('New item? ' . get_class($this)) // @codeCoverageIgnore
        };
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
        $this->scopeLevel = 0;
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
