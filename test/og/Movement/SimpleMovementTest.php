<?php

namespace Test\Movement;

use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Point;
use Test\BaseTestCase;

class SimpleMovementTest extends BaseTestCase
{

    public function testPlayerCanMoveRight(): void
    {
        $ticks = 20;
        $game = $this->createOneRoundGame($ticks);
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveRight());
        $game->start();
        $this->assertPlayerPosition($game, new Point($ticks * Player::speedMove, 0, 0));
    }

    public function testPlayerCanMoveForward(): void
    {
        $ticks = 21;
        $game = $this->createOneRoundGame($ticks);
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertPlayerPosition($game, new Point(0, 0, $ticks * Player::speedMove));
    }

    public function testPlayerDiagonalMovement(): void
    {
        $game = $this->createOneRoundGame();
        $game->onTick(function (GameState $state): void {
            $state->getPlayer(1)->moveForward();
            $state->getPlayer(1)->moveRight();
        });

        $game->start();
        $this->assertGreaterThan(Player::speedMove / 2, $game->getPlayer(1)->getPositionImmutable()->x);
        $this->assertLessThan(Player::speedMove, $game->getPlayer(1)->getPositionImmutable()->x);
        $this->assertGreaterThan(Player::speedMove / 2, $game->getPlayer(1)->getPositionImmutable()->z);
        $this->assertLessThan(Player::speedMove, $game->getPlayer(1)->getPositionImmutable()->z);
    }

    public function testPlayerMovementSpeed(): void
    {
        $game = $this->createOneRoundGame(2);
        $game->onTick(function (GameState $state): void {
            $state->getPlayer(1)->moveForward();
        });

        $game->start();
        $this->assertPlayerPosition($game, new Point(0, 0, 2 * Player::speedMove));
    }

    public function testPlayerCrouch(): void
    {
        $game = $this->createOneRoundGame(Player::tickCountCrouch);
        $game->onTick(function (GameState $state): void {
            $state->getPlayer(1)->crouch();
        });

        $game->start();
        $this->assertSame(Player::headHeightCrouch, $game->getPlayer(1)->getHeadHeight());
    }

    public function testPlayerMovementWalkSpeed(): void
    {
        $game = $this->createOneRoundGame();
        $game->onTick(function (GameState $state): void {
            $state->getPlayer(1)->speedWalk();
            $state->getPlayer(1)->moveForward();
        });

        $game->start();
        $this->assertPlayerPosition($game, new Point(0, 0, Player::speedMoveWalk));
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
        $this->assertPlayerPosition($game, new Point(20 * Player::speedMove, 0, 0));
    }


}