<?php

namespace cs\Core;

final class DynamicFloor extends Floor
{

    private Point $pointReference;

    public function __construct(private Player $player)
    {
        $this->pointReference = $player->getReferenceToPosition();
    }

    public function getY(): int
    {
        return $this->pointReference->y + $this->player->getHeadHeight() + 1;
    }

    public function intersect(Point $point, int $radius = 0): bool
    {
        return (
            $this->pointReference->y + $this->player->getHeadHeight() + 1 === $point->y
            && Collision::pointWithCircle(
                $this->pointReference->x,
                $this->pointReference->z,
                $point->x,
                $point->z,
                $this->player->getBoundingRadius() + $radius
            )
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public function getHitAntiForce(Point $point): int
    {
        throw new GameException('Should not be here');
    }

    /**
     * @codeCoverageIgnore
     */
    public function getPlane(): string
    {
        throw new GameException('Should not be here');
    }

}
