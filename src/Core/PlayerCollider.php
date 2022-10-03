<?php

namespace cs\Core;

use cs\Enum\HitBoxType;
use cs\HitGeometry;
use cs\Interface\Hittable;

class PlayerCollider
{

    /** @var HitBox[] */
    private array $hitBoxes = [];

    public function __construct(private Player $player)
    {
        // TODO: create real football player geometry in 3D software - fill it with bunch of rigid body spheres, bake it and export spheres coordinates
        // TODO: crouch, move animation
        // NOTE: only first hit box count so do good geometry and array priorities

        // HEAD
        $this->hitBoxes[] = new HitBox(
            $this->player,
            HitBoxType::HEAD,
            new HitGeometry\HitBoxHead(Setting::playerHeadRadius())
        );

        // BODY
        $this->hitBoxes[] = new HitBox(
            $this->player,
            HitBoxType::STOMACH,
            new HitGeometry\HitBoxBody($this->player->getBoundingRadius() - 2)
        );

        // Chest
        $this->hitBoxes[] = new HitBox(
            $this->player,
            HitBoxType::CHEST,
            new HitGeometry\HitBoxChest()
        );

        // Legs
        $this->hitBoxes[] = new HitBox(
            $this->player,
            HitBoxType::LEG,
            new HitGeometry\HitBoxLegs(Setting::playerHeadHeightStand() - Setting::playerHeadHeightCrouch())
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
        if (!$this->player->isAlive()) {
            return false;
        }

        return Collision::cylinderWithCylinder(
            $this->player->getPositionImmutable(), $this->player->getBoundingRadius(), $this->player->getHeadHeight(),
            $point, $radius, $height
        );
    }

    /**
     * @return HitBox[]
     */
    public function getHitBoxes(): array
    {
        return $this->hitBoxes;
    }

}
