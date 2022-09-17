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

        $point = $bullet->getPosition();
        $lowPoint = $this->player->getPositionImmutable();
        $radius = $this->player->getBoundingRadius();

        if ($point->y < $lowPoint->y || $point->y > $lowPoint->y + $this->player->getHeadHeight()) {
            return false;
        }
        if ($point->x < $lowPoint->x - $radius || $point->x > $lowPoint->x + $radius) {
            return false;
        }
        if ($point->z < $lowPoint->z - $radius || $point->z > $lowPoint->z + $radius) {
            return false;
        }

        return true;
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

    public function collide(Point $base, int $radius, int $height): bool
    {
        $pp = $this->player->getPositionImmutable();
        if ($pp->y > $base->y + $height) {
            return false;
        }
        if ($pp->y + $this->player->getHeadHeight() < $base->y) {
            return false;
        }

        return Collision::circleWithCircle($base->to2D('xz'), $radius, $pp->to2D('xz'), $this->player->getBoundingRadius());
    }

}
