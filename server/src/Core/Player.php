<?php

namespace cs\Core;

use cs\Enum\ArmorType;
use cs\Enum\Color;
use cs\Enum\InventorySlot;
use cs\Event\Event;
use cs\Traits\Player as PlayerTrait;
use cs\Weapon\AmmoBasedWeapon;

final class Player
{
    use PlayerTrait\JumpTrait;
    use PlayerTrait\CrouchTrait;
    use PlayerTrait\AttackTrait;
    use PlayerTrait\GravityTrait;
    use PlayerTrait\MovementTrait;
    use PlayerTrait\InventoryTrait;

    private Point $position;
    private PlayerCamera $sight;
    private Inventory $inventory;
    private World $world;
    private ?Floor $activeFloor = null;
    private int $playerBoundingRadius;
    /** @var Event[] */
    private array $eventsCache = [];
    /** @var Event[] */
    private array $events = [];

    private int $health;
    private int $armor = 0;
    private int $headHeight; // highest player point
    private bool $isAttacking = false;

    // Event IDs, sequence - ascending order priority
    private int $eventIdPrimary = 0;
    private int $eventIdJump = 1;
    private int $eventIdCrouch = 2;
    private int $eventIdMovement = 3;
    private int $eventIdGravity = 4;
    private int $eventIdOther = 5; // last

    public function __construct(
        private int   $id,
        private Color $color,
        private bool  $isPlayingOnAttackerSide,
        Point         $position = new Point(),
    )
    {
        $this->inventory = new Inventory($this->isPlayingOnAttackerSide);
        $this->sight = new PlayerCamera();
        $this->position = $position;
        $this->playerBoundingRadius = Setting::playerBoundingRadius();

        $this->initialize();
    }

    private function initialize(): void
    {
        $this->health = 100;
        $this->isWalking = false;
        $this->headHeight = Setting::playerHeadHeightStand();

        $this->events = [];
        $this->addEvent($this->createMovementEvent(), $this->eventIdMovement);
        $this->addEvent($this->createGravityEvent(), $this->eventIdGravity);
    }

    public function onTick(int $tick): void
    {
        for ($i = 0; $i <= $this->eventIdOther; $i++) {
            if (!isset($this->events[$i])) {
                continue;
            }

            $this->events[$i]->process($tick);
        }

        $this->resetTickStates();
    }

    private function resetTickStates(): void
    {
        $this->isAttacking = false;
    }

    private function onPlayerDied(): void
    {
        $this->armor = 0;

        $items = $this->getInventory()->getItems();
        $dropPosition = $this->getPositionImmutable();
        if (isset($items[InventorySlot::SLOT_PRIMARY->value])) {
            $this->world->addDropItem($items[InventorySlot::SLOT_PRIMARY->value], $dropPosition);
        } elseif (isset($items[InventorySlot::SLOT_SECONDARY->value])) {
            $this->world->addDropItem($items[InventorySlot::SLOT_SECONDARY->value], $dropPosition);
        }
    }

    private function addEvent(Event $event, int $eventId): void
    {
        $this->events[$eventId] = $event;
        $event->customId = $eventId;
        $event->onComplete[] = fn(Event $e) => $this->removeEvent($e->customId);
    }

    private function removeEvent(int $eventId): void
    {
        unset($this->events[$eventId]);
    }

    public function isAlive(): bool
    {
        return ($this->health > 0);
    }

    public function suicide(): void
    {
        $this->lowerHealth($this->health);
        $this->world->playerDiedToFallDamage($this);
    }

    public function use(): void
    {
        $this->world->playerUse($this);
    }

    public function getHeadHeight(): int
    {
        return $this->headHeight;
    }

    public function getBodyHeight(): int
    {
        return $this->getHeadHeight() - 2 * Setting::playerHeadRadius();
    }

    public function getSightHeight(): int
    {
        return $this->getHeadHeight() - Setting::playerHeadRadius();
    }

    public function getSight(): PlayerCamera
    {
        return $this->sight;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setWorld(World $world): void
    {
        $this->world = $world;
    }

    public function getColor(): Color
    {
        return $this->color;
    }

    public function isPlayingOnAttackerSide(): bool
    {
        return $this->isPlayingOnAttackerSide;
    }

    public function getBoundingRadius(): int
    {
        return $this->playerBoundingRadius;
    }

    public function lowerArmor(int $armorDamage): void
    {
        $this->armor -= abs($armorDamage);
        if ($this->armor < 0) {
            $this->armor = 0;
        }
    }

    public function lowerHealth(int $healthDamage): void
    {
        $this->health -= abs($healthDamage);
        if ($this->health <= 0) {
            $this->health = 0;
            $this->onPlayerDied();
        }
    }

    public function isPlantingOrDefusing(): bool
    {
        return $this->world->isPlantingOrDefusing($this);
    }

    public function getHealth(): int
    {
        return $this->health;
    }

    public function getArmorType(): ArmorType
    {
        return $this->inventory->getArmor();
    }

    public function hasDefuseKit(): bool
    {
        return $this->inventory->has(InventorySlot::SLOT_KIT->value);
    }

    public function swapTeam(): void
    {
        $this->isPlayingOnAttackerSide = !$this->isPlayingOnAttackerSide;
    }

    public function roundReset(): void
    {
        $this->resetTickStates();
        $this->getSight()->reset();
        $this->inventory->reset($this->isPlayingOnAttackerSide, !$this->isAlive());
        $this->initialize();
    }

    /**
     * @param array{id: int, color: int, isAttacker: bool} $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['id'], Color::from($data['color']), $data['isAttacker']);
    }

    /**
     * @return array{id: int, color: int, isAttacker: bool}
     */
    public function toArray(): array
    {
        return [
            'id'         => $this->getId(),
            'color'      => $this->getColor()->value,
            'isAttacker' => $this->isPlayingOnAttackerSide(),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function serialize(): array
    {
        $ammo = null;
        $ammoReserve = null;
        $reloading = false;
        $equippedItem = $this->getInventory()->getEquipped();
        if ($equippedItem instanceof AmmoBasedWeapon) {
            $ammo = $equippedItem->getAmmo();
            $ammoReserve = $equippedItem->getAmmoReserve();
            $reloading = $equippedItem->isReloading();
        }

        return [
            "id"          => $this->getId(),
            "color"       => $this->getColor()->value,
            "money"       => $this->getInventory()->getDollars(),
            "item"        => $equippedItem->toArray(),
            "canAttack"   => $this->world->canAttack($this),
            "canBuy"      => $this->world->canBuy($this),
            "canPlant"    => $this->world->canPlant($this),
            "slots"       => $this->getInventory()->getFilledSlots(),
            "health"      => $this->health,
            "position"    => $this->position->toArray(),
            "look"        => $this->getSight()->toArray(),
            "isAttacker"  => $this->isPlayingOnAttackerSide(),
            "heightSight" => $this->getSightHeight(),
            "heightBody"  => $this->getBodyHeight(),
            "height"      => $this->getHeadHeight(),
            "armor"       => $this->getInventory()->getArmor() === ArmorType::NONE ? 0 : 100, //TODO
            "ammo"        => $ammo,
            "ammoReserve" => $ammoReserve,
            "isReloading" => $reloading,
        ];
    }

}
