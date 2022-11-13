<?php

namespace cs\HitGeometry;

use cs\Core\Collision;
use cs\Core\GameException;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Util;
use cs\Interface\HitIntersect;

class SphereHitBox implements HitIntersect
{

    public function __construct(protected Point $relativeCenter, public readonly int $radius)
    {
        if ($this->radius <= 0) {
            throw new GameException("Radius needs to be bigger than zero");
        }
    }

    public function intersect(Player $player, Point $point): bool
    {
        $center = $this->calculateWorldCoordinate($player);
        return Collision::pointWithSphere($point, $center, $this->radius);
    }

    public function calculateWorldCoordinate(Player $player, Point $centerModifier = new Point()): Point
    {
        $angle = $player->getSight()->getRotationHorizontal();
        $center = $player->getPositionImmutable()->add($centerModifier);
        $point = $center->clone()->add($this->relativeCenter);

        if ($angle === 0.0) {
            return $point;
        }

        [$x, $z] = Util::rotatePointY($angle, $point->x, $point->z, $center->x, $center->z);
        return $point->setX($x)->setZ($z);
    }

    public function getRelativeCenter(): Point
    {
        return $this->relativeCenter;
    }

}
