<?php

namespace Test\World;

use cs\Core\Point;
use cs\Core\Point2D;
use cs\Core\Ramp;
use cs\Core\Wall;
use Test\BaseTestCase;
use Test\TestGame;

class RampTest extends BaseTestCase
{

    protected function _createGame(): TestGame
    {
        $game = $this->createTestGame(200);
        $player = $game->getPlayer(1);

        $stepDepth = 33;
        $ramp1 = new Ramp(
            new Point(0, 0, $player->getBoundingRadius() * 3),
            new Point2D(0, 1),
            (int)floor(800 / $stepDepth),
            2123,
            true,
            $stepDepth
        );
        $game->getWorld()->addRamp($ramp1);
        $ramp2 = new Ramp(
            new Point(1000, $ramp1->stepHeight * $ramp1->stepCount, 910),
            new Point2D(0, 1), (int)floor(1800 / $stepDepth * 2),
            8123,
            true,
            $stepDepth
        );
        $game->getWorld()->addRamp($ramp2);
        return $game;
    }

    public function testDiagonalRampMovement1(): void
    {
        $game = $this->_createGame();
        $player = $game->getPlayer(1);
        $wall = new Wall(new Point(800, 0, 900 + 1), true, 200);
        $game->getWorld()->addWall($wall);

        $game->onTick(function () use ($player) {
            $player->moveRight();
            $player->moveForward();
        });
        $game->start();
        $this->assertGreaterThan(0, $player->getPositionClone()->y);
        $this->assertGreaterThan($wall->getStart()->z, $player->getPositionClone()->z);
        $this->assertGreaterThan($wall->getStart()->x, $player->getPositionClone()->x);
    }

    public function testDiagonalRampMovement2(): void
    {
        $game = $this->_createGame();
        $player = $game->getPlayer(1);
        $wall = new Wall(new Point(800, 0, 900 + 1), true, 200);
        $game->getWorld()->addWall($wall);

        $player->getSight()->lookAt(61, 12);
        $game->onTick(function () use ($player) {
            $player->moveForward();
        });
        $game->start();
        $this->assertGreaterThan(0, $player->getPositionClone()->y);
        $this->assertGreaterThan($wall->getStart()->z, $player->getPositionClone()->z);
        $this->assertGreaterThan($wall->getStart()->x, $player->getPositionClone()->x);
    }

    public function testDiagonalRampMovement3(): void
    {
        $game = $this->_createGame();
        $player = $game->getPlayer(1);
        $wall = new Wall(new Point(100, 0, 900 + 1), true, 900);
        $game->getWorld()->addWall($wall);

        $player->getSight()->lookAt(41, -11);
        $game->onTick(function () use ($player) {
            $player->moveForward();
        });
        $game->start();
        $this->assertGreaterThan(0, $player->getPositionClone()->y);
        $this->assertGreaterThan($wall->getStart()->z, $player->getPositionClone()->z);
        $this->assertGreaterThan($wall->getStart()->x, $player->getPositionClone()->x);
    }

}
