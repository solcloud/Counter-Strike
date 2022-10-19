<?php

namespace cs\Traits\Player;

use cs\Enum\SoundType;
use cs\Event\AttackEvent;
use cs\Event\AttackResult;
use cs\Event\SoundEvent;
use cs\Interface\AttackEnable;
use cs\Interface\Reloadable;
use cs\Weapon\AmmoBasedWeapon;

trait AttackTrait
{

    public function attack(): ?AttackResult
    {
        if ($this->isAttacking) {
            return null;
        }
        $item = $this->getEquippedItem();
        if (!($item instanceof AttackEnable)) {
            return null;
        }
        if ($item instanceof AmmoBasedWeapon && $item->getAmmo() === 0) {
            $sound = new SoundEvent($this->getPositionImmutable()->addY($this->getSightHeight()), SoundType::ATTACK_NO_AMMO);
            $this->world->makeSound($sound->setPlayer($this)->setItem($item));
            return null;
        }

        $result = $item->attack($this->createAttackEvent());
        if ($result) {
            $sound = new SoundEvent($this->getPositionImmutable()->addY($this->getSightHeight()), SoundType::ITEM_ATTACK);
            $this->world->makeSound($sound->setPlayer($this)->setItem($item));
            return $this->processAttackResult($result);
        }
        return null;
    }

    public function attackSecondary(): ?AttackResult
    {
        return null; // TODO
    }

    private function processAttackResult(AttackResult $result): AttackResult
    {
        $this->inventory->earnMoney($result->getMoneyAward());
        return $result;
    }

    protected function createAttackEvent(): AttackEvent
    {
        $this->isAttacking = true;
        $origin = $this->getPositionImmutable();
        $origin->addY($this->getSightHeight());

        return new AttackEvent(
            $this->world,
            $origin,
            $this->getSight()->getRotationHorizontal(),
            $this->getSight()->getRotationVertical(),
            $this->getId(),
            $this->isPlayingOnAttackerSide(),
        );
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
            $sound = new SoundEvent($this->getPositionImmutable()->addY($this->getSightHeight()), SoundType::ITEM_RELOAD);
            $this->world->makeSound($sound->setPlayer($this)->setItem($item));
        }
    }

}
