<?php

namespace cs\Event;

use cs\Core\Bullet;
use cs\Core\GameException;
use cs\Core\Item;
use cs\Core\Point;
use cs\Core\Util;
use cs\Core\World;
use cs\Weapon\AmmoBasedWeapon;
use cs\Weapon\Knife;

final class AttackEvent
{
    private Item $item;

    public function __construct(
        private World $world,
        private Point $origin,
        private int   $angleHorizontal,
        private int   $angleVertical,
        private int   $playerId,
        private bool  $playingOnAttackerSide,
    )
    {
    }

    public function process(): AttackResult
    {
        $bullet = $this->createBullet();
        $bullet->setOriginPlayer($this->playerId, $this->playingOnAttackerSide);
        $result = new AttackResult($bullet);

        while ($bullet->isActive()) {
            $newPos = $this->nextPosition($bullet);
            $bullet->move($newPos);
            $hits = $this->world->calculateHits($bullet);
            if ($hits === []) {
                continue;
            }

            foreach ($hits as $hit) {
                $bullet->lowerDamage($hit->getHitAntiForce());
                $result->addHit($hit);
                $this->world->bulletHit($hit, $newPos->clone());
            }
        }
        return $result;
    }

    public function setItem(Item $item): void
    {
        $this->item = $item;
    }

    private function createBullet(): Bullet
    {
        if ($this->item instanceof AmmoBasedWeapon) {
            return $this->item->createBullet();
        }

        if ($this->item instanceof Knife) {
            return $this->item->createBullet();
        }

        GameException::notImplementedYet("No bullet for item: " . get_class($this->item));
    }

    private function nextPosition(Bullet $bullet): Point
    {
        $bullet->incrementDistance();

        [$x, $y, $z] = Util::movementXYZ($this->angleHorizontal, $this->angleVertical, $bullet->getDistanceTraveled());
        $target = $this->origin->clone();
        $target->setX($target->x + $x)->setY($target->y + $y)->setZ($target->z + $z);
        return $target;
    }

    public function getTickId(): int
    {
        return $this->world->getTickId();
    }

}
