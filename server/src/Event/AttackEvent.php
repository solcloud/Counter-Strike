<?php

namespace cs\Event;

use cs\Core\Bullet;
use cs\Core\GameException;
use cs\Core\Item;
use cs\Core\Point;
use cs\Core\World;
use cs\Weapon\AmmoBasedWeapon;
use cs\Weapon\Knife;

final class AttackEvent
{
    private Item $item;

    public function __construct(
        private World $world,
        private Point $origin,
        private float $angleHorizontal,
        private float $angleVertical,
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
        $checkDistance = $bullet->getDistanceTraveled();

        // OPTIMIZATION_1: Precalculate sin/cos
        $sinV = sin(deg2rad($this->angleVertical));
        $sinH = sin(deg2rad($this->angleHorizontal));
        $cosH = cos(deg2rad($this->angleHorizontal));
        $nearbyInt = function (float $float): int {
            return (int)($float > 0 ? $float + .5 : $float - .5);
        };

        $newPos = $this->origin->clone();
        $prevPos = $newPos->clone();
        while ($bullet->isActive()) {
            $distance = $bullet->incrementDistance();

            // OPTIMIZATION_1: Inline Util::movementXYZ() here
            $y = $distance * $sinV;
            $z = $nearbyInt(sqrt(pow($distance, 2) - pow($y, 2)));
            $newPos->set($this->origin->x + $nearbyInt($sinH * $z), $this->origin->y + $nearbyInt($y), $this->origin->z + $nearbyInt($cosH * $z));
            if ($newPos->equals($prevPos)) {
                continue;
            }

            $prevPos->setFrom($newPos);
            $bullet->move($newPos);
            if ($distance > $checkDistance) {
                $checkDistance *= 4;
                $this->world->optimizeBulletHitCheck($bullet, $this->angleHorizontal, $this->angleVertical);
            }
            $hits = $this->world->calculateHits($bullet);
            if ($hits === []) {
                continue;
            }

            foreach ($hits as $hit) {
                $bullet->lowerDamage($hit->getHitAntiForce($newPos));
                $result->addHit($hit);
                $this->world->bulletHit($hit, $bullet, $hit->wasHeadShot());
                if (!$bullet->isActive()) {
                    break;
                }
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

    public function getTickId(): int
    {
        return $this->world->getTickId();
    }

}
