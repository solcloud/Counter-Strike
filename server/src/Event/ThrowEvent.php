<?php

namespace cs\Event;

use cs\Core\Bullet;
use cs\Core\Item;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Util;
use cs\Core\World;
use cs\Enum\ItemId;
use cs\Enum\SoundType;
use cs\Equipment\Flashbang;
use cs\Equipment\Grenade;
use cs\HitGeometry\BallCollider;
use cs\Interface\Attackable;

final class ThrowEvent extends Event implements Attackable
{

    private string $id;
    private float $time = 0.0;
    private float $timeIncrement;
    private Point $position;
    private Point $lastEventPosition;
    private BallCollider $ball;
    private int $bounceCount = 0;
    private bool $needsToLandOnFloor;
    private int $tickMax;

    public function __construct(
        private readonly Player  $player,
        private readonly World   $world,
        Point                    $origin,
        private readonly Grenade $item,
        private float            $angleHorizontal,
        private float            $angleVertical,
        public readonly int      $radius,
        private float            $velocity,
        int                      $maxTimeMs = 99999,
    )
    {
        $this->position = $origin->clone();
        $this->lastEventPosition = $origin->clone();
        $this->ball = new BallCollider($this->world, $origin, $radius);
        $this->needsToLandOnFloor = ($item->getId() !== ItemId::$map[Flashbang::class]);
        $this->timeIncrement = 1 / Util::millisecondsToFrames(150); // fixme some good value or velocity or gravity :)
        $this->tickMax = $this->getTickId() + Util::millisecondsToFrames($maxTimeMs);
    }

    private function makeEvent(Point $point, SoundType $type): Event
    {
        /** @var Item $item */
        $item = $this->item;
        $event = (new SoundEvent($point->clone(), $type))
            ->setItem($item)
            ->setPlayer($this->player)
            ->addExtra('id', $this->id)
        ;
        $this->world->makeSound($event);
        $this->lastEventPosition->setFrom($point);
        return $event;
    }

    private function finishLanding(Point $point): void
    {
        if (!$this->needsToLandOnFloor) {
            $this->makeEvent($point, SoundType::GRENADE_LAND);
            foreach ($this->onComplete as $func) {
                call_user_func($func, $this);
            }
            return;
        }

        $candidate = $point->clone();
        $candidate->addY(-$this->radius);
        if ($this->world->findFloor($candidate, $this->radius)) {
            $this->makeEvent($point, SoundType::GRENADE_LAND);
            foreach ($this->onComplete as $func) {
                call_user_func($func, $this);
            }
            return;
        }

        while (true) { // apply gravity when low velocity, fixme not optimal - find better minimal velocity or at least split into multiple tick instead of single while true
            if (!$this->world->findFloor($candidate->addY(-1), $this->radius)) {
                continue;
            }

            $this->makeEvent($candidate->addY($this->radius), SoundType::GRENADE_LAND);
            foreach ($this->onComplete as $func) {
                call_user_func($func, $this);
            }
            return;
        }
    }

    public function process(int $tick): void
    {
        if ($this->tickMax < $tick) {
            $this->finishLanding($this->position);
            return;
        }

        $pos = $this->position;
        $this->time += $this->timeIncrement;
        $directionX = Util::directionX($this->angleHorizontal);
        $directionZ = Util::directionZ($this->angleHorizontal);

        $x = $pos->x;
        $y = $pos->y;
        $z = $pos->z;
        $targetX = Util::nearbyInt($this->velocity * $this->time * cos(deg2rad($this->angleVertical)));
        $targetY = $y + Util::nearbyInt($this->velocity * $this->time * sin(deg2rad($this->angleVertical)) - (.5 * Util::GRAVITY * $this->time * $this->time));
        [$targetX, $targetZ] = Util::rotatePointY($this->angleHorizontal, 0, $targetX);
        $targetX += $x;
        $targetZ += $z;
        $directionY = ($targetY <=> $y);
        $maxStep = max(abs($targetX - $x), abs($targetY - $y), abs($targetZ - $z)); // fixme cap to some max (min value) and do partial float round sub-step for lower targets based on maxTargetDistance
        if ($maxStep === 0) {
            return;
        }
        for ($step = 1; $step <= $maxStep; $step++) {
            if ($targetX !== $x) {
                $x += $directionX;
                $pos->x = $x;
            }
            if ($targetY !== $y) {
                $y += $directionY;
                $pos->y = $y;
            }
            if ($targetZ !== $z) {
                $z += $directionZ;
                $pos->z = $z;
            }

            $collision = $this->ball->resolveCollision($pos);
            if (!$collision) {
                continue;
            }

            [$newPosition, $angleH, $angleV] = $collision;
            if ($angleH === null || $angleV === null) {
                $this->finishLanding($newPosition);
                return;
            }

            $this->setAngles($angleH, $angleV);
            $pos->setFrom($newPosition);
            $this->bounceCount++;
            $this->velocity = $this->velocity / ($this->bounceCount > 4 ? $this->bounceCount : 1.5);
            if ($this->velocity < 1) { // fixme some value based on velocity and gravity that will give lowest possible (angle 0.01/90) distance < 1
                $this->finishLanding($pos);
                return;
            }

            $this->makeEvent($pos, SoundType::GRENADE_BOUNCE);
            $this->time = 0.0;
            return;
        }

        $this->makeEvent(Util::lerpPoint($this->lastEventPosition, $pos, 0.6), SoundType::GRENADE_AIR); // fixme remove lerp and precalculate next bounce so there is no visual backtrack or ghosting
    }

    public function fire(): AttackResult
    {
        $this->id = "{$this->player->getId()}-{$this->item->getId()}-{$this->getTickId()}";
        $this->player->getInventory()->removeEquipped();
        $this->velocity *= $this->item->getSpeedMultiplier();
        $bullet = new Bullet($this->item);
        return new AttackResult($bullet);
    }

    public function getTickId(): int
    {
        return $this->world->getTickId();
    }

    public function applyRecoil(float $offsetHorizontal, float $offsetVertical): void
    {
        // no recoil on throw
    }

    public function setAngles(float $angleHorizontal, float $angleVertical): void
    {
        $this->angleHorizontal = $angleHorizontal;
        if ($angleVertical < 0) {
            $angleVertical /= 2;
        }
        $this->angleVertical = $angleVertical;
    }

    public function serialize(): array
    {
        return [
            'id'       => $this->id,
            'radius'   => $this->radius,
            'item'     => $this->item->toArray(),
            'position' => $this->position->toArray(),
        ];
    }

}
