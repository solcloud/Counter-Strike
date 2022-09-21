<?php

namespace cs\Core;

use cs\Enum\HitBoxType;
use cs\Interface\Hittable;

class PlayerCollider
{

    /** @var HitBox[] */
    private array $hitBoxes = [];

    public function __construct(private Player $player)
    {
        // TODO: LOL hit boxes, only first count so do good geometry and array priorities

        // HEAD
        $this->hitBoxes[] = new HitBox(
            $this->player,
            HitBoxType::HEAD,
            new HitBoxHead(
                Player::headRadius,
            )
        );
        // BODY
        $this->hitBoxes[] = new HitBox(
            $this->player,
            HitBoxType::CHEST,
            new HitBoxBody(new Point(), Player::bodyRadius)
        );
    }

    public function roundReset(): void
    {
        foreach ($this->hitBoxes as $hitBox) {
            $hitBox->reset();
        }
    }

    private function maybeIntersect(Bullet $bullet): bool
    {
        if (!$this->player->isAlive()) {
            return false;
        }

        return Collision::pointWithCylinder(
            $bullet->getPosition(),
            $this->player->getPositionImmutable(),
            $this->player->getBoundingRadius(),
            $this->player->getHeadHeight()
        );
    }

    public function tryHitPlayer(Bullet $bullet): ?Hittable
    {
        if (!$this->maybeIntersect($bullet)) {
            return null;
        }

        foreach ($this->hitBoxes as $hitBox) {
            if ($hitBox->intersect($bullet)) {
                $hitBox->registerHit($bullet);
                return $hitBox;
            }
        }

        return null;
    }

    public function getPlayerId(): int
    {
        return $this->player->getId();
    }

    public function collide(Point $point, int $radius, int $height): bool
    {
        return Collision::cylinderWithCylinder(
            $this->player->getPositionImmutable(), $this->player->getBoundingRadius(), $this->player->getHeadHeight(),
            $point, $radius, $height
        );
    }

}
