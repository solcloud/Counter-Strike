<?php

namespace cs\Core;

use cs\Enum\ArmorType;
use cs\Enum\Color;
use cs\Event\Event;
use cs\Traits\Player as PlayerTrait;

final class Player
{
    use PlayerTrait\JumpTrait;
    use PlayerTrait\CrouchTrait;
    use PlayerTrait\AttackTrait;
    use PlayerTrait\GravityTrait;
    use PlayerTrait\MovementTrait;
    use PlayerTrait\InventoryTrait;

    // Better to use even numbers
    public const speedFall = 60;
    public const speedMove = 50; // TODO: linear movement or ease-in-out?
    public const speedMoveWalk = 40;
    public const speedMoveCrouch = 30;
    public const speedJump = 30;
    public const tickCountJump = 5;
    public const tickCountCrouch = 10;
    public const headHeightStand = 190;
    public const gunHeightStand = self::headHeightStand - self::headRadius;
    public const headHeightCrouch = 140;
    public const boxHeightCrouchCover = self::headHeightCrouch + 2;
    public const obstacleOvercomeHeight = 20;
    public const playerBoundingRadius = self::bodyRadius;
    public const fallDamageThreshold = 3 * self::headHeightStand;
    public const jumpHeight = self::speedJump * self::tickCountJump;
    public const headRadius = 30;
    public const bodyRadius = 44;
    public const jumpMovementSlowDown = 1;
    public const flyingMovementSlowDown = 0.8;

    private Point $position;
    private PlayerCamera $sight;
    private Inventory $inventory;
    private World $world;
    private ?Floor $activeFloor;
    /** @var Event[] */
    private array $eventsCache = [];
    /** @var Event[] */
    private array $events = [];

    private bool $isAttacking = false;
    private int $health = 100;
    private int $armor = 0;
    private int $headHeight = self::headHeightStand; // highest player point
    public int $playerBoundingRadius = self::playerBoundingRadius;

    // Event IDs, sequence, ascending order priority
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
    )
    {
        $this->inventory = new Inventory($this->isPlayingOnAttackerSide);
        $this->sight = new PlayerCamera();
        $this->position = new Point();

        $this->initialize();
    }

    private function initialize(): void
    {
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
        $this->health = 0;
        $this->world->playerDiedToFallDamage($this);
    }

    public function getHeadHeight(): int
    {
        return $this->headHeight;
    }

    public function getBodyHeight(): int
    {
        return $this->getHeadHeight() - 2 * self::headRadius;
    }

    public function getSightHeight(): int
    {
        return $this->getHeadHeight() - self::headRadius;
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
    }

    public function lowerHealth(int $healthDamage): void
    {
        $this->health -= abs($healthDamage);
    }

    public function getHealth(): int
    {
        return $this->health;
    }

    public function getArmorType(): ArmorType
    {
        return $this->inventory->getArmor();
    }

    public function roundReset(): void
    {
        $this->resetTickStates();
        $this->getSight()->reset();
        $this->inventory->reset($this->isPlayingOnAttackerSide, !$this->isAlive());

        $this->events = [];
        $this->health = 100;
        $this->isWalking = false;
        $this->headHeight = self::headHeightStand;
        $this->initialize();
    }

    /**
     * @param array{id: int, color: int, isAttacker: bool, position: array{x: int, y: int, z: int}} $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['id'], Color::from($data['color']), $data['isAttacker']);
    }

    /**
     * @return array{id: int, color: int, isAttacker: bool, position: array{x: int, y: int, z: int}}
     */
    public function toArray(): array
    {
        return [
            'id'         => $this->getId(),
            'color'      => $this->getColor()->value,
            'isAttacker' => $this->isPlayingOnAttackerSide(),
            'position'   => $this->position->toArray(),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function serialize(): array
    {
        return [
            "id"          => $this->getId(),
            "color"       => $this->getColor()->value,
            "money"       => $this->getInventory()->getDollars(),
            "item"        => $this->getEquippedItem()->toArray(),
            "slots"       => $this->getInventory()->getFilledSlots(),
            "health"      => $this->health,
            "position"    => $this->position->toArray(),
            "look"        => $this->getSight()->toArray(),
            "isAttacker"  => $this->isPlayingOnAttackerSide(),
            "heightSight" => $this->getSightHeight(),
            "heightBody"  => $this->getBodyHeight(),
            "armor"       => 0, //TODO
        ];
    }

}
