<?php

namespace cs\Event;

use cs\Core\Point;
use cs\Core\World;
use cs\Interface\Attackable;
use cs\Interface\AttackEnable;

final class AttackEvent implements Attackable
{

    public function __construct(
        private World        $world,
        private Point        $origin,
        private AttackEnable $item,
        private float        $angleHorizontal,
        private float        $angleVertical,
        private int          $playerId,
        private bool         $playingOnAttackerSide,
    )
    {
    }

    public function fire(): AttackResult
    {
        $bullet = $this->item->createBullet();
        $bullet->setOriginPlayer($this->playerId, $this->playingOnAttackerSide, $this->origin->clone());
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
        $this->world->getBacktrack()->saveState();
        while ($bullet->isActive()) {
            $distance = $bullet->incrementDistance();

            // OPTIMIZATION_1: Inline Util::movementXYZ() here
            $y = $distance * $sinV;
            $z = $nearbyInt(sqrt(($distance * $distance) - ($y * $y)));
            $newPos->set($this->origin->x + $nearbyInt($sinH * $z), $this->origin->y + $nearbyInt($y), $this->origin->z + $nearbyInt($cosH * $z));
            if ($newPos->equals($prevPos)) {
                continue;
            }

            $prevPos->setFrom($newPos);
            $bullet->move($newPos);
            if ($distance > $checkDistance) {
                $checkDistance *= 3;
                $this->world->optimizeBulletHitCheck($bullet);
            }

            foreach ($this->world->calculateHits($bullet, $newPos) as $hit) {
                $bullet->lowerDamage($hit->getHitAntiForce($newPos));
                $result->addHit($hit);
                $this->world->bulletHit($hit, $bullet, $hit->wasHeadShot());
                if (!$bullet->isActive()) {
                    break;
                }
            }
        }
        $this->world->getBacktrack()->restoreState();
        return $result;
    }

    public function applyRecoil(float $offsetHorizontal, float $offsetVertical): void
    {
        $this->angleHorizontal += $offsetHorizontal;
        $this->angleVertical += $offsetVertical;
    }

    public function getTickId(): int
    {
        return $this->world->getTickId();
    }

}
