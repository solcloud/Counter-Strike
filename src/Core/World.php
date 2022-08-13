<?php

namespace cs\Core;

use cs\Interface\Hittable;
use cs\Map\Map;

class World
{
    private const ATTACKER = 0;
    private const DEFENDER = 1;
    private const WALL_X = 0;
    private const WALL_Z = 1;

    private ?Map $map;
    /** @var PlayerCollider[] */
    private array $playersColliders = [];
    /** @var array<int,array<int,Wall[]>> xCoordinate:Wall[] */
    private array $walls = [];
    /** @var array<int,Floor[]> yCoordinate:Floor[] */
    private array $floors = [];
    /** @var array<int,int[]> */
    private array $spawnPositionTakes = [];
    /** @var array<int,Point[]> */
    private array $spawnCandidates;

    public function __construct(private Game $game)
    {
    }

    public function roundReset(): void
    {
        $this->spawnCandidates = [];
        $this->spawnPositionTakes = [];
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

        foreach (($this->walls[self::WALL_Z][$point->getZ()] ?? []) as $wall) {
            if ($wall->intersect($point)) {
                return $wall;
            }
        }
        foreach (($this->walls[self::WALL_X][$point->getX()] ?? []) as $wall) {
            if ($wall->intersect($point)) {
                return $wall;
            }
        }

        return null;
    }

    public function findFloor(Point $point): ?Floor
    {
        if ($point->y < 0) {
            throw new GameException("Y value cannot be lower than zero");
        }

        foreach (($this->floors[$point->getY()] ?? []) as $floor) {
            if ($floor->intersect($point)) {
                return $floor;
            }
        }

        return null;
    }

    public function isOnFloor(Floor $floor, Point $position): bool
    {
        if ($floor->getY() !== $position->getY()) {
            return false;
        }

        if ($floor->getStart()->x > $position->x || $floor->getEnd()->x < $position->x) {
            return false;
        }

        if ($floor->getStart()->z > $position->z || $floor->getEnd()->z < $position->z) {
            return false;
        }

        return true;
    }

    public function getPlayerSpawnPosition(bool $isAttacker, bool $randomizeSpawnPosition): Point
    {
        if (null === $this->map) {
            throw new GameException("No map is loaded! Cannot spawn players.");
        }

        $key = ($isAttacker ? self::ATTACKER : self::DEFENDER);
        if (isset($this->spawnCandidates[$key])) {
            $source = $this->spawnCandidates[$key];
        } else {
            $source = ($isAttacker ? $this->map->getSpawnPositionAttacker() : $this->map->getSpawnPositionDefender());
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

    /**
     * @return Hittable[]
     */
    public function calculateHits(Bullet $bullet): array
    {
        $hits = [];

        foreach ($this->playersColliders as $playerCollider) {
            if ($playerCollider->getPlayerId() === $bullet->getOriginPlayerId()) {
                continue; // cannot shoot self
            }

            $hitBox = $playerCollider->tryHitPlayer($bullet);
            if ($hitBox) {
                $player = $hitBox->getPlayer();
                if ($hitBox->playerWasKilled() && $player) {
                    $this->game->playerAttackKilledEvent($player, $bullet, $hitBox->wasHeadShot());
                }
                $hits[] = $hitBox;
            }
        }

        $floor = $this->findFloor($bullet->getPosition());
        if ($floor) {
            $hits[] = $floor;
            return $hits; // no floor is penetrable
        }

        $wall = $this->isWallAt($bullet->getPosition());
        if ($wall) {
            $hits[] = $wall;
        }

        return $hits;
    }

    public function getTickId(): int
    {
        return $this->game->getTickId();
    }

    public function playerDiedToFallDamage(Player $playerDead): void
    {
        $this->game->playerFallDamageKilledEvent($playerDead);
    }

    public function checkXSideWallCollision(Point $base, int $zMin, int $zMax): ?Wall
    {
        foreach (($this->walls[self::WALL_X][$base->x] ?? []) as $wall) {
            if ($wall->getStart()->z > $zMax || $wall->getEnd()->z < $zMin) {
                continue;
            }
            if($wall->getStart()->y > $base->y || $wall->getEnd()->y < $base->y) {
                continue;
            }
            return $wall;
        }

        return null;
    }

    public function checkZSideWallCollision(Point $base, mixed $xMin, mixed $xMax): ?Wall
    {
        foreach (($this->walls[self::WALL_Z][$base->z] ?? []) as $wall) {
            if ($wall->getStart()->x > $xMax || $wall->getEnd()->x < $xMin) {
                continue;
            }
            if($wall->getStart()->y > $base->y || $wall->getEnd()->y < $base->y) {
                continue;
            }
            return $wall;
        }

        return null;
    }


}
