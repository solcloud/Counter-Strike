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
    private const WALL_X = 'zy';
    private const WALL_Z = 'xy';
    private const BOMB_RADIUS = 90;
    private const BOMB_DEFUSE_MAX_DISTANCE = 300;
    private const ITEM_PICK_MAX_DISTANCE = 370;

    private ?Map $map = null;
    /** @var PlayerCollider[] */
    private array $playersColliders = [];
    /** @var DropItem[] */
    private array $dropItems = [];
    /** @var array<string,array<int,Wall[]>> (x|z)BaseCoordinate:Wall[] */
    private array $walls = [];
    /** @var array<int,Floor[]> yCoordinate:Floor[] */
    private array $floors = [];
    /** @var array<int,int[]> */
    private array $spawnPositionTakes = [];
    /** @var array<int,Point[]> */
    private array $spawnCandidates;
    private Bomb $bomb;
    private int $lastBombActionTick = -1;
    private int $lastBombPlayerId = -1;
    private int $playerPotentialDistanceSquared;

    public function __construct(private Game $game)
    {
        $this->playerPotentialDistanceSquared = ($game->getBacktrack()->numberOfHistoryStates + 2) * pow(Setting::moveDistancePerTick(), 2)
            + pow(Setting::playerBoundingRadius(), 2); // fixme: more tests to know if it is reliable heuristic value
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
        $this->walls[$wall->getPlane()][$wall->getBase()][] = $wall;
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

        foreach (($this->walls[self::WALL_Z][$point->z] ?? []) as $wall) {
            if ($wall->intersect($point)) {
                return $wall;
            }
        }
        foreach (($this->walls[self::WALL_X][$point->x] ?? []) as $wall) {
            if ($wall->intersect($point)) {
                return $wall;
            }
        }

        return null;
    }

    public function findPlayersHeadFloor(Point $point, int $radius = 0): ?Floor
    {
        foreach ($this->game->getAlivePlayers() as $player) {
            $floor = $player->getHeadFloor();
            if ($floor->intersect($point, $radius)) {
                return $floor;
            }
        }

        return null;
    }

    public function findFloor(Point $point, int $radius = 0): ?Floor
    {
        if ($point->y < 0) {
            throw new GameException("Y value cannot be lower than zero");
        }

        $floors = $this->floors[$point->y] ?? [];
        if ($floors === []) {
            return null;
        }

        $px = $point->x;
        $py = $point->z;
        $targetRadiusSquared = $radius * $radius;
        $smallestRadiusSquared = $targetRadiusSquared;
        $targetFloor = null;
        foreach ($floors as $floor) {
            $distanceSquared = Collision::circleCenterToPlaneBoundaryDistanceSquared($px, $py, $floor);
            if ($distanceSquared === $targetRadiusSquared || $distanceSquared === 0) {
                return $floor;
            }
            if ($distanceSquared < $smallestRadiusSquared) {
                $smallestRadiusSquared = $distanceSquared;
                $targetFloor = $floor;
            }
        }

        return $targetFloor;
    }

    public function isOnFloor(Floor $floor, Point $position, int $radius): bool
    {
        return (
            $floor->getY() === $position->y
            && $floor->intersect($position, $radius)
        );
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

    public function addPlayer(Player $player): void
    {
        $this->playersColliders[$player->getId()] = new PlayerCollider($player);
    }

    public function tryPickDropItems(Player $player): void
    {
        foreach ($this->dropItems as $key => $dropItem) {
            if (!Collision::cylinderWithCylinder(
                $dropItem->getPosition(), $dropItem->getBoundingRadius(), $dropItem->getHeight(),
                $player->getReferenceToPosition(), $player->getBoundingRadius(), $player->getHeadHeight()
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
        // Bomb defusing
        if (!$player->isPlayingOnAttackerSide() && $this->game->isBombActive()
            && $this->canBeSeen($player, $this->bomb->getPosition(), self::BOMB_RADIUS, self::BOMB_DEFUSE_MAX_DISTANCE)
        ) {
            $bomb = $this->bomb;
            if ($this->lastBombActionTick + Util::millisecondsToFrames(50) < $this->getTickId()) {
                $bomb->reset();
                $player->stop();
                $sound = new SoundEvent($player->getPositionClone()->addY(10), SoundType::BOMB_DEFUSING);
                $this->makeSound($sound->setPlayer($player)->setItem($bomb));
            }
            $this->lastBombActionTick = $this->getTickId();
            $this->lastBombPlayerId = $player->getId();

            $defused = $this->bomb->defuse($player->hasDefuseKit());
            if ($defused) {
                $this->game->bombDefused($player);
                $this->lastBombActionTick = -1;
                $this->lastBombPlayerId = -1;
            }
            return;
        }

        // Drop item pickup
        foreach ($this->dropItems as $key => $dropItem) {
            if (!$this->canBeSeen($player, $dropItem->getPosition(), $dropItem->getBoundingRadius(), self::ITEM_PICK_MAX_DISTANCE)) {
                continue;
            }
            if ($player->getInventory()->pickup($dropItem->getItem())) {
                $sound = new SoundEvent($dropItem->getPosition(), SoundType::ITEM_PICKUP);
                $this->makeSound($sound->setPlayer($player)->setItem($dropItem->getItem()));
                unset($this->dropItems[$key]);
                return;
            }
        }
    }

    public function canBeSeen(Player $observer, Point $targetCenter, int $targetRadius, int $maximumDistance, bool $checkForOtherPlayersAlso = false): bool
    {
        $start = $observer->getPositionClone()->addY($observer->getSightHeight());
        if (Util::distanceSquared($start, $targetCenter) > $maximumDistance * $maximumDistance) {
            return false;
        }
        $angleVertical = $observer->getSight()->getRotationVertical();
        $angleHorizontal = $observer->getSight()->getRotationHorizontal();

        $prevPos = $start->clone();
        $candidate = $start->clone();
        for ($distance = $observer->getBoundingRadius(); $distance <= $maximumDistance; $distance++) {
            [$x, $y, $z] = Util::movementXYZ($angleHorizontal, $angleVertical, $distance);
            $candidate->set($start->x + $x, $start->y + $y, $start->z + $z);
            if ($candidate->equals($prevPos)) {
                continue;
            }
            $prevPos->setFrom($candidate);

            if (Collision::pointWithSphere($candidate, $targetCenter, $targetRadius)) {
                return true;
            }
            if ($this->findFloor($candidate)) {
                return false;
            }
            if ($this->isWallAt($candidate)) {
                return false;
            }
            if ($checkForOtherPlayersAlso && $this->isCollisionWithOtherPlayers($observer->getId(), $candidate, 0, 0)) {
                return false;
            }
        }

        return false;
    }

    public function optimizeBulletHitCheck(Bullet $bullet): void
    {
        $skipPlayerIds = $bullet->getPlayerSkipIds();
        if (count($this->playersColliders) === count($skipPlayerIds)) {
            return;
        }

        $test = new Point();
        $bo = $bullet->getOrigin();
        $bp = $bullet->getPosition();
        $headCrouch = Setting::playerHeadHeightCrouch();
        foreach ($this->game->getPlayers() as $playerId => $player) {
            if (isset($skipPlayerIds[$playerId])) {
                continue;
            }
            if (!$player->isAlive()) {
                $bullet->addPlayerIdSkip($playerId);
                continue;
            }

            $test->setFrom($player->getReferenceToPosition());
            $distanceSquared = Util::distanceSquared($test->addY($headCrouch), $bp);
            if ($distanceSquared < $this->playerPotentialDistanceSquared) { // to close to reliably decide
                continue;
            }
            if ($distanceSquared - $this->playerPotentialDistanceSquared > Util::distanceSquared($test, $bo)) {
                $bullet->addPlayerIdSkip($playerId); // distance is bigger than bullet origin so hit should not be possible
                continue;
            }
        }
    }

    /**
     * @return Hittable[]
     */
    public function calculateHits(Bullet $bullet): array
    {
        $hits = [];
        $bp = $bullet->getPosition();
        $skipPlayerIds = $bullet->getPlayerSkipIds();
        foreach ($this->playersColliders as $playerId => $playerCollider) {
            if (isset($skipPlayerIds[$playerId])) {
                continue;
            }

            $hitBox = $playerCollider->tryHitPlayer($bullet, $this->game->getBacktrack());
            if (!$hitBox) {
                continue;
            }

            $hits[] = $hitBox;
            $player = $hitBox->getPlayer();
            if ($player) {
                $bullet->addPlayerIdSkip($player->getId());
                if ($hitBox->playerWasKilled()) {
                    $this->game->playerAttackKilledEvent($player, $bullet, $hitBox->wasHeadShot());
                }
            }
        }

        $floor = $this->findFloor($bp);
        if ($floor) {
            $hits[] = $floor;
        }

        $wall = $this->isWallAt($bp);
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

        return Collision::pointWithBox($player->getReferenceToPosition(), $this->getMap()->getPlantArea());
    }

    public function canBuy(Player $player): bool
    {
        if (!$this->game->playersCanBuy()) {
            return false;
        }

        return Collision::pointWithBox($player->getReferenceToPosition(), $this->getMap()->getBuyArea($player->isPlayingOnAttackerSide()));
    }

    public function getTickId(): int
    {
        return $this->game->getTickId();
    }

    public function playerDiedToFallDamage(Player $playerDead): void
    {
        $this->game->playerFallDamageKilledEvent($playerDead);
    }

    public function checkXSideWallCollision(Point $bottomCenter, int $height, int $radius): ?Wall
    {
        if ($bottomCenter->x < 0) {
            return new Wall(new Point(-1, -1, -1), false);
        }

        $candidatePlane = $bottomCenter->to2D(self::WALL_X)->addX(-$radius);
        $width = 2 * $radius;
        foreach (($this->walls[self::WALL_X][$bottomCenter->x] ?? []) as $wall) {
            if (Collision::planeWithPlane($wall->getPoint2DStart(), $wall->width, $wall->height, $candidatePlane, $width, $height)) {
                return $wall;
            }
        }

        return null;
    }

    public function checkZSideWallCollision(Point $bottomCenter, int $height, int $radius): ?Wall
    {
        if ($bottomCenter->z < 0) {
            return new Wall(new Point(-1, -1, -1), true);
        }

        $candidatePlane = $bottomCenter->to2D(self::WALL_Z)->addX(-$radius);
        $width = 2 * $radius;
        foreach (($this->walls[self::WALL_Z][$bottomCenter->z] ?? []) as $wall) {
            if (Collision::planeWithPlane($wall->getPoint2DStart(), $wall->width, $wall->height, $candidatePlane, $width, $height)) {
                return $wall;
            }
        }

        return null;
    }

    public function bulletHit(Hittable $hit, Bullet $bullet, bool $wasHeadshot): void
    {
        $soundEvent = new SoundEvent($bullet->getPosition()->clone(), $wasHeadshot ? SoundType::BULLET_HIT_HEADSHOT : SoundType::BULLET_HIT);
        $soundEvent->setPlayer($hit->getPlayer());
        $item = $bullet->getShootItem();
        if ($item instanceof Item) {
            $soundEvent->setItem($item);
        }
        if ($hit instanceof SolidSurface) {
            $soundEvent->setSurface($hit);
        }
        $soundEvent->addExtra('origin', $bullet->getOrigin()->toArray());

        $this->makeSound($soundEvent);
        $this->game->getScore()->getPlayerStat($bullet->getOriginPlayerId())->addDamage($hit->getDamage());
    }

    public function tryPlantBomb(Player $player): void
    {
        if (!$this->canPlant($player)) {
            return;
        }

        /** @var Bomb $bomb */
        $bomb = $player->getEquippedItem();
        if ($this->lastBombActionTick + Util::millisecondsToFrames(200) < $this->getTickId()) {
            $bomb->reset();
            $player->stop();
            $sound = new SoundEvent($player->getPositionClone()->addY(10), SoundType::BOMB_PLANTING);
            $this->makeSound($sound->setPlayer($player)->setItem($bomb));
        }
        $this->lastBombActionTick = $this->getTickId();
        $this->lastBombPlayerId = $player->getId();

        $planted = $bomb->plant();
        if ($planted) {
            $player->equip($player->getInventory()->removeBomb());
            $bomb->setPosition($player->getPositionClone());
            $this->game->bombPlanted($player);

            $this->bomb = $bomb;
            $this->lastBombActionTick = -1;
            $this->lastBombPlayerId = -1;
        }
    }

    public function isPlantingOrDefusing(Player $player): bool
    {
        return (
            $this->lastBombPlayerId === $player->getId() &&
            ($this->lastBombActionTick === $this->getTickId() || $this->lastBombActionTick + 1 === $this->getTickId())
        );
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
            if ($collider->playerId === $playerIdSkip) {
                continue;
            }

            if ($collider->collide($point, $radius, $height)) {
                return $this->game->getPlayer($collider->playerId);
            }
        }

        return null;
    }

    /**
     * @return Wall[]
     */
    public function getXWalls(int $x): array
    {
        return ($this->walls[self::WALL_X][$x] ?? []);
    }

    /**
     * @return Wall[]
     */
    public function getZWalls(int $z): array
    {
        return ($this->walls[self::WALL_Z][$z] ?? []);
    }

    /**
     * @return array<int,array<string,mixed>>
     * @internal
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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

    public function getBacktrack(): Backtrack
    {
        return $this->game->getBacktrack();
    }

}
