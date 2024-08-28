<?php

namespace cs\HitGeometry;

use Closure;
use cs\Core\Collision;
use cs\Core\Player;
use cs\Core\Point;
use cs\Interface\HitIntersect;

class SphereGroupHitBox implements HitIntersect
{
    /** @var SphereHitBox[] */
    private array $parts = [];

    /**
     * @param ?Closure $centerPointModifier function (Player $player): Point {}
     */
    public function __construct(public readonly ?Closure $centerPointModifier = null)
    {
    }

    public function intersect(Player $player, Point $point): bool
    {
        /** @var Point $modifier */
        $modifier = $this->centerPointModifier ? call_user_func($this->centerPointModifier, $player) : null;
        foreach ($this->getParts($player) as $part) {
            $center = $part->calculateWorldCoordinate($player, $modifier);
            if (Collision::pointWithSphere($point, $center, $part->radius)) {
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
