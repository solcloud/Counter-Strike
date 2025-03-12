<?php

namespace cs\Core;

final class DynamicFloor extends Floor
{

    private Point $pointReference;

    public function __construct(private readonly Player $player)
    {
        $this->pointReference = $player->getReferenceToPosition();
    }

    #[\Override]
    public function getY(): int
    {
        return $this->pointReference->y + $this->player->getHeadHeight() + 1;
    }

    #[\Override]
    public function intersect(Point $point, int $radius): bool
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
    #[\Override]
    public function getHitAntiForce(Point $point): int
    {
        GameException::invalid();
    }

    /**
     * @codeCoverageIgnore
     */
    #[\Override]
    public function getPlane(): string
    {
        GameException::invalid();
    }

}
