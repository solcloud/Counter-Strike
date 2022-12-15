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

    protected function _createGame(int $tickMax): TestGame
    {
        $game = $this->createTestGame($tickMax);
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
        $game = $this->_createGame(250);
        $player = $game->getPlayer(1);
        $wall = new Wall(new Point(800, 0, 900 - 3), true, 200);
        $game->getWorld()->addWall($wall);

        $game->onTick(function () use ($player) {
            $player->moveRight();
            $player->moveForward();
        });
        $game->start();
        $this->assertGreaterThan(500, $player->getPositionClone()->y);
        $this->assertGreaterThan($wall->getStart()->z, $player->getPositionClone()->z);
        $this->assertGreaterThan($wall->getStart()->x, $player->getPositionClone()->x);
    }

    public function testDiagonalRampMovement2(): void
    {
        $game = $this->_createGame(250);
        $player = $game->getPlayer(1);
        $wall = new Wall(new Point(800, 0, 900 - 2), true, 200);
        $wall1 = new Wall(new Point(1450, 10, 500), false, 800);
        $game->getWorld()->addWall($wall);
        $game->getWorld()->addWall($wall1);

        $player->getSight()->lookAt(61, 12);
        $game->onTick(function () use ($player) {
            $player->moveForward();
        });
        $game->start();
        $this->assertGreaterThan(500, $player->getPositionClone()->y);
        $this->assertGreaterThan($wall->getStart()->z, $player->getPositionClone()->z);
        $this->assertSame($wall1->getStart()->x - $player->getBoundingRadius() - 1, $player->getPositionClone()->x);
    }

    public function testDiagonalRampMovement3(): void
    {
        $game = $this->_createGame(340);
        $player = $game->getPlayer(1);
        $wall = new Wall(new Point(100, 0, 900 - 11), true, 900);
        $game->getWorld()->addWall($wall);

        $player->getSight()->lookAt(41, -11);
        $game->onTick(function () use ($player) {
            $player->moveForward();
        });
        $game->start();
        $this->assertGreaterThan(500, $player->getPositionClone()->y);
        $this->assertGreaterThan($wall->getStart()->z, $player->getPositionClone()->z);
        $this->assertGreaterThan($wall->getStart()->x, $player->getPositionClone()->x);
    }

    public function testRampMovementWithWallTouching(): void
    {
        $game = $this->createTestGame(250);
        $player = $game->getPlayer(1);
        $wall = new Wall(new Point(-200, 0, 250), true, 400);
        $game->getWorld()->addWall($wall);
        $game->getWorld()->addRamp(
            new Ramp($player->getPositionClone()->addZ($player->getBoundingRadius() + 10)->addX(-50),
                new Point2D(0, 1),
                20,
                200
            )
        );

        $game->onTick(function () use ($player) {
            $player->moveLeft();
            $player->moveForward();
        });
        $game->start();
        $this->assertSame($wall->getStart()->z - $player->getBoundingRadius() - 1, $player->getPositionClone()->z);
        $this->assertSame(200, $player->getPositionClone()->y);
        $this->assertSame(0, $player->getPositionClone()->x);
    }

    public function testDiagonal1UnitWallMovement(): void
    {
        $unit = 1;
        $game = $this->createTestGame(200);
        $player = $game->getPlayer(1);
        $player->getSight()->lookHorizontal(2);

        for ($i = 0; $i < 200; $i += $unit) {
            $game->getWorld()->addWall(new Wall(new Point($i + $unit, 0, 250 + $i), false, $unit));
            $game->getWorld()->addWall(new Wall(new Point($i, 0, 250 + $i), true, $unit));
        }

        $game->onTick(function () use ($player) {
            $player->moveForward();
        });
        $game->start();
        $this->assertPositionSame(new Point(204, 0, 363), $player->getPositionClone());
    }

    public function testDiagonal10UnitWallMovement(): void
    {
        $unit = 10;
        $game = $this->createTestGame(200);
        $player = $game->getPlayer(1);
        $player->getSight()->lookHorizontal(2);

        for ($i = 0; $i < 400; $i += $unit) {
            $game->getWorld()->addWall(new Wall(new Point($i + $unit, 0, 250 + $i), false, $unit));
            $game->getWorld()->addWall(new Wall(new Point($i, 0, 250 + $i), true, $unit));
        }

        $game->onTick(function () use ($player) {
            $player->moveForward();
        });
        $game->start();
        $this->assertPositionSame(new Point(204, 0, 355), $player->getPositionClone());
    }

    public function testDiagonal101UnitWallMovement(): void
    {
        $unit = 101;
        $game = $this->createTestGame(200);
        $player = $game->getPlayer(1);
        $player->getSight()->lookHorizontal(2);

        for ($i = 0; $i < 300; $i += $unit) {
            $game->getWorld()->addWall(new Wall(new Point($i + $unit, 0, 250 + $i), false, $unit));
            $game->getWorld()->addWall(new Wall(new Point($i, 0, 250 + $i), true, $unit));
        }

        $game->onTick(function () use ($player) {
            $player->moveForward();
        });
        $game->start();
        $this->assertPositionSame(new Point(206, 0, 306), $player->getPositionClone());
    }

}
