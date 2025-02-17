<?php

namespace cs\Core;

use cs\Enum\ArmorType;
use cs\Enum\InventorySlot;
use cs\Enum\SoundType;
use cs\Equipment\Bomb;
use cs\Equipment\Grenade;
use cs\Equipment\HighExplosive;
use cs\Equipment\Smoke;
use cs\Event\DropEvent;
use cs\Event\GrillEvent;
use cs\Event\SmokeEvent;
use cs\Event\SoundEvent;
use cs\Event\ThrowEvent;
use cs\Interface\Flammable;
use cs\Interface\Hittable;
use cs\Map\Map;

final class World
{
    private const string WALL_X = 'zy';
    private const string WALL_Z = 'xy';
    private const int BOMB_RADIUS = 90;
    private const int BOMB_DEFUSE_MAX_DISTANCE = 300;
    private const int ITEM_PICK_MAX_DISTANCE = 370;
    public const int GRENADE_NAVIGATION_MESH_TILE_SIZE = 31;
    public const int GRENADE_NAVIGATION_MESH_OBJECT_HEIGHT = 80;

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
    /** @var array<string,SmokeEvent> */
    private array $activeSmokes = [];
    /** @var array<string,GrillEvent> */
    private array $activeMolotovs = [];
    private Bomb $bomb;
    private int $lastBombActionTick = -1;
    private int $lastBombPlayerId = -1;
    private int $bombActionTickBuffer = 1;
    private ?PathFinder $grenadeNavMesh = null;

    public function __construct(private Game $game)
    {
    }

    public function roundReset(): void
    {
        $this->activeSmokes = [];
        $this->activeMolotovs = [];
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

    public function regenerateNavigationMeshes(): void
    {
        $this->grenadeNavMesh = $this->buildNavigationMesh(self::GRENADE_NAVIGATION_MESH_TILE_SIZE, self::GRENADE_NAVIGATION_MESH_OBJECT_HEIGHT);
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

    public function findFloorSquare(Point $point, int $radius): ?Floor
    {
        if ($point->y < 0) {
            throw new GameException("Y value cannot be lower than zero"); // @codeCoverageIgnore
        }
        $floors = $this->floors[$point->y] ?? [];
        if ($floors === []) {
            return null;
        }

        $distance = 2 * $radius;
        $candidateX = $point->x - $radius;
        $candidateZ = $point->z - $radius;
        foreach ($floors as $floor) {
            if (Collision::planeWithPlane($floor->getPoint2DStart(), $floor->width, $floor->depth, $candidateX, $candidateZ, $distance, $distance)) {
                return $floor;
            }
        }
        return null;
    }

    public function findFloor(Point $point, int $radius = 0): ?Floor
    {
        if ($point->y < 0) {
            throw new GameException("Y value cannot be lower than zero"); // @codeCoverageIgnore
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

    public function findHighestWall(Point $bottomCenter, int $height, int $radius, int $maxWallCeiling, bool $xWall): int
    {
        $base = $xWall ? $bottomCenter->x : $bottomCenter->z;
        if ($base < 0) {
            return $maxWallCeiling + 1;
        }
        $walls = $xWall ? $this->getXWalls($base) : $this->getZWalls($base);
        if ($walls === []) {
            return 0;
        }

        $width = 2 * $radius;
        $highestWallCeiling = 0;
        $candidatePlaneA = $xWall ? $bottomCenter->z - $radius : $bottomCenter->x - $radius;
        foreach ($walls as $wall) {
            $wallCeiling = $wall->getCeiling();
            if ($wallCeiling <= $bottomCenter->y) {
                continue;
            }
            if (!Collision::planeWithPlane($wall->getPoint2DStart(), $wall->width, $wall->height, $candidatePlaneA, $bottomCenter->y, $width, $height)) {
                continue;
            }
            if ($wallCeiling > $maxWallCeiling) {
                return $wallCeiling;
            }
            if ($wallCeiling > $highestWallCeiling) {
                $highestWallCeiling = $wallCeiling;
            }
        }

        return $highestWallCeiling;
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
        $playerPosition = $player->getReferenceToPosition();
        $boundingRadius = $player->getBoundingRadius();
        $headHeight = $player->getHeadHeight();

        foreach ($this->dropItems as $key => $dropItem) {
            if (!Collision::cylinderWithCylinder(
                $dropItem->getPosition(), $dropItem->getBoundingRadius(), $dropItem->getHeight(),
                $playerPosition, $boundingRadius, $headHeight
            )) {
                continue;
            }

            if ($player->getInventory()->pickup($dropItem->getItem())) {
                $soundEvent = new SoundEvent($dropItem->getPosition(), SoundType::ITEM_PICKUP);
                $this->makeSound($soundEvent->setPlayer($player)->setItem($dropItem->getItem())->addExtra('id', $dropItem->getId()));
                unset($this->dropItems[$key]);
            }
        }
    }

    public function dropItem(Player $player, Item $item): void
    {
        $dropEvent = new DropEvent($player, $item, $this);
        $dropEvent->onFloorLand(function (DropItem $dropItem): void {
            $this->dropItems[] = $dropItem;
        });
        $this->game->addDropEvent($dropEvent);
    }

    public function playerUse(Player $player): void
    {
        // Bomb defusing
        if (!$player->isPlayingOnAttackerSide() && $this->game->isBombActive()
            && $this->canBeSeen($player, $this->bomb->getPosition(), self::BOMB_RADIUS, self::BOMB_DEFUSE_MAX_DISTANCE)
        ) {
            $bomb = $this->bomb;
            $tickId = $this->getTickId();
            $playerId = $player->getId();
            if ($playerId !== $this->lastBombPlayerId || $this->lastBombActionTick + $this->bombActionTickBuffer < $tickId) {
                $player->stop();
                $bomb->startDefusing($tickId, $player->hasDefuseKit());
                $soundEvent = new SoundEvent($player->getPositionClone()->addY(10), SoundType::BOMB_DEFUSING);
                $this->makeSound($soundEvent->setPlayer($player)->setItem($bomb));
            }
            $this->lastBombActionTick = $tickId;
            $this->lastBombPlayerId = $playerId;

            if ($bomb->isDefused($tickId)) {
                $this->lastBombActionTick = -1;
                $this->lastBombPlayerId = -1;
                $this->game->bombDefused($player);
            }
            return;
        }

        // Dropped item pickup
        foreach ($this->dropItems as $key => $dropItem) {
            if (!$this->canBeSeen($player, $dropItem->getPosition(), $dropItem->getBoundingRadius(), self::ITEM_PICK_MAX_DISTANCE)) {
                continue;
            }

            $shouldEquipOnPickup = false;
            $item = $dropItem->getItem();
            $slot = $item->getSlot();
            $slotId = $slot->value;
            if ($player->getInventory()->has($slotId) && in_array($slotId, [InventorySlot::SLOT_PRIMARY->value, InventorySlot::SLOT_SECONDARY->value], true)) {
                $shouldEquipOnPickup = ($player->getEquippedItem()->getSlot() === $slot);
                $player->dropItemFromSlot($slotId);
            }
            if ($player->getInventory()->pickup($item)) {
                $soundEvent = new SoundEvent($dropItem->getPosition(), SoundType::ITEM_PICKUP);
                $this->makeSound($soundEvent->setPlayer($player)->setItem($item)->addExtra('id', $dropItem->getId()));
                unset($this->dropItems[$key]);
                if ($shouldEquipOnPickup) {
                    $player->equip($slot);
                }
                return;
            }
        }
    }

    public function canBeSeen(Player $observer, Point $targetCenter, int $targetRadius, int $maximumDistance, bool $checkForOtherPlayersAlso = false): bool
    {
        $start = $observer->getSightPositionClone();
        if (Util::distanceSquared($start, $targetCenter) > $maximumDistance * $maximumDistance) {
            return false;
        }

        return $this->pointCanSeePoint(
            $start, $targetCenter, $observer->getSight()->getRotationHorizontal(), $observer->getSight()->getRotationVertical(),
            $maximumDistance, $checkForOtherPlayersAlso ? $observer->getId() : null, $targetRadius, $observer->getBoundingRadius(),
        );
    }

    private function pointCanSeePoint(
        Point $observer, Point $targetCenter, float $angleHorizontal, float $angleVertical,
        int   $maximumDistance, int|null $playerIdSkip = -1, int $targetRadius = 1, int $startDistance = 0,
    ): bool
    {
        $prevPos = $observer->clone();
        $candidate = $observer->clone();
        for ($distance = $startDistance; $distance <= $maximumDistance; $distance++) {
            [$x, $y, $z] = Util::movementXYZ($angleHorizontal, $angleVertical, $distance);
            $candidate->set($observer->x + $x, $observer->y + $y, $observer->z + $z);
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
            if ($playerIdSkip !== null && $this->isCollisionWithOtherPlayers($playerIdSkip, $candidate, 0, 0)) {
                return false;
            }
        }

        return false;
    }

    public function optimizeBulletHitCheck(Bullet $bullet, Point $maxDestination): void
    {
        $boxMin = new Point();
        $boxMax = new Point();
        foreach ($this->game->getPlayers() as $playerId => $player) {
            if (!$player->isAlive()) {
                $bullet->addPlayerIdSkip($playerId);
                continue;
            }

            $playerRadius = $player->getBoundingRadius();
            $boxMin->setFrom($player->getReferenceToPosition());
            $boxMax->setFrom($boxMin);
            foreach ($this->getBacktrack()->getAllPlayerPositions($playerId) as $xyz) {
                $boxMin->set(
                    min($boxMin->x, $xyz[0]), min($boxMin->y, $xyz[1]), min($boxMin->z, $xyz[2])
                );
                $boxMax->set(
                    max($boxMax->x, $xyz[0]), max($boxMax->y, $xyz[1]), max($boxMax->z, $xyz[2])
                );
            }
            $boxMin->addPart(-$playerRadius, 0, -$playerRadius);
            $boxMax->addPart($playerRadius, Setting::playerHeadHeightStand(), $playerRadius);

            if (!Collision::boxWithSegment($boxMin, $boxMax, $bullet->getOrigin(), $maxDestination)) {
                $bullet->addPlayerIdSkip($playerId);
            }
        }
    }

    /**
     * @return Hittable[]
     */
    public function calculateHits(Bullet $bullet, Point $bulletPosition): array
    {
        $hits = [];
        $skipPlayerIds = $bullet->getPlayerSkipIds();
        foreach ($this->playersColliders as $playerId => $playerCollider) {
            if ($skipPlayerIds[$playerId] ?? false) {
                continue;
            }

            $hitBox = $playerCollider->tryHitPlayer($bullet, $bulletPosition, $this->game->getBacktrack());
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

        $floor = $this->findFloor($bulletPosition);
        if ($floor) {
            $hits[] = $floor;
        }

        $wall = $this->isWallAt($bulletPosition);
        if ($wall) {
            $hits[] = $wall;
        }

        return $hits;
    }

    public function makeSound(SoundEvent $soundEvent): void
    {
        $this->game->addSoundEvent($soundEvent);
    }

    public function throw(ThrowEvent $event): void
    {
        $event->onComplete[] = function (ThrowEvent $event): void {
            if ($event->item instanceof HighExplosive) {
                $this->processHighExplosiveBlast($event->getPlayer(), $event->getPositionClone(), $event->item);
            }
            if ($event->item instanceof Flammable) {
                $this->processFlammableExplosion($event->getPlayer(), $event->getPositionClone(), $event->item);
            }
            if ($event->item instanceof Smoke) {
                $this->processSmokeExpansion($event->getPlayer(), $event->getPositionClone(), $event->item);
            }
        };
        $this->game->addThrowEvent($event);
    }

    private function processSmokeExpansion(Player $initiator, Point $epicentre, Smoke $item): void
    {
        if ($this->grenadeNavMesh === null) {
            $this->regenerateNavigationMeshes();
        }
        assert($this->grenadeNavMesh !== null);

        $epicentreFloor = $epicentre->clone()->addY(-$item->getBoundingRadius());
        $floorNavmeshPoint = $this->grenadeNavMesh->findTile($epicentreFloor, $item->getBoundingRadius());

        $event = new SmokeEvent(
            $initiator, $item, $this, $this->grenadeNavMesh->tileSizeHalf,
            $this->grenadeNavMesh->colliderHeight, $this->grenadeNavMesh->getGraph(), $floorNavmeshPoint,
        );
        $event->onComplete[] = function (SmokeEvent $event): void {
            unset($this->activeSmokes[$event->id]);
        };
        $this->activeSmokes[$event->id] = $event;
        $this->game->addSmokeEvent($event);
    }

    private function processFlammableExplosion(Player $thrower, Point $epicentre, Flammable $item): void
    {
        if ($this->grenadeNavMesh === null) {
            $this->regenerateNavigationMeshes();
        }
        assert($this->grenadeNavMesh !== null);

        $epicentreFloor = $epicentre->clone()->addY(-$item->getBoundingRadius());
        $floorNavmeshPoint = $this->grenadeNavMesh->findTile($epicentreFloor, $item->getBoundingRadius());

        $event = new GrillEvent(
            $thrower, $item, $this, $this->grenadeNavMesh->tileSizeHalf,
            $this->grenadeNavMesh->colliderHeight, $this->grenadeNavMesh->getGraph(), $floorNavmeshPoint,
        );
        $event->onComplete[] = function (GrillEvent $event): void {
            unset($this->activeMolotovs[$event->id]);
        };
        $this->activeMolotovs[$event->id] = $event;
        $this->game->addGrillEvent($event);
    }

    public function smokeTryToExtinguishFlames(Column $smoke): void
    {
        foreach ($this->activeMolotovs as $fire) {
            if (!Collision::boxWithBox($smoke->boundaryMin, $smoke->boundaryMax, $fire->boundaryMin, $fire->boundaryMax)) {
                continue;
            }

            foreach ($fire->parts as $flame) {
                if ($flame->active && Collision::boxWithBox($smoke->boundaryMin, $smoke->boundaryMax, $flame->boundaryMin, $flame->boundaryMax)) {
                    $fire->extinguish($flame);
                }
            }
        }
    }

    public function flameCanIgnite(Column $flame): bool
    {
        foreach ($this->activeSmokes as $smoke) {
            if (!Collision::boxWithBox($smoke->boundaryMin, $smoke->boundaryMax, $flame->boundaryMin, $flame->boundaryMax)) {
                continue;
            }

            foreach ($smoke->parts as $smokePart) {
                if (Collision::boxWithBox($smokePart->boundaryMin, $smokePart->boundaryMax, $flame->boundaryMin, $flame->boundaryMax)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function checkFlameDamage(GrillEvent $fire, int $tickId): void
    {
        foreach ($this->playersColliders as $playerId => $collider) {
            $player = $collider->getPlayer();
            if (!$player->isAlive() || !$fire->canHitPlayer($playerId, $tickId)) {
                continue;
            }

            $pp = $player->getReferenceToPosition();
            $playerHeight = $player->getHeadHeight();
            $playerRadius = $player->getBoundingRadius();
            if (
                $pp->y > $fire->boundaryMax->y
                || $pp->y + $playerHeight < $fire->boundaryMin->y
                || !Collision::circleWithRect(
                    $pp->x, $pp->z, $playerRadius,
                    $fire->boundaryMin->x, $fire->boundaryMax->x,
                    $fire->boundaryMin->z, $fire->boundaryMax->z,
                )
            ) {
                continue;
            }

            foreach ($fire->parts as $flame) {
                if (!$flame->active || !Collision::pointWithCylinder($flame->highestPoint, $pp, $playerRadius, $playerHeight)) {
                    continue;
                }

                $fire->playerHit($playerId, $tickId);
                $flammableItem = $fire->getItem();
                assert($flammableItem instanceof Flammable);
                $damage = $flammableItem->calculateDamage($player->getArmorType() !== ArmorType::NONE);
                assert($fire->item instanceof Item);
                $this->playerHit(
                    $player->getCentrePointClone(), $player, $fire->initiator, SoundType::FLAME_PLAYER_HIT,
                    $fire->item, $flame->center, $damage
                );
                $player->lowerHealth($damage);
                if (!$player->isAlive()) {
                    $this->playerDiedToFlame($fire->initiator, $player, $flammableItem);
                }

                break;
            }
        }
    }

    public function isCollisionWithMolotov(Point $pos): bool
    {
        foreach ($this->activeMolotovs as $molotov) {
            if (!Collision::pointWithBoxBoundary($pos, $molotov->boundaryMin, $molotov->boundaryMax)) {
                continue;
            }

            foreach ($molotov->parts as $flame) {
                if ($flame->active && Collision::pointWithBoxBoundary($pos, $flame->boundaryMin, $flame->boundaryMax)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function processHighExplosiveBlast(Player $thrower, Point $epicentre, HighExplosive $item): void
    {
        $maxBlastDistance = $item->getMaxBlastRadius();
        $maxBlastDistanceSquared = $maxBlastDistance * $maxBlastDistance;
        foreach ($this->playersColliders as $playerId => $playerCollider) {
            $player = $this->game->getPlayer($playerId);
            if (!$player->isAlive()) {
                continue; // @codeCoverageIgnore
            }
            if (Util::distanceSquared($epicentre, $player->getCentrePointClone()) > $maxBlastDistanceSquared) {
                continue;
            }

            $damage = 0;
            foreach ($player->getPlayerGrenadeHitPoints() as $point) {
                $distanceSquared = Util::distanceSquared($epicentre, $point);
                if ($distanceSquared > $maxBlastDistanceSquared) {
                    continue; // @codeCoverageIgnore
                }
                [$angleHorizontal, $angleVertical] = Util::worldAngle($point, $epicentre);
                if (!$this->pointCanSeePoint($epicentre, $point, $angleHorizontal ?? 0, $angleVertical, $maxBlastDistance, null)) {
                    continue;
                }

                $damage += $item->calculateDamage($distanceSquared, $player->getArmorType() !== ArmorType::NONE);
            }

            $player->lowerHealth($damage);
            if (!$player->isAlive()) {
                $this->game->playerGrenadeKilledEvent($thrower, $player, $item);
            }
        }
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

    public function isPaused(): bool
    {
        return $this->game->isPaused();
    }

    public function playerDiedToFallDamage(Player $playerDead): void
    {
        $this->game->playerFallDamageKilledEvent($playerDead);
    }

    private function playerDiedToFlame(Player $playerCulprit, Player $playerDead, Flammable $item): void
    {
        assert($item instanceof Grenade, "New flammable non grenade type?");
        $this->game->playerGrenadeKilledEvent($playerCulprit, $playerDead, $item);
    }

    public function buildNavigationMesh(int $tileSize, int $objectHeight): PathFinder
    {
        $boundingRadius = Setting::playerBoundingRadius();
        if ($tileSize > $boundingRadius - 4) {
            throw new GameException('Tile size should be decently lower than player bounding radius.'); // @codeCoverageIgnore
        }

        $pathFinder = new PathFinder($this, $tileSize, $objectHeight);
        $startPoints = $this->getMap()->getStartingPointsForNavigationMesh();
        if ([] === $startPoints) {
            throw new GameException('No starting point for navigation defined!'); // @codeCoverageIgnore
        }
        foreach ($startPoints as $point) {
            $pathFinder->buildNavigationMesh($point, $objectHeight);
        }

        return $pathFinder->saveAndClear();
    }

    public function checkXSideWallCollision(Point $bottomCenter, int $height, int $radius): ?Wall
    {
        $startZ = $bottomCenter->z - $radius;
        $width = 2 * $radius;
        foreach (($this->walls[self::WALL_X][$bottomCenter->x] ?? []) as $wall) {
            if (Collision::planeWithPlane($wall->getPoint2DStart(), $wall->width, $wall->height, $startZ, $bottomCenter->y, $width, $height)) {
                return $wall;
            }
        }

        return null;
    }

    public function checkZSideWallCollision(Point $bottomCenter, int $height, int $radius): ?Wall
    {
        $startX = $bottomCenter->x - $radius;
        $width = 2 * $radius;
        foreach (($this->walls[self::WALL_Z][$bottomCenter->z] ?? []) as $wall) {
            if (Collision::planeWithPlane($wall->getPoint2DStart(), $wall->width, $wall->height, $startX, $bottomCenter->y, $width, $height)) {
                return $wall;
            }
        }

        return null;
    }

    public function bulletHit(Hittable $hit, Bullet $bullet, bool $wasHeadshot): void
    {
        $item = $bullet->getShootItem();
        assert($item instanceof Item);

        if ($hit->getPlayer()) {
            $this->playerHit(
                $bullet->getPosition()->clone(),
                $hit->getPlayer(),
                $this->game->getPlayer($bullet->getOriginPlayerId()),
                $wasHeadshot ? SoundType::BULLET_HIT_HEADSHOT : SoundType::BULLET_HIT,
                $item,
                $bullet->getOrigin(),
                $hit->getDamage(),
            );
        }

        if ($hit instanceof SolidSurface) {
            $this->surfaceHit(
                $bullet->getPosition()->clone(),
                $hit,
                $bullet->getOriginPlayerId(),
                $bullet->getOrigin(),
                $item,
                $hit->getDamage()
            );
        }
    }

    private function surfaceHit(Point $hitPoint, SolidSurface $hit, int $attackerId, Point $origin, Item $item, int $damage): void
    {
        $soundEvent = new SoundEvent($hitPoint, SoundType::BULLET_HIT);
        $soundEvent->setItem($item);
        $soundEvent->setSurface($hit);
        $soundEvent->addExtra('origin', $origin->toArray());
        $soundEvent->addExtra('damage', min(100, $damage));
        $soundEvent->addExtra('shooter', $attackerId);

        $this->makeSound($soundEvent);
    }

    private function playerHit(Point $hitPoint, Player $playerHit, Player $playerCulprit, SoundType $soundType, Item $item, Point $origin, int $damage): void
    {
        $attackerId = $playerCulprit->getId();
        $soundEvent = new SoundEvent($hitPoint, $soundType);
        $soundEvent->setPlayer($playerHit);
        $soundEvent->setItem($item);
        $soundEvent->addExtra('origin', $origin->toArray());
        $soundEvent->addExtra('damage', min(100, $damage));
        $soundEvent->addExtra('shooter', $attackerId);

        $this->makeSound($soundEvent);
        if ($playerHit->isPlayingOnAttackerSide() !== $playerCulprit->isPlayingOnAttackerSide()) {
            $this->game->getScore()->getPlayerStat($attackerId)->addDamage($damage);
        }
    }

    public function tryPlantBomb(Player $player, Bomb $bomb): void
    {
        if (!$this->canPlant($player)) {
            return;
        }

        $tickId = $this->getTickId();
        $playerId = $player->getId();
        if ($playerId !== $this->lastBombPlayerId || $this->lastBombActionTick + $this->bombActionTickBuffer < $tickId) {
            $player->stop();
            $bomb->startPlanting($tickId);
            $soundEvent = new SoundEvent($player->getPositionClone()->addY(10), SoundType::BOMB_PLANTING);
            $this->makeSound($soundEvent->setPlayer($player)->setItem($bomb));
        }
        $this->lastBombActionTick = $this->getTickId();
        $this->lastBombPlayerId = $playerId;

        if ($bomb->isPlanted($tickId)) {
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
        return ($this->lastBombPlayerId === $player->getId() && $this->bombActionTickBuffer >= $this->getTickId() - $this->lastBombActionTick);
    }

    public function isWallOrFloorCollision(Point $start, Point $candidate, int $radius): bool
    {
        if ($this->findFloor($candidate, $radius)) {
            return true;
        }

        if ($start->x !== $candidate->x) {
            $xGrowing = ($start->x < $candidate->x);
            $baseX = $candidate->clone()->addX($xGrowing ? $radius : -$radius);
            if ($this->checkXSideWallCollision($baseX, $radius, $radius)) {
                return true;
            }
        }
        if ($start->z !== $candidate->z) {
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

            if ($collider->isBoundaryCollision($point, $radius, $height)) {
                return $collider->getPlayer();
            }
        }

        return null;
    }

    /**
     * @return Wall[]
     */
    private function getXWalls(int $x): array
    {
        return ($this->walls[self::WALL_X][$x] ?? []);
    }

    /**
     * @return Wall[]
     */
    private function getZWalls(int $z): array
    {
        return ($this->walls[self::WALL_Z][$z] ?? []);
    }

    /**
     * @return array<int,array<string,mixed>>
     * @internal
     * @codeCoverageIgnore
     * @infection-ignore-all
     */
    public function getWalls(): array
    {
        $output = [];
        foreach ($this->walls as $wallGroup) {
            foreach ($wallGroup as $walls) {
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
     * @infection-ignore-all
     */
    public function getFloors(): array
    {
        $output = [];
        foreach ($this->floors as $floors) {
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
            throw new GameException("No map is loaded!"); // @codeCoverageIgnore
        }

        return $this->map;
    }

    public function getBacktrack(): Backtrack
    {
        return $this->game->getBacktrack();
    }

    public function activeMolotovExists(): bool
    {
        return ([] !== $this->activeMolotovs);
    }

}
