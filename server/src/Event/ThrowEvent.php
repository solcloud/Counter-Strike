<?php

namespace cs\Event;

use cs\Core\Bullet;
use cs\Core\GameException;
use cs\Core\Item;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Util;
use cs\Core\World;
use cs\Enum\SoundType;
use cs\Equipment\Flashbang;
use cs\Equipment\Grenade;
use cs\Equipment\HighExplosive;
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
    private bool $lastBounce = false;
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
    )
    {
        if ($this->velocity <= 0) {
            throw new GameException("Velocity needs to be positive"); // @codeCoverageIgnore
        }

        $this->position = $origin->clone();
        $this->lastEventPosition = $origin->clone();
        $this->ball = new BallCollider($this->world, $origin, $radius);
        $this->needsToLandOnFloor = !($this->item instanceof Flashbang || $this->item instanceof HighExplosive);
        $this->timeIncrement = 1 / Util::millisecondsToFrames(150); // fixme some good value or velocity or gravity :)
        $this->tickMax = $this->getTickId() + Util::millisecondsToFrames($this->needsToLandOnFloor ? 99999 : 1200);
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
            $this->runOnCompleteHooks();
            return;
        }

        if ($this->tickMax > 0) {
            $point->addY(-$this->radius);
            $this->tickMax = 0;
        }
        for ($i = 1; $i <= ceil(Util::GRAVITY * 2); $i++) {
            if (!$this->world->findFloor($point, $this->radius)) {
                $point->addY(-1);
                continue;
            }

            $this->makeEvent($point->addY($this->radius), SoundType::GRENADE_LAND);
            $this->runOnCompleteHooks();
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
        $maxStep = max(abs($targetX - $x), abs($targetY - $y), abs($targetZ - $z)); // fixme cap to some max (min value) and do partial float round sub-step for lower targets based on maxTargetDistance
        if ($maxStep === 0) {
            return;
        }
        if ($this->lastBounce && $this->angleVertical >= 0 && $targetY < $y) { // gravity force too strong for going up
            $this->finishLanding($pos);
            return;
        }
        $this->lastBounce = false;

        $directionY = ($targetY <=> $y);
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

            if (!$this->ball->hasCollision($pos, $this->angleHorizontal, $this->angleVertical)) {
                continue;
            }

            $this->setAngles($this->ball->getResolutionAngleHorizontal(), $this->ball->getResolutionAngleVertical());
            $this->bounceCount++;
            $this->velocity = $this->velocity / ($this->bounceCount > 4 ? $this->bounceCount : 1.5);
            if ($this->velocity < 1) { // fixme some value based on velocity and gravity that will give lowest possible (angle 0.01/90) distance < 1
                $this->finishLanding($pos);
                return;
            }

            $this->makeEvent($pos, SoundType::GRENADE_BOUNCE);
            $this->lastBounce = true;
            $this->time = 0.0;
            $pos->setFrom($this->ball->getLastValidPosition());
            return;
        }

        $this->makeEvent($pos, SoundType::GRENADE_AIR);
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

    /**
     * @codeCoverageIgnore
     */
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
