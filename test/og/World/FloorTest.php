<?php

namespace Test\World;

use cs\Core\Box;
use cs\Core\Floor;
use cs\Core\GameState;
use cs\Core\Point;
use cs\Core\Setting;
use Test\BaseTestCase;

class FloorTest extends BaseTestCase
{

    public function testPlayerCannotFallDownThroughFloor(): void
    {
        $floorHeight = 20;
        $game = $this->createTestGame(Setting::moveDistancePerTick() / 2);
        $p = $game->getPlayer(1);
        $p->setPosition(new Point($p->getBoundingRadius() * 4, $floorHeight * 2, $p->getBoundingRadius()));

        $base = new Point($p->getPositionClone()->x - $p->getBoundingRadius() / 2, $floorHeight, 0);
        for ($i = 1; $i <= Setting::moveDistancePerTick() * 2; $i++) {
            $game->getWorld()->addFloor(new Floor($base->clone()->addX(rand(-$p->getBoundingRadius() + 1, $p->getBoundingRadius() - 1)), 100, 2));
            $base->addZ($p->getBoundingRadius());
        }

        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertSame($floorHeight, $p->getPositionClone()->y);
        $this->assertGreaterThan(Setting::moveDistancePerTick(), $p->getPositionClone()->z);
    }

    public function testPlayerCanFallDownThroughFloor(): void
    {
        $floorHeight = 20;
        $game = $this->createTestGame(Setting::moveDistancePerTick() / 2);
        $p = $game->getPlayer(1);
        $p->setPosition(new Point($p->getBoundingRadius() * 4, $floorHeight * 2, $p->getBoundingRadius()));

        $base = new Point($p->getPositionClone()->x, $floorHeight, 0);
        for ($i = 1; $i <= Setting::moveDistancePerTick() * 2; $i++) {
            if ($i === 5) {
                $base->addX($p->getBoundingRadius() + 1);
            }
            $game->getWorld()->addFloor(new Floor($base->clone(), 100, 2));
            $base->addZ($p->getBoundingRadius());
        }

        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertSame(0, $p->getPositionClone()->y);
        $this->assertGreaterThan(Setting::moveDistancePerTick(), $p->getPositionClone()->z);
    }

    public function testPlayerCannotFallDownThroughBoxFloor(): void
    {
        $floorHeight = 20;
        $game = $this->createTestGame(Setting::moveDistancePerTick() / 2);
        $p = $game->getPlayer(1);
        $p->setPosition(new Point($p->getBoundingRadius() * 4, $floorHeight * 4, $p->getBoundingRadius()));

        $base = new Point(-$p->getPositionClone()->x, -$floorHeight, 0);
        for ($i = 1; $i <= Setting::moveDistancePerTick() * 2; $i++) {
            $game->getWorld()->addBox(
                new Box(
                    $base->clone()->addX(rand(-$p->getBoundingRadius(), $p->getBoundingRadius()))
                    , 400, $floorHeight * 2, $p->getBoundingRadius()
                )
            );
            $base->addZ($p->getBoundingRadius());
        }

        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->moveForward();
        });
        $game->start();
        $this->assertSame($floorHeight, $p->getPositionClone()->y);
        $this->assertGreaterThan(Setting::moveDistancePerTick(), $p->getPositionClone()->z);
    }

    public function testPlayerBoxTunnel(): void
    {
        $floorHeight = 0;
        $game = $this->createTestGame(20);
        $p = $game->getPlayer(1);

        $base = new Point(-200, $floorHeight, 0);
        for ($i = 1; $i <= Setting::moveDistancePerTick() * 2; $i++) {
            $game->getWorld()->addBox(
                new Box(
                    $base->clone()->addX(rand(-$p->getBoundingRadius(), $p->getBoundingRadius()))
                    , 400, $p->getHeadHeight(), $p->getBoundingRadius(), Box::SIDE_ALL ^ (Box::SIDE_BACK | Box::SIDE_FRONT)
                )
            );
            $base->addZ($p->getBoundingRadius());
        }

        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertSame($floorHeight, $p->getPositionClone()->y);
        $this->assertSame(20 * Setting::moveDistancePerTick(), $p->getPositionClone()->z);
    }

}
