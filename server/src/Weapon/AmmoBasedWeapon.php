<?php

namespace cs\Weapon;

use cs\Core\Bullet;
use cs\Core\Util;
use cs\Enum\InventorySlot;
use cs\Enum\ItemType;
use cs\Event\AttackResult;
use cs\Event\ReloadEvent;
use cs\Interface\Attackable;
use cs\Interface\AttackEnable;
use cs\Interface\Reloadable;

abstract class AmmoBasedWeapon extends BaseWeapon implements Reloadable, AttackEnable
{
    protected int $ammo;
    protected int $ammoReserve;
    protected bool $reloading;
    protected bool $isWeaponPrimary;
    private ?ReloadEvent $eventReload = null;
    private int $lastAttackTick;
    private int $lastRecoilTick;
    private int $lastRecoilBulletCount;
    private int $fireRateTicks;
    private int $recoilResetTicks;

    public function __construct(bool $instantlyEquip = false)
    {
        parent::__construct($instantlyEquip);
        $this->fireRateTicks = Util::millisecondsToFrames(static::fireRateMs);
        $this->recoilResetTicks = Util::millisecondsToFrames(static::recoilResetMs);
        $this->reset();
    }

    public function unEquip(): void
    {
        parent::unEquip();
        $this->reloading = false;
        $this->resetRecoil();
    }

    public function reset(): void
    {
        $this->ammo = static::magazineCapacity;
        $this->ammoReserve = static::reserveAmmo;
        $this->reloading = false;
        $this->lastAttackTick = 0;
        $this->resetRecoil();
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

    public final function attack(Attackable $event): ?AttackResult
    {
        if ($this->ammo === 0) {
            return null;
        }

        $this->ammo--;
        $this->lastAttackTick = $event->getTickId();

        $this->recoilModifier($event);
        $event->applyRecoil(...$this->getSpreadOffsets());
        return $event->fire();
    }

    /**
     * @return float[] [offsetHorizontal, offsetVertical]
     */
    protected function getSpreadOffsets(): array
    {
        return [0.0, 0.0];
    }

    protected function resetRecoil(int $tickId = 0): void
    {
        $this->lastRecoilTick = $tickId;
        $this->lastRecoilBulletCount = 1;
    }

    private function recoilModifier(Attackable $event): void
    {
        if ($this->recoilResetTicks === 0) {
            return;
        }

        $tickId = $event->getTickId();
        if ($this->lastRecoilTick === 0 || $this->lastRecoilTick + $this->recoilResetTicks < $tickId) { // recoil is fully reset
            $this->resetRecoil($tickId);
        }

        [$offsetHorizontal, $offsetVertical] = static::recoilPattern[$this->lastRecoilBulletCount - 1] ?? [0, 0];
        if ($this->lastRecoilTick + $this->fireRateTicks >= $tickId) { // maximum (full spraying) recoil
            $event->applyRecoil($offsetHorizontal, $offsetVertical);
        } else { // partial recoil
            $portion = 1 - (min($this->recoilResetTicks, $tickId - $this->lastRecoilTick) / $this->recoilResetTicks);
            $event->applyRecoil($offsetHorizontal * $portion, $offsetVertical * $portion);
        }
        $this->lastRecoilTick = $tickId;
        $this->lastRecoilBulletCount++;
    }

    public function attackSecondary(Attackable $event): ?AttackResult
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
                    $this->ammoReserve = (int)abs(static::magazineCapacity - $newAmmo);
                    $newAmmo = min(static::magazineCapacity, $newAmmo);
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
