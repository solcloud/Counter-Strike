<?php

namespace cs\HitGeometry;

use cs\Core\Collision;
use cs\Core\Player;
use cs\Core\Point;
use cs\Interface\HitIntersect;

class SphereGroupHitBox implements HitIntersect
{
    /** @var SphereHitBox[] */
    private array $parts = [];
    private Point $point;

    public function __construct(public readonly bool $usePlayerHeight)
    {
        $this->point = new Point();
    }

    public function intersect(Player $player, Point $point): bool
    {
        $this->point->setScalar(0)->addY($this->usePlayerHeight ? $player->getHeadHeight() : 0);
        foreach ($this->getParts($player) as $part) {
            if (Collision::pointWithSphere($point, $part->calculateWorldCoordinate($player, $this->point), $part->radius)) {
                return true;
            }
        }

        return false;
    }

    public function addHitBox(Point $relativeCenter, int $radius): self
    {
        $this->parts[] = $this->createHitBox($relativeCenter, $radius);
        return $this;
    }

    public function createHitBox(Point $relativeCenter, int $radius): SphereHitBox
    {
        return new SphereHitBox($relativeCenter, $radius);
    }

    /**
     * @return SphereHitBox[]
     */
    public function getParts(Player $player): array
    {
        return $this->parts;
    }

}
