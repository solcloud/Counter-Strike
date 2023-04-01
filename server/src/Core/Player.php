<?php

namespace cs\Core;

use cs\Enum\ArmorType;
use cs\Enum\Color;
use cs\Enum\InventorySlot;
use cs\Event\Event;
use cs\Event\TimeoutEvent;
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
    private DynamicFloor $headFloor;
    private int $playerBoundingRadius;
    /** @var Event[] */
    private array $eventsCache = [];
    /** @var Event[] */
    private array $events = [];

    private int $health;
    private int $headHeight; // highest player point
    private bool $isAttacking = false;

    // Event IDs, sequence - ascending order priority
    private int $eventIdPrimary = 0;
    private int $eventIdJump = 1;
    private int $eventIdCrouch = 2;
    private int $eventIdShotSlowdown = 3;
    private int $eventIdMovement = 4;
    private int $eventIdGravity = 5;
    private int $eventIdLast = 6; // last

    public function __construct(
        private int   $id,
        private Color $color,
        private bool  $isPlayingOnAttackerSide,
    )
    {
        $this->inventory = new Inventory($this->isPlayingOnAttackerSide);
        $this->sight = new PlayerCamera();
        $this->position = new Point();
        $this->headFloor = new DynamicFloor($this);
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
        if ($this->activeFloor && !$this->world->isOnFloor($this->activeFloor, $this->position, $this->playerBoundingRadius)) {
            $this->setActiveFloor(null);
        }

        for ($i = 0; $i <= $this->eventIdLast; $i++) {
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
        $dropItems = [];
        $items = $this->inventory->getItems();
        if (isset($items[InventorySlot::SLOT_PRIMARY->value])) {
            $dropItems[] = $items[InventorySlot::SLOT_PRIMARY->value];
        } elseif (isset($items[InventorySlot::SLOT_SECONDARY->value])) {
            $dropItems[] = $items[InventorySlot::SLOT_SECONDARY->value];
        }
        if (isset($items[InventorySlot::SLOT_KIT->value])) {
            $dropItems[] = $items[InventorySlot::SLOT_KIT->value];
        } elseif (isset($items[InventorySlot::SLOT_BOMB->value])) {
            $dropItems[] = $items[InventorySlot::SLOT_BOMB->value];
        }

        $dropCount = count($dropItems);
        if ($dropCount > 0) {
            $angle = 0;
            $angleOffset = 360 / $dropCount;
            foreach ($dropItems as $item) {
                $this->sight->look($angle, -78);
                $this->world->dropItem($this, $item);
                $angle += $angleOffset;
            }
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
        return ($this->health !== 0);
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

    /**
     * @internal
     */
    public function setHeadHeight(int $height): void
    {
        $this->headHeight = $height;
    }

    public function getHeadHeight(): int
    {
        return $this->headHeight;
    }

    public function getSightHeight(): int
    {
        return $this->headHeight - Setting::playerHeadRadius();
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
        $this->inventory->getKevlar()?->lowerArmor($armorDamage);
    }

    public function lowerHealth(int $healthDamage): void
    {
        $this->addEvent(new TimeoutEvent(null, 70), $this->eventIdShotSlowdown);
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
        $kevlar = $this->inventory->getKevlar();
        if ($kevlar) {
            return $kevlar->getArmorType();
        }
        return ArmorType::NONE;
    }

    public function getArmorValue(): int
    {
        $kevlar = $this->inventory->getKevlar();
        if ($kevlar) {
            return $kevlar->getArmor();
        }
        return 0;
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

    public function getHeadFloor(): Floor
    {
        return $this->headFloor;
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
            'id'         => $this->id,
            'color'      => $this->color->value,
            'isAttacker' => $this->isPlayingOnAttackerSide,
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
        $equippedItem = $this->inventory->getEquipped();
        if ($equippedItem instanceof AmmoBasedWeapon) {
            $ammo = $equippedItem->getAmmo();
            $ammoReserve = $equippedItem->getAmmoReserve();
            $reloading = $equippedItem->isReloading();
        }

        return [
            'id'          => $this->id,
            'color'       => $this->color->value,
            'money'       => $this->inventory->getDollars(),
            'item'        => $equippedItem->toArrayCache,
            'canAttack'   => $this->world->canAttack($this),
            'canBuy'      => $this->world->canBuy($this),
            'canPlant'    => $this->world->canPlant($this),
            'slots'       => $this->inventory->getFilledSlots(),
            'health'      => $this->health,
            'position'    => $this->position->toArray(),
            'look'        => $this->sight->toArray(),
            'isAttacker'  => $this->isPlayingOnAttackerSide,
            'sight'       => $this->getSightHeight(),
            'armor'       => $this->getArmorValue(),
            'armorType'   => $this->getArmorType()->value,
            'ammo'        => $ammo,
            'ammoReserve' => $ammoReserve,
            'isReloading' => $reloading,
        ];
    }

}
