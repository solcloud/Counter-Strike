<?php

namespace cs\Core;

use cs\Enum\HitBoxType;
use cs\HitGeometry;
use cs\Interface\Hittable;

class PlayerCollider
{

    /** @var HitBox[] */
    private array $hitBoxes = [];
    public readonly int $playerId;

    public function __construct(private Player $player)
    {
        $this->playerId = $player->getId();

        // NOTE: only first hit box count so do good geometry and array priorities
        $this->hitBoxes[] = new HitBox($this->player, HitBoxType::HEAD, new HitGeometry\HitBoxHead());
        $this->hitBoxes[] = new HitBox($this->player, HitBoxType::BACK, new HitGeometry\HitBoxBack());
        $this->hitBoxes[] = new HitBox($this->player, HitBoxType::STOMACH, new HitGeometry\HitBoxStomach());
        $this->hitBoxes[] = new HitBox($this->player, HitBoxType::CHEST, new HitGeometry\HitBoxChest());
        $this->hitBoxes[] = new HitBox($this->player, HitBoxType::LEG, new HitGeometry\HitBoxLegs());
    }

    public function roundReset(): void
    {
        foreach ($this->hitBoxes as $hitBox) {
            $hitBox->reset();
        }
    }

    public function tryHitPlayer(Bullet $bullet, Backtrack $backtrack): ?Hittable
    {
        $bp = $bullet->getPosition();
        foreach ($backtrack->getStates() as $state) {
            $backtrack->apply($state, $this->playerId);

            if (false === Collision::pointWithCylinder(
                    $bp,
                    $this->player->getReferenceToPosition(),
                    $this->player->getBoundingRadius(),
                    $this->player->getHeadHeight()
                )) {
                continue;
            }

            foreach ($this->hitBoxes as $hitBox) {
                if ($hitBox->intersect($bullet)) {
                    $hitBox->registerHit($bullet);
                    return $hitBox;
                }
            }
        }

        return null;
    }

    public function collide(Point $point, int $radius, int $height): bool
    {
        return (
            $this->player->isAlive() &&
            Collision::cylinderWithCylinder(
                $this->player->getReferenceToPosition(), $this->player->getBoundingRadius(), $this->player->getHeadHeight(),
                $point, $radius, $height
            )
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
