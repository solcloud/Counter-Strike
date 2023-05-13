<?php

namespace cs\Traits\Player;

use cs\Core\Setting;
use cs\Enum\ItemType;
use cs\Enum\SoundType;
use cs\Equipment\Bomb;
use cs\Equipment\Grenade;
use cs\Event\AttackEvent;
use cs\Event\AttackResult;
use cs\Event\SoundEvent;
use cs\Event\ThrowEvent;
use cs\Interface\Attackable;
use cs\Interface\AttackEnable;
use cs\Interface\Reloadable;
use cs\Weapon\AmmoBasedWeapon;
use cs\Weapon\Knife;

trait AttackTrait
{

    public function attack(): ?AttackResult
    {
        if ($this->isAttacking) {
            return null;
        }
        $this->isAttacking = true;
        $item = $this->getEquippedItem();

        if ($item instanceof Bomb) {
            $this->world->tryPlantBomb($this);
            return null;
        }

        if (!($item instanceof AttackEnable)) {
            return null; // @codeCoverageIgnore
        }
        if ($item instanceof AmmoBasedWeapon && $item->getAmmo() === 0) {
            $sound = new SoundEvent($this->getPositionClone()->addY($this->getSightHeight()), SoundType::ATTACK_NO_AMMO);
            $this->world->makeSound($sound->setPlayer($this)->setItem($item));
            return null;
        }
        if (!$item->canAttack($this->world->getTickId())) {
            return null;
        }

        $result = $item->attack($this->createAttackEvent($item));
        if ($result) {
            $sound = new SoundEvent($this->getPositionClone()->addY($this->getSightHeight()), SoundType::ITEM_ATTACK);
            $this->world->makeSound($sound->setPlayer($this)->setItem($item));
            return $this->processAttackResult($result);
        }
        return null; // @codeCoverageIgnore
    }

    public function attackSecondary(): ?AttackResult
    {
        $item = $this->getEquippedItem();
        if (!($item instanceof AttackEnable)) {
            return null; // @codeCoverageIgnore
        }

        if ($item instanceof Knife || $item instanceof Grenade) {
            $result = $item->attackSecondary($this->createAttackEvent($item));
            if ($result) {
                $sound = new SoundEvent($this->getPositionClone()->addY($this->getSightHeight()), SoundType::ITEM_ATTACK2);
                $this->world->makeSound($sound->setPlayer($this)->setItem($item));
                return $this->processAttackResult($result);
            }
        }

        return null; // @codeCoverageIgnore
    }

    private function processAttackResult(AttackResult $result): AttackResult
    {
        $this->inventory->earnMoney($result->getMoneyAward());
        return $result;
    }

    private function getThrowSpeed(): int
    {
        $base = Setting::throwSpeed();
        if ($this->isMoving() && $this->isRunning()) {
            $base *= 1.2;
        }
        if ($this->isJumping()) {
            $base *= 1.1;
        }
        return (int)ceil($base);
    }

    protected function createAttackEvent(AttackEnable $item): Attackable
    {
        $origin = $this->getPositionClone();
        $origin->addY($this->getSightHeight());

        if ($item instanceof Grenade) {
            $event = new ThrowEvent(
                $this,
                $this->world,
                $origin,
                $item,
                $this->getSight()->getRotationHorizontal(),
                $this->getSight()->getRotationVertical(),
                $item->getBoundingRadius(),
                $this->getThrowSpeed(),
            );
            $this->world->throw($event);
            return $event;
        }
        $event = new AttackEvent(
            $this->world,
            $origin,
            $item,
            $this->getSight()->getRotationHorizontal(),
            $this->getSight()->getRotationVertical(),
            $this->getId(),
            $this->isPlayingOnAttackerSide(),
        );

        $this->applyMovementRecoil($event);
        return $event;
    }

    private function applyMovementRecoil(AttackEvent $event): void
    {
        $item = $this->getEquippedItem();
        if (in_array($item->getType(), [ItemType::TYPE_KNIFE, ItemType::TYPE_BOMB, ItemType::TYPE_GRENADE], true)) {
            return;
        }
        // fixme: better offsets value calculations for each item and smallest group range randomness as possible

        if ($this->isFlying()) {
            $offsetHorizontal = $item->getType() === ItemType::TYPE_WEAPON_PRIMARY ? rand(15, 25) : rand(10, 11);
            $offsetVertical = $item->getType() === ItemType::TYPE_WEAPON_PRIMARY ? rand(8, 16) : rand(6, 12);
            $event->applyRecoil((rand(0, 1) === 1 ? -1 : 1) * $offsetHorizontal, (rand(0, 1) === 1 ? -1 : 1) * $offsetVertical);
            return;
        }

        if ($this->isCrouching()) {
            return;
        }

        if ($this->isMoving()) {
            if ($this->isWalking()) {
                $offsetHorizontal = $item->getType() === ItemType::TYPE_WEAPON_PRIMARY ? rand(2, 4) : (rand(0, 1) === 1 ? rand(10, 19) / 10 : rand(4, 12) / 10);
                $offsetVertical = $item->getType() === ItemType::TYPE_WEAPON_PRIMARY ? rand(2, 3) : (rand(0, 1) === 1 ? rand(8, 14) / 10 : rand(7, 9) / 10);
                $event->applyRecoil((rand(0, 1) === 1 ? -1 : 1) * $offsetHorizontal, (rand(0, 1) === 1 ? -1 : 1) * $offsetVertical);
            } elseif ($this->isRunning()) {
                $offsetHorizontal = $item->getType() === ItemType::TYPE_WEAPON_PRIMARY ? rand(3, 9) : rand(3, 7);
                $offsetVertical = $item->getType() === ItemType::TYPE_WEAPON_PRIMARY ? rand(5, 15) : rand(4, 6);
                $event->applyRecoil((rand(0, 1) === 1 ? -1 : 1) * $offsetHorizontal, (rand(0, 1) === 1 ? -1 : 1) * $offsetVertical);
            }
        }
    }

    public function reload(): void
    {
        $item = $this->getEquippedItem();
        if (!($item instanceof Reloadable)) {
            return;
        }

        $event = $item->reload();
        if ($event) {
            $this->addEvent($event, $this->eventIdPrimary);
            $sound = new SoundEvent($this->getPositionClone()->addY($this->getSightHeight()), SoundType::ITEM_RELOAD);
            $this->world->makeSound($sound->setPlayer($this)->setItem($item));
        }
    }

}
