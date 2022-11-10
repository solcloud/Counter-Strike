<?php

namespace Test\Game;

use cs\Core\Box;
use cs\Core\GameFactory;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Util;
use cs\Enum\Color;
use cs\Map\BoxMap;
use cs\Map\Map;
use cs\Weapon\PistolGlock;
use SebastianBergmann\Timer\Timer;
use Test\BaseTestCase;

class PerformanceTest extends BaseTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('CI') !== false) {
            $this->markTestSkipped('CI too slow');
        }
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
                $player->getSight()->lookAt(0, 0);
            } elseif ($i < ceil($playersCount / 2)) {
                $player->getSight()->lookAt(-1, -1);
            } else {
                $player->getSight()->lookAt(1, 1);
            }
            $this->assertInstanceOf(PistolGlock::class, $player->getEquippedItem());
        }
        $players = $game->getPlayers();
        $this->assertCount($playersCount, $players);

        $timer = new Timer();
        $timer->start();
        foreach ($players as $player) {
            $player->attack();
        }
        $game->tick(0);
        $took = $timer->stop();
        $this->assertSame(0, $game->getTickId());

        foreach ($players as $player) {
            $glock = $player->getEquippedItem();
            $this->assertInstanceOf(PistolGlock::class, $glock);
            $this->assertSame(PistolGlock::magazineCapacity - 1, $glock->getAmmo());
        }
        $this->assertGreaterThanOrEqual($range, PistolGlock::range);
        $this->assertLessThan(90, $took->asMilliseconds());
    }

    public function test3DMovement(): void
    {
        $maxDistance = 1000;
        $coordinates = [];

        $timer = new Timer();
        $timer->start();
        for ($distance = 1; $distance <= $maxDistance; $distance++) {
            $coordinates[] = Util::movementXYZ(42, 42, $distance);
        }
        $took = $timer->stop();
        $this->assertSame([497, 669, 552], $coordinates[$maxDistance - 1]);

        // Check if coordinates grow only by max one unit from previous
        $prev = [0, 0, 0];
        foreach ($coordinates as $test) {
            [$x, $y, $z] = $test;
            if (false === ($prev[0] === $x || $prev[0] + 1 === $x)) {
                $this->fail("X grows more than 1 unit, from '{$prev[0]}' to '{$x}'");
            }
            if (false === ($prev[1] === $y || $prev[1] + 1 === $y)) {
                $this->fail("Y grows more than 1 unit, from '{$prev[0]}' to '{$y}'");
            }
            if (false === ($prev[2] === $z || $prev[2] + 1 === $z)) {
                $this->fail("Z grows more than 1 unit, from '{$prev[0]}' to '{$z}'");
            }
            $prev = $test;
        }

        $this->assertLessThan(820, $took->asMicroseconds());
    }

    public function test2DMovement(): void
    {
        $maxDistance = 1000;
        $coordinates = [];

        $timer = new Timer();
        $timer->start();
        for ($distance = 1; $distance <= $maxDistance; $distance++) {
            $coordinates[] = Util::horizontalMovementXZ(42, $distance);
        }
        $took = $timer->stop();
        $this->assertSame([669, 743], $coordinates[$maxDistance - 1]);

        // Check if coordinates grow only by max one unit from previous
        $prev = [0, 0];
        foreach ($coordinates as $test) {
            [$x, $y] = $test;
            if (false === ($prev[0] === $x || $prev[0] + 1 === $x)) {
                $this->fail("X grows more than 1 unit, from '{$prev[0]}' to '{$x}'");
            }
            if (false === ($prev[1] === $y || $prev[1] + 1 === $y)) {
                $this->fail("Y grows more than 1 unit, from '{$prev[0]}' to '{$y}'");
            }
            $prev = $test;
        }

        $this->assertLessThan(380, $took->asMicroseconds());
    }

    private function createMap(int $depth = 2000): Map
    {
        return new class($depth) extends BoxMap {
            private Box $boundary;

            public function __construct(int $depth)
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

        };
    }


}
