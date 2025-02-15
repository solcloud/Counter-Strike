<?php

namespace Test\Unit;

use cs\Core\Box;
use cs\Core\GameFactory;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Setting;
use cs\Core\Util;
use cs\Core\Wall;
use cs\Enum\BuyMenuItem;
use cs\Enum\Color;
use cs\Equipment\Molotov;
use cs\Event\AttackResult;
use cs\Interface\Flammable;
use cs\Map\BoxMap;
use cs\Map\Map;
use cs\Map\TestMap;
use cs\Weapon\AmmoBasedWeapon;
use cs\Weapon\PistolGlock;
use SebastianBergmann\Timer\Timer;
use Test\BaseTest;

/** @coversNothing */
class PerformanceTest extends BaseTest
{
    private static float $timeScale;

    public static function setUpBeforeClass(): void
    {
        if (getenv('XDEBUG_MODE') === 'coverage' || getenv('INFECTION') === '1') {
            self::markTestSkipped();
        }

        gc_collect_cycles();
        parent::setUpBeforeClass();

        self::$timeScale = 1;
        $sum = 0;
        $timer = new Timer();
        $timer->start();
        for ($i = 0; $i < 1231234; $i++) {
            $sum -= $sum;
        }
        $took = $timer->stop();
        $tookMs = $took->asMilliseconds();
        if ($tookMs < 6.0) {
            self::$timeScale = 1.0;
        } elseif ($tookMs < 6.4) {
            self::$timeScale = 1.5;
        } elseif ($tookMs < 6.9) {
            self::$timeScale = 3.0;
        } elseif ($tookMs < 10.0) {
            self::$timeScale = 6.0;
        } else {
            self::markTestSkipped();
        }
        if (getenv('CI') !== false) {var_dump($tookMs, self::$timeScale);} // todo remove

        Util::$TICK_RATE = 20;
        Setting::loadConstants([
            'moveOneMs'                     => 0.7,
            'moveWalkOneMs'                 => 0.6,
            'moveCrouchOneMs'               => 0.4,
            'fallAmountOneMs'               => 1,
            'crouchDurationMs'              => 250,
            'jumpDurationMs'                => 420,
            'jumpMovementSpeedMultiplier'   => 1.0,
            'flyingMovementSpeedMultiplier' => 0.8,
            'playerHeadRadius'              => 30,
            'playerBoundingRadius'          => 44,
            'playerJumpHeight'              => 150,
            'playerHeadHeightStand'         => 190,
            'playerHeadHeightCrouch'        => 140,
            'playerObstacleOvercomeHeight'  => 20,
            'playerFallDamageThreshold'     => 570,
        ]);

        // Warmup cache
        $game = GameFactory::createDebug();
        $game->loadMap(new TestMap());
        $player = new Player(1, Color::GREEN, true);
        $game->addPlayer($player);
        self::assertNotNull($player->attack());
    }

    public function testPlayersRangeShooting(): void
    {
        ////////
        $range = 2000;
        $playersCount = 10;
        ////////

        $game = GameFactory::createDebug();
        $game->loadMap($this->createMap($range));
        for ($i = 1; $i <= $playersCount; $i++) {
            $player = new Player($i, Color::GREEN, true);
            $game->addPlayer($player);
            if ($i === 1) {
                $player->getSight()->look(0, 0);
            } elseif ($i < ceil($playersCount / 2)) {
                $player->getSight()->look(-1, -1);
            } else {
                $player->getSight()->look(1, 1);
            }
            $this->assertInstanceOf(PistolGlock::class, $player->getEquippedItem());
        }
        $players = $game->getPlayers();
        $this->assertCount($playersCount, $players);

        $attackResults = [];
        $timer = new Timer();
        $timer->start();
        foreach ($players as $player) {
            $attackResults[] = $player->attack();
        }
        $game->tick();
        $took = $timer->stop();
        $this->assertSame(1, $game->getTickId());

        foreach ($players as $player) {
            $glock = $player->getEquippedItem();
            $this->assertInstanceOf(PistolGlock::class, $glock);
            $this->assertSame(PistolGlock::magazineCapacity - 1, $glock->getAmmo());
        }
        $this->assertCount($playersCount, $attackResults);
        foreach ($attackResults as $result) {
            $this->assertInstanceOf(AttackResult::class, $result);
            $hits = $result->getHits();
            $this->assertCount(1, $hits);
            $wall = $hits[0];
            $this->assertInstanceOf(Wall::class, $wall);
            $this->assertSame($range + 1, $wall->getBase());
            $this->assertGreaterThanOrEqual($range - 50, $result->getBullet()->getDistanceTraveled());
            $this->assertLessThanOrEqual($range + 50, $result->getBullet()->getDistanceTraveled());
        }
        $this->assertGreaterThanOrEqual($range, PistolGlock::range);
        $this->assertLessThan(22 * self::$timeScale, $took->asMilliseconds());
    }

    public function testTwoPlayersRangeShootingEachOther(): void
    {
        ////////
        $range = 5000;
        ////////

        $game = GameFactory::createDebug();
        $game->loadMap($this->createMap($range));
        $game->addPlayer(new Player(1, Color::GREEN, true));
        $game->addPlayer(new Player(2, Color::BLUE, false));
        $players = $game->getPlayers();
        $this->assertCount(2, $players);
        foreach ($players as $player) {
            $player->getInventory()->purchase($player, BuyMenuItem::KEVLAR_BODY_AND_HEAD);
        }

        $timer = new Timer();
        $timer->start();
        foreach ($players as $player) {
            $player->attack();
        }
        $game->tick();
        $took = $timer->stop();

        foreach ($players as $player) {
            $pistol = $player->getEquippedItem();
            $this->assertInstanceOf(AmmoBasedWeapon::class, $pistol);
            $this->assertSame($pistol::magazineCapacity - 1, $pistol->getAmmo());
            $this->assertGreaterThanOrEqual($range, $pistol::range);
        }
        foreach ($players as $player) {
            $this->assertLessThan(100, $player->getHealth(), "Player: '{$player->getId()}'");
            $this->assertTrue($player->isAlive());
        }
        $this->assertLessThan(27 * self::$timeScale, $took->asMilliseconds());
    }

    public function testPlayersMoving(): void
    {
        ////////
        $range = 2000;
        $tickCount = 20;
        $playersCount = 10;
        ////////

        $game = GameFactory::createDebug();
        $game->loadMap($this->createMap($range));
        for ($i = 1; $i <= $playersCount; $i++) {
            $player = new Player($i, Color::GREEN, true);
            $game->addPlayer($player);
            $player->getSight()->look(2, -10);
            $this->assertSame(50, $player->getPositionClone()->z);
        }
        $players = $game->getPlayers();
        $this->assertCount($playersCount, $players);

        $timer = new Timer();
        $timer->start();
        for ($i = 0; $i < $tickCount; $i++) {
            foreach ($players as $player) {
                $player->jump();
                $player->moveForward();
            }
            $game->tick();
        }
        $took = $timer->stop();
        $this->assertSame($tickCount, $game->getTickId());

        foreach ($players as $player) {
            $this->assertGreaterThan(50, $player->getPositionClone()->z);
            $this->assertGreaterThan(200, $player->getPositionClone()->z);
        }
        $this->assertLessThan(17 * self::$timeScale, $took->asMilliseconds());
    }

    public function test3DMovement(): void
    {
        $coordinates = [];
        $maxDistance = 100000;

        $timer = new Timer();
        $timer->start();
        for ($distance = 1; $distance <= $maxDistance; $distance++) {
            $coordinates = Util::movementXYZ(42, 42, $distance);
        }
        $took = $timer->stop();
        $this->assertSame([49726, 66913, 55226], $coordinates);
        $this->assertLessThan(38 * self::$timeScale, $took->asMilliseconds());
    }

    public function test2DMovement(): void
    {
        $coordinates = [];
        $maxDistance = 100000;

        $timer = new Timer();
        $timer->start();
        for ($distance = 1; $distance <= $maxDistance; $distance++) {
            $coordinates = Util::movementXZ(42, $distance);
        }
        $took = $timer->stop();
        $this->assertSame([66913, 74314], $coordinates);
        $this->assertLessThan(23 * self::$timeScale, $took->asMilliseconds());
    }

    public function testMolotov(): void
    {
        $game = GameFactory::createDebug();
        $game->loadMap($this->createMolotovMap());

        $timer = new Timer();
        $timer->start();
        $game->getWorld()->regenerateNavigationMeshes();
        $took = $timer->stop();
        $this->assertGreaterThan(10, $took->asMilliseconds());
        $this->assertLessThan(120 * self::$timeScale, $took->asMilliseconds());

        $player = new Player(1, Color::GREEN, true);
        $game->addPlayer($player);
        $this->assertTrue($player->buyItem(BuyMenuItem::GRENADE_MOLOTOV));
        foreach (range(0, Util::millisecondsToFrames(Molotov::equipReadyTimeMs)) as $i) {
            $game->tick();
        }
        $flammableItem = $player->getEquippedItem();
        $this->assertInstanceOf(Flammable::class, $flammableItem);
        $player->getSight()->look(0, -90);

        $timer->start();
        $attackResult = $player->attack();
        $took = $timer->stop();
        $this->assertNotNull($attackResult);
        $this->assertLessThan(1 * self::$timeScale, $took->asMilliseconds());

        foreach (range(1, Util::millisecondsToFrames(Molotov::MAX_TIME_MS)) as $i) {
            $timer->start();
            $game->tick();
            $took = $timer->stop();
            $this->assertLessThan(1 * self::$timeScale, $took->asMilliseconds(), "Tick {$game->getTickId()}");

            if ($game->getRoundNumber() === 2) {
                break;
            }
        }
        $this->assertSame(2, $game->getRoundNumber());
    }

    private function createMolotovMap(): Map
    {
        return new class() extends BoxMap {
            private Box $boundary;

            public function __construct()
            {
                $this->boundary = new Box(new Point(11, 12, 13), 1000, 2000, 1000);
                $this->addBox($this->boundary);
            }

            public function getBuyArea(bool $forAttackers): Box
            {
                return $this->boundary;
            }

            public function getPlantArea(): Box
            {
                return $this->boundary;
            }

            public function getSpawnPositionAttacker(): array
            {
                return [new Point(500, 12, 500)];
            }
        };
    }

    private function createMap(int $depth = 2000): Map
    {
        return new class($depth) extends BoxMap {
            private Box $boundary;

            public function __construct(private int $depth)
            {
                $this->boundary = new Box(new Point(1, 1, 1), 1100, 1500, $depth);
                $this->addBox($this->boundary);
                for ($i = 100; $i <= $depth; $i += 100) {
                    $this->addBox(new Box(new Point(10, 1, $i), 10, 1400, 90));
                    $this->addBox(new Box(new Point(1090, 10, $i), 10, 1400, 90));
                }
            }

            public function getBuyArea(bool $forAttackers): Box
            {
                return $this->boundary;
            }

            public function getPlantArea(): Box
            {
                return $this->boundary;
            }

            public function getSpawnRotationAttacker(): int
            {
                return 0;
            }

            public function getSpawnRotationDefender(): int
            {
                return 180;
            }

            public function getSpawnPositionAttacker(): array
            {
                return [
                    new Point(100, 1, 50),
                    new Point(200, 1, 50),
                    new Point(300, 1, 50),
                    new Point(400, 1, 50),
                    new Point(500, 1, 50),
                    new Point(600, 1, 50),
                    new Point(700, 1, 50),
                    new Point(800, 1, 50),
                    new Point(900, 1, 50),
                    new Point(1000, 1, 50),
                ];
            }

            public function getSpawnPositionDefender(): array
            {
                return [
                    new Point(100, 1, $this->depth - 50),
                ];
            }

        };
    }


}
