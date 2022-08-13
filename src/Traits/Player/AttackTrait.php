<?php

namespace cs\Traits\Player;

use cs\Event\AttackEvent;
use cs\Event\AttackResult;
use cs\Interface\AttackEnable;
use cs\Interface\Reloadable;

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

        $result = $item->attack($this->createAttackEvent());
        if ($result) {
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
        $origin->setY($this->getSightHeight());
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
        }
    }

}
