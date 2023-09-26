<?php

namespace cs\Event;

use Closure;
use cs\Core\DropItem;
use cs\Core\Item;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Setting;
use cs\Core\Util;
use cs\Core\World;
use cs\Enum\SoundType;

class DropEvent extends Event
{

    private string $id;
    private Point $origin;
    private DropItem $dropItem;
    private ?Closure $onLand = null;
    private float $angleHorizontal;
    private float $angleVertical;
    private float $velocity;
    private float $time = 0.0;
    private float $timeIncrement;

    public function __construct(private readonly Player $player, private readonly Item $item, private readonly World $world)
    {
        $this->id = "drop-{$this->player->getId()}-{$this->item->getId()}-{$this->world->getTickId()}";
        $this->origin = $this->player->getSightPositionClone();
        $this->dropItem = new DropItem($this->id, $this->item, $this->origin->clone());
        $this->angleHorizontal = $player->getSight()->getRotationHorizontal();
        $this->angleVertical = $player->getSight()->getRotationVertical();
        $this->velocity = ($player->isMoving() || $player->isJumping()) ? 30.0 : 20.0;
        $this->timeIncrement = 1 / Util::millisecondsToFrames(100);
        if (!$this->player->isAlive()) {
            $this->velocity = 7;
            $this->timeIncrement = 7;
        }
    }

    public function onLand(Closure $callback): void
    {
        $this->onLand = $callback;
    }

    private function finish(): void
    {
        $this->runOnCompleteHooks();
    }

    public function process(int $tick): void
    {
        $dropPosition = $this->dropItem->getPosition();
        $this->time += $this->timeIncrement;
        $directionX = Util::directionX($this->angleHorizontal);
        $directionZ = Util::directionZ($this->angleHorizontal);

        $pos = $dropPosition->clone();
        $x = $pos->x;
        $y = $pos->y;
        $z = $pos->z;

        $targetX = Util::nearbyInt($this->velocity * $this->time * cos(deg2rad($this->angleVertical)));
        $targetY = $y + Util::nearbyInt($this->velocity * $this->time * sin(deg2rad($this->angleVertical)) - (.5 * Util::GRAVITY * $this->time * $this->time));
        [$targetX, $targetZ] = Util::rotatePointY($this->angleHorizontal, 0, $targetX);
        $targetX += $x;
        $targetZ += $z;
        $maxStep = max(abs($targetX - $x), abs($targetY - $y), abs($targetZ - $z));
        if ($maxStep === 0) {
            return;
        }

        $item = $this->item;
        $world = $this->world;
        $player = $this->player;
        $radius = $this->dropItem->getBoundingRadius();
        $height = $this->dropItem->getHeight();
        $playerId = ($this->time > 2 && $this->angleVertical > 60 ? -1 : $player->getId());
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

            $collisionPlayer = $world->isCollisionWithOtherPlayers($playerId, $pos, $radius, $height);
            if ($collisionPlayer && $collisionPlayer->getInventory()->pickup($item)) {
                $sound = new SoundEvent($pos->clone(), SoundType::ITEM_PICKUP);
                $this->world->makeSound($sound->setPlayer($collisionPlayer)->setItem($item)->addExtra('id', $this->id));
                $this->finish();
                return;
            }
            if ($world->isWallOrFloorCollision($dropPosition, $pos, $radius)) {
                $this->angleVertical = -90.0;

                $floorCandidate = $world->findFloorSquare($pos, $radius);
                if ($floorCandidate) {
                    $dropPosition->setFrom($pos);
                    if ($this->onLand) {
                        call_user_func($this->onLand, $this);
                    }
                    $sound = new SoundEvent($pos->clone(), SoundType::ITEM_DROP_LAND);
                    $this->world->makeSound($sound->setPlayer($player)->setItem($item)->addExtra('id', $this->id));
                    $this->finish();
                    return;
                }

                break;
            }

            $dropPosition->setFrom($pos);
        }

        $sound = new SoundEvent($pos->clone(), SoundType::ITEM_DROP_AIR);
        $this->world->makeSound($sound->setPlayer($player)->setItem($item)->addExtra('id', $this->id));
    }

    public function getDropItem(): DropItem
    {
        return $this->dropItem;
    }

    public function serialize(): array
    {
        return [
            'id'   => $this->id,
            'item' => $this->item->toArray(),
        ];
    }

}
