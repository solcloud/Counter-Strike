<?php

namespace Test\Game;

use cs\Core\Box;
use cs\Core\GameFactory;
use cs\Core\Player;
use cs\Core\Point;
use cs\Enum\Color;
use cs\Map\BoxMap;
use cs\Map\Map;
use cs\Weapon\PistolGlock;
use SebastianBergmann\Timer\Timer;
use Test\BaseTestCase;

class PerformanceTest extends BaseTestCase
{

    public function test1(): void
    {
        $playersCount = 10;
        $game = GameFactory::createDebug();
        $game->loadMap($this->createMap());
        for ($i = 1; $i <= $playersCount; $i++) {
            $player = new Player($i, Color::GREEN, true);
            $game->addPlayer($player);
            $player->getSight()->lookAt(rand(-15, 15), rand(-10, 10));
            $this->assertInstanceOf(PistolGlock::class, $player->getEquippedItem());
        }
        $this->assertCount($playersCount, $game->getPlayers());

        $timer = new Timer();
        $timer->start();
        foreach ($game->getPlayers() as $player) {
            $player->attack();
        }
        $game->tick(0);
        $took = $timer->stop();
        $this->assertSame(0, $game->getTickId());

        foreach ($game->getPlayers() as $player) {
            $glock = $player->getEquippedItem();
            $this->assertInstanceOf(PistolGlock::class, $glock);
            $this->assertSame(PistolGlock::magazineCapacity - 1, $glock->getAmmo());
        }
        $this->assertLessThan(150, $took->asMilliseconds()); // TODO do it consistently with 10 players under 10ms :)
    }

    private function createMap(): Map
    {
        return new class extends BoxMap {
            private Box $boundary;

            public function __construct()
            {
                $this->boundary = new Box(new Point(1, 1, 1), 1100, 2000, 2000);
                for ($i = 100; $i <= 4000; $i += 100) {
                    $this->addBox(new Box(new Point(10, 1, $i), 10, 400, 100));
                    $this->addBox(new Box(new Point(1190, 10, $i), 10, 400, 100));
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
