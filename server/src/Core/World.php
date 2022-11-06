<?php

namespace cs\Core;

use cs\Enum\InventorySlot;
use cs\Enum\SoundType;
use cs\Equipment\Bomb;
use cs\Event\SoundEvent;
use cs\Interface\Hittable;
use cs\Map\Map;

class World
{
    private const WALL_X = 0;
    private const WALL_Z = 1;

    private ?Map $map = null;
    /** @var PlayerCollider[] */
    private array $playersColliders = [];
    /** @var DropItem[] */
    private array $dropItems = [];
    /** @var array<int,array<int,Wall[]>> (x|z)BaseCoordinate:Wall[] */
    private array $walls = [];
    /** @var array<int,Floor[]> yCoordinate:Floor[] */
    private array $floors = [];
    /** @var array<int,int[]> */
    private array $spawnPositionTakes = [];
    /** @var array<int,Point[]> */
    private array $spawnCandidates;
    private int $lastBombPlantTick = -1;
    private int $lastBombPlantPlayer = -1;

    public function __construct(private Game $game)
    {
    }

    public function roundReset(): void
    {
        $this->spawnCandidates = [];
        $this->spawnPositionTakes = [];
        $this->dropItems = [];
        foreach ($this->playersColliders as $playerCollider) {
            $playerCollider->roundReset();
        }
    }

    public function loadMap(Map $map): void
    {
        $this->roundReset();
        $this->map = $map;

        $this->walls = [];
        foreach ($map->getWalls() as $wall) {
            $this->addWall($wall);
        }

        $this->floors = [];
        foreach ($map->getFloors() as $floor) {
            $this->addFloor($floor);
        }
    }

    public function addRamp(Ramp $ramp): void
    {
        foreach ($ramp->getBoxes() as $box) {
            $this->addBox($box);
        }
    }

    public function addBox(Box $box): void
    {
        foreach ($box->getWalls() as $wall) {
            $this->addWall($wall);
        }
        foreach ($box->getFloors() as $floor) {
            $this->addFloor($floor);
        }
    }

    public function addWall(Wall $wall): void
    {
        if ($wall->isWidthOnXAxis()) {
            $this->walls[self::WALL_Z][$wall->getBase()][] = $wall;
        } else {
            $this->walls[self::WALL_X][$wall->getBase()][] = $wall;
        }
    }

    public function addFloor(Floor $floor): void
    {
        $this->floors[$floor->getY()][] = $floor;
    }

    public function isWallAt(Point $point): ?Wall
    {
        if ($point->x < 0 || $point->z < 0) {
            return new Wall(new Point(-1, -1, -1), $point->z < 0);
        }

        $candidateZ = $point->to2D('xy');
        foreach (($this->walls[self::WALL_Z][$point->z] ?? []) as $wall) {
            if ($wall->intersect($candidateZ)) {
                return $wall;
            }
        }
        $candidateX = $point->to2D('zy');
        foreach (($this->walls[self::WALL_X][$point->x] ?? []) as $wall) {
            if ($wall->intersect($candidateX)) {
                return $wall;
            }
        }

        return null;
    }

    public function findFloor(Point $point, int $radius = 0): ?Floor
    {
        if ($point->y < 0) {
            throw new GameException("Y value cannot be lower than zero");
        }

        $floors = $this->floors[$point->getY()] ?? [];
        if ($floors === []) {
            return null;
        }
        $candidate = $point->to2D('xz');
        for ($r = 0; $r <= $radius; $r++) {
            foreach ($floors as $floor) {
                if ($floor->intersect($candidate, $r)) {
                    return $floor;
                }
            }
            if ($r > 3 && $r < $radius) {
                $r = min($r + 8, $radius - 1);
            }
        }

        return null;
    }

    public function isOnFloor(Floor $floor, Point $position, int $radius): bool
    {
        if ($floor->getY() !== $position->getY()) {
            return false;
        }

        return Collision::circleWithPlane($position->to2D('xz'), $radius, $floor);
    }

    public function getPlayerSpawnRotationHorizontal(bool $isAttacker, int $maxRandomOffset): int
    {
        $base = $isAttacker ? $this->getMap()->getSpawnRotationAttacker() : $this->getMap()->getSpawnRotationDefender();
        return $base + rand(-$maxRandomOffset, $maxRandomOffset);
    }

    public function getPlayerSpawnPosition(bool $isAttacker, bool $randomizeSpawnPosition): Point
    {
        $key = (int)$isAttacker;
        if (isset($this->spawnCandidates[$key])) {
            $source = $this->spawnCandidates[$key];
        } else {
            $source = ($isAttacker ? $this->getMap()->getSpawnPositionAttacker() : $this->getMap()->getSpawnPositionDefender());
            if ($randomizeSpawnPosition) {
                shuffle($source);
            }
            $this->spawnCandidates[$key] = $source;
        }

        foreach ($source as $index => $position) {
            if (isset($this->spawnPositionTakes[$key][$index])) {
                continue;
            }

            $this->spawnPositionTakes[$key][$index] = 1;
            return $position->clone();
        }

        $side = $isAttacker ? 'attacker' : 'defender';
        throw new GameException("Cannot find free spawn position for '{$side}' player");
    }

    public function addPlayerCollider(PlayerCollider $player): void
    {
        $this->playersColliders[] = $player;
    }

    public function tryPickDropItems(Player $player): void
    {
        foreach ($this->dropItems as $key => $dropItem) {
            if (!Collision::cylinderWithCylinder(
                $dropItem->getPosition(), $dropItem->getBoundingRadius(), $dropItem->getHeight(),
                $player->getPositionImmutable(), $player->getBoundingRadius(), $player->getHeadHeight()
            )) {
                continue;
            }

            if ($player->getInventory()->pickup($dropItem->getItem())) {
                $sound = new SoundEvent($dropItem->getPosition(), SoundType::ITEM_PICKUP);
                $this->makeSound($sound->setPlayer($player)->setItem($dropItem->getItem()));
                unset($this->dropItems[$key]);
            }
        }
    }

    public function dropItem(Player $player, Item $item): void
    {
        $dropItem = new DropItem($item);
        $dropPosition = $dropItem->calculateDropPosition($player, $this, $item);
        if ($dropPosition) {
            $this->dropItems[] = $dropItem;
            $sound = new SoundEvent($dropPosition, SoundType::ITEM_DROP);
            $this->makeSound($sound->setPlayer($player)->setItem($item));
        }
    }

    public function playerUse(Player $player): void
    {
        // TODO
    }

    /**
     * @return Hittable[]
     */
    public function calculateHits(Bullet $bullet): array
    {
        $hits = [];
        $alreadyHitPlayerIds = $bullet->getPlayerHitIds();
        $alreadyHitPlayerIds[$bullet->getOriginPlayerId()] = true; // cannot shoot self

        foreach ($this->playersColliders as $playerCollider) {
            if (isset($alreadyHitPlayerIds[$playerCollider->getPlayerId()])) {
                continue; // player already hit or self
            }

            $hitBox = $playerCollider->tryHitPlayer($bullet);
            if (!$hitBox) {
                continue;
            }

            $hits[] = $hitBox;
            $player = $hitBox->getPlayer();
            if ($player) {
                $bullet->addPlayerIdHit($player->getId());
                if ($hitBox->playerWasKilled()) {
                    $this->game->playerAttackKilledEvent($player, $bullet, $hitBox->wasHeadShot());
                }
            }
        }

        $floor = $this->findFloor($bullet->getPosition());
        if ($floor) {
            $hits[] = $floor;
        }

        $wall = $this->isWallAt($bullet->getPosition());
        if ($wall) {
            $hits[] = $wall;
        }

        return $hits;
    }

    public function makeSound(SoundEvent $soundEvent): void
    {
        $this->game->addSoundEvent($soundEvent);
    }

    public function canAttack(Player $player): bool
    {
        if ($this->game->isPaused()) {
            return false;
        }
        if (!$player->isAlive()) {
            return false;
        }

        return $player->getEquippedItem()->canAttack($this->getTickId());
    }

    public function canPlant(Player $player): bool
    {
        if ($player->getEquippedItem()->getSlot() !== InventorySlot::SLOT_BOMB) {
            return false;
        }
        if ($player->isFlying()) {
            return false;
        }
        if (!$player->isAlive()) {
            return false;
        }
        if ($this->game->isPaused()) {
            return false;
        }

        return Collision::pointWithBox($player->getPositionImmutable(), $this->getMap()->getPlantArea());
    }

    public function canBuy(Player $player): bool
    {
        if (!$this->game->playersCanBuy()) {
            return false;
        }

        return Collision::pointWithBox($player->getPositionImmutable(), $this->getMap()->getBuyArea($player->isPlayingOnAttackerSide()));
    }

    public function getTickId(): int
    {
        return $this->game->getTickId();
    }

    public function playerDiedToFallDamage(Player $playerDead): void
    {
        $this->game->playerFallDamageKilledEvent($playerDead);
    }

    public function checkXSideWallCollision(Point $center, int $height, int $radius): ?Wall
    {
        if ($center->x < 0) {
            return new Wall(new Point(-1, -1, -1), false);
        }

        $candidatePlane = $center->to2D('zy')->addX(-$radius);
        $width = 2 * $radius;
        foreach (($this->walls[self::WALL_X][$center->x] ?? []) as $wall) {
            if ($wall->getCeiling() === $center->y) {
                continue;
            }
            if (Collision::planeWithPlane($wall->getPoint2DStart(), $wall->width, $wall->height, $candidatePlane, $width, $height)) {
                return $wall;
            }
        }

        return null;
    }

    public function checkZSideWallCollision(Point $center, int $height, int $radius): ?Wall
    {
        if ($center->z < 0) {
            return new Wall(new Point(-1, -1, -1), true);
        }

        $candidatePlane = $center->to2D('xy')->addX(-$radius);
        $width = 2 * $radius;
        foreach (($this->walls[self::WALL_Z][$center->z] ?? []) as $wall) {
            if ($wall->getCeiling() === $center->y) {
                continue;
            }
            if (Collision::planeWithPlane($wall->getPoint2DStart(), $wall->width, $wall->height, $candidatePlane, $width, $height)) {
                return $wall;
            }
        }

        return null;
    }

    public function bulletHit(Hittable $hit, Point $position, bool $wasHeadshot): void
    {
        $soundEvent = new SoundEvent($position, $wasHeadshot ? SoundType::BULLET_HIT_HEADSHOT : SoundType::BULLET_HIT);
        $soundEvent->setPlayer($hit->getPlayer());
        if ($hit instanceof SolidSurface) {
            $soundEvent->setSurface($hit);
        }

        $this->makeSound($soundEvent);
    }

    public function tryPlantBomb(Player $player): void
    {
        if (!$this->canPlant($player)) {
            return;
        }

        /** @var Bomb $bomb */
        $bomb = $player->getEquippedItem();
        if ($this->lastBombPlantTick + Util::millisecondsToFrames(200) < $this->getTickId()) {
            $bomb->reset();
            $player->stop();
            $player->crouch();
            $sound = new SoundEvent($player->getPositionImmutable()->addY(10), SoundType::BOMB_PLANTING);
            $this->makeSound($sound->setPlayer($player)->setItem($bomb));
        }
        $this->lastBombPlantTick = $this->getTickId();
        $this->lastBombPlantPlayer = $player->getId();

        $planted = $bomb->plant();
        if ($planted) {
            $player->equip($player->getInventory()->removeBomb());
            $bomb->setPosition($player->getPositionImmutable());
            $this->game->bombPlanted($player);
            $player->stand();
        }
    }

    public function isPlantingOrDefusing(Player $player): bool
    {
        if (
            $this->lastBombPlantPlayer === $player->getId() &&
            ($this->lastBombPlantTick === $this->getTickId() || $this->lastBombPlantTick + 1 === $this->getTickId())
        ) {
            return true;
        }

        return false;
    }

    public function isWallOrFloorCollision(Point $start, Point $candidate, int $radius): bool
    {
        if ($this->findFloor($candidate, $radius)) {
            return true;
        }

        if ($start->x <> $candidate->x) {
            $xGrowing = ($start->x < $candidate->x);
            $baseX = $candidate->clone()->addX($xGrowing ? $radius : -$radius);
            if ($this->checkXSideWallCollision($baseX, $radius, $radius)) {
                return true;
            }
        }
        if ($start->z <> $candidate->z) {
            $zGrowing = ($start->z < $candidate->z);
            $baseZ = $candidate->clone()->addZ($zGrowing ? $radius : -$radius);
            if ($this->checkZSideWallCollision($baseZ, $radius, $radius)) {
                return true;
            }
        }

        return false;
    }

    public function isCollisionWithOtherPlayers(int $playerIdSkip, Point $point, int $radius, int $height): ?Player
    {
        foreach ($this->playersColliders as $collider) {
            if ($collider->getPlayerId() === $playerIdSkip) {
                continue;
            }

            if ($collider->collide($point, $radius, $height)) {
                return $this->game->getPlayer($collider->getPlayerId());
            }
        }

        return null;
    }

    /**
     * @return array<int,array<string,mixed>>
     * @internal
     */
    public function getWalls(): array
    {
        $output = [];
        foreach ($this->walls as $_groupIndex => $wallGroup) {
            foreach ($wallGroup as $_baseCoordinate => $walls) {
                foreach ($walls as $wall) {
                    $output[] = $wall->toArray();
                }
            }
        }

        return $output;
    }

    /**
     * @return array<int,array<string,mixed>>
     * @internal
     */
    public function getFloors(): array
    {
        $output = [];
        foreach ($this->floors as $_yCoordinate => $floors) {
            foreach ($floors as $floor) {
                $output[] = $floor->toArray();
            }
        }
        return $output;
    }

    /**
     * @return DropItem[]
     * @internal
     */
    public function getDropItems(): array
    {
        return $this->dropItems;
    }

    public function getMap(): Map
    {
        if (null === $this->map) {
            throw new GameException("No map is loaded!");
        }

        return $this->map;
    }

}
