<?php

namespace cs\Weapon;

use cs\Core\Bullet;
use cs\Core\Util;
use cs\Enum\InventorySlot;
use cs\Enum\ItemType;
use cs\Event\AttackEvent;
use cs\Event\AttackResult;
use cs\Event\ReloadEvent;
use cs\Interface\AttackEnable;
use cs\Interface\Reloadable;

abstract class AmmoBasedWeapon extends BaseWeapon implements Reloadable, AttackEnable
{
    protected int $ammo;
    protected int $ammoReserve;
    protected bool $reloading = false;
    protected bool $isWeaponPrimary;
    private ?ReloadEvent $eventReload = null;
    private int $lastAttackTick = 0;
    private int $fireRateTicks;

    public function __construct(bool $instantlyEquip = false)
    {
        parent::__construct($instantlyEquip);
        $this->ammoReserve = static::reserveAmmo;
        $this->fireRateTicks = Util::millisecondsToFrames(static::fireRateMs);
    }

    public function unEquip(): void
    {
        parent::unEquip();
        $this->reloading = false;
    }

    public function reset(): void
    {
        $this->ammo = static::magazineCapacity;
        $this->ammoReserve = static::reserveAmmo;
        $this->reloading = false;
        $this->lastAttackTick = 0;
    }

    public function canAttack(int $tickId): bool
    {
        if (!$this->equipped) {
            return false;
        }
        if ($this->reloading) {
            return false;
        }

        return ($this->lastAttackTick === 0 || $this->lastAttackTick + $this->fireRateTicks <= $tickId);
    }

    public final function attack(AttackEvent $event): ?AttackResult
    {
        if (!$this->canAttack($event->getTickId())) {
            return null;
        }
        if ($this->ammo === 0) {
            return null;
        }

        $this->ammo--;
        $this->lastAttackTick = $event->getTickId();

        $event->setItem($this);
        return $event->process();
    }

    public function attackSecondary(AttackEvent $event): ?AttackResult
    {
        return null;
    }

    public function createBullet(): Bullet
    {
        $bullet = new Bullet($this, static::range);
        $bullet->setProperties(
            damage: static::damage,
            damageArmor: static::damage * static::armorPenetration,
        );

        return $bullet;
    }

    public function getKillAward(): int
    {
        return static::killAward;
    }

    public function getAmmo(): int
    {
        return $this->ammo;
    }

    public function getAmmoReserve(): int
    {
        return $this->ammoReserve;
    }

    public function reload(): ?ReloadEvent
    {
        if ($this->reloading || $this->ammo === static::magazineCapacity || $this->ammoReserve === 0) {
            return null;
        }

        $this->reloading = true;
        return $this->createReloadEvent();
    }

    protected function createReloadEvent(): ReloadEvent
    {
        if ($this->eventReload === null) {
            $this->eventReload = new ReloadEvent(function () {
                if ($this->ammoReserve >= static::magazineCapacity) {
                    $this->ammoReserve -= (static::magazineCapacity - $this->ammo);
                    $newAmmo = static::magazineCapacity;
                } else {
                    $newAmmo = $this->ammo + $this->ammoReserve;
                    $this->ammoReserve = 0;
                }

                $this->ammo = $newAmmo;
                $this->reloading = false;
            }, static::reloadTimeMs);
        }

        $this->eventReload->reset();
        return $this->eventReload;
    }

    public function getType(): ItemType
    {
        if ($this->isWeaponPrimary) {
            return ItemType::TYPE_WEAPON_PRIMARY;
        }
        return ItemType::TYPE_WEAPON_SECONDARY;
    }

    public function getSlot(): InventorySlot
    {
        if ($this->isWeaponPrimary) {
            return InventorySlot::SLOT_PRIMARY;
        }
        return InventorySlot::SLOT_SECONDARY;
    }

    public function isReloading(): bool
    {
        return $this->reloading;
    }

}
