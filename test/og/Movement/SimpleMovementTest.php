<?php

namespace Test\Movement;

use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Setting;
use Test\BaseTestCase;

class SimpleMovementTest extends BaseTestCase
{

    public function testPlayerCanMoveRight(): void
    {
        $ticks = 20;
        $game = $this->createOneRoundGame($ticks);
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveRight());
        $game->start();
        $this->assertPlayerPosition($game, new Point($ticks * Setting::moveDistancePerTick(), 0, 0));
    }

    public function testPlayerCanMoveForward(): void
    {
        $ticks = 21;
        $game = $this->createOneRoundGame($ticks);
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertPlayerPosition($game, new Point(0, 0, $ticks * Setting::moveDistancePerTick()));
    }

    public function testPlayerDiagonalMovement(): void
    {
        $game = $this->createOneRoundGame();
        $game->onTick(function (GameState $state): void {
            $state->getPlayer(1)->moveForward();
            $state->getPlayer(1)->moveRight();
        });

        $game->start();
        $this->assertGreaterThan(Setting::moveDistancePerTick() / 2, $game->getPlayer(1)->getPositionClone()->x);
        $this->assertLessThan(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionClone()->x);
        $this->assertGreaterThan(Setting::moveDistancePerTick() / 2, $game->getPlayer(1)->getPositionClone()->z);
        $this->assertLessThan(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionClone()->z);
    }

    public function testPlayerMovementSpeed(): void
    {
        $game = $this->createOneRoundGame(2);
        $game->onTick(function (GameState $state): void {
            $state->getPlayer(1)->moveForward();
        });

        $game->start();
        $this->assertPlayerPosition($game, new Point(0, 0, 2 * Setting::moveDistancePerTick()));
    }

    public function testPlayerCrouch(): void
    {
        $game = $this->createOneRoundGame(Setting::tickCountCrouch() + 4);
        $game->onTick(function (GameState $state): void {
            $state->getPlayer(1)->crouch();
        });

        $game->start();
        $this->assertSame(Setting::playerHeadHeightCrouch(), $game->getPlayer(1)->getHeadHeight());
    }

    public function testPlayerCrouchSpeed(): void
    {
        $game = $this->createTestGame();
        $this->playPlayer($game, [
            fn(Player $p) => $p->crouch(),
            $this->waitXTicks(Setting::tickCountCrouch()),
            fn(Player $p) => $p->moveForward(),
            $this->endGame(),
        ]);

        $game->start();
        $this->assertSame(Setting::moveDistanceCrouchPerTick(), $game->getPlayer(1)->getPositionClone()->z);
    }

    public function testPlayerMovementWalkSpeed(): void
    {
        $game = $this->createOneRoundGame();
        $game->onTick(function (GameState $state): void {
            $state->getPlayer(1)->speedWalk();
            $state->getPlayer(1)->moveForward();
        });

        $game->start();
        $this->assertPlayerPosition($game, new Point(0, 0, Setting::moveDistanceWalkPerTick()));
    }

    public function testPlayerCantMoveLeftFromOrigin(): void
    {
        $game = $this->createOneRoundGame(4);
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveLeft());
        $game->start();
        $this->assertPlayerPosition($game, new Point(0, 0, 0));
    }

    public function testPlayerMoveInLastDirectionInOneTick(): void
    {
        $game = $this->createOneRoundGame(20);
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->moveLeft();
            $state->getPlayer(1)->moveRight();
        });
        $game->start();
        $this->assertPlayerPosition($game, new Point(20 * Setting::moveDistancePerTick(), 0, 0));
    }


}
