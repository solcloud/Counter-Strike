<?php

namespace cs\HitGeometry;

use cs\Core\Collision;
use cs\Core\GameException;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Util;
use cs\Interface\HitIntersect;

final class SphereHitBox implements HitIntersect
{
    private Point $point;

    public function __construct(private readonly Point $relativeCenter, public readonly int $radius)
    {
        if ($this->radius <= 0) {
            throw new GameException("Radius needs to be bigger than zero");
        }
        $this->point = new Point();
    }

    public function intersect(Player $player, Point $point): bool
    {
        return Collision::pointWithSphere($point, $this->calculateWorldCoordinate($player), $this->radius);
    }

    public function calculateWorldCoordinate(Player $player, ?Point $centerModifier = null): Point
    {
        [$x, $z] = Util::rotatePointY($player->getSight()->getRotationHorizontal(), $this->relativeCenter->x, $this->relativeCenter->z);
        $this->point->setFrom($player->getReferenceToPosition());
        $this->point->addPart($x, $this->relativeCenter->y, $z);

        if ($centerModifier) {
            $this->point->add($centerModifier);
        }

        return $this->point;
    }

}
