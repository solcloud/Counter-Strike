<?php

namespace Test\World;

use cs\Core\Box;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Setting;
use cs\Enum\Color;
use Test\BaseTestCase;

class PlayerBoostTest extends BaseTestCase
{

    public function testPlayerCanStandOnTopOfOtherPlayer(): void
    {
        $player2 = new Player(2, Color::GREEN, false);
        $game = $this->createTestGame(20);
        $game->addPlayer($player2);
        $player1 = $game->getPlayer(1);
        $player2->setPosition($player1->getPositionClone()->addY($player1->getHeadHeight() + 10));

        $game->start();
        $this->assertPositionSame(new Point(0, $player1->getHeadHeight() + 1, 0), $game->getPlayer(2)->getPositionClone());
        $this->assertFalse($player2->isFlying());
        $this->assertTrue($player2->canJump());
    }

    public function testPlayerCanJumpOverHighWallUsingOtherPlayer(): void
    {
        $player2 = new Player(2, Color::GREEN, false);
        $game = $this->createTestGame(Setting::tickCountCrouch() * 5);
        $game->addPlayer($player2);
        $player1 = $game->getPlayer(1);
        $player2->setPosition($player1->getPositionClone()->addZ(Setting::moveDistancePerTick() * 3));
        $player2->crouch();
        $boxStartPos = $player2->getPositionClone()->addZ(Setting::moveDistancePerTick() * 2);
        $box = new Box(
            $boxStartPos,
            200,
            Setting::playerHeadHeightCrouch() + Setting::playerJumpHeight(),
            1000,
        );
        $game->getWorld()->addBox($box);

        $game->onTick(function (GameState $state) use ($player1, $player2) {
            $player2->moveForward();
            $player1->moveForward();
            if ($state->getTickId() < Setting::tickCountCrouch()) {
                return;
            }
            $player1->jump();
        });
        $game->start();
        $this->assertPositionSame($boxStartPos, $player2->getPositionClone()->addZ($player2->getBoundingRadius() + 1));
        $this->assertGreaterThanOrEqual($box->heightY, $player1->getPositionClone()->y);
        $this->assertGreaterThan($boxStartPos->z, $player1->getPositionClone()->z);
    }

    public function testPlayerFallDownWhenBoosterMoveAway(): void
    {
        $player2 = new Player(2, Color::GREEN, false);
        $game = $this->createTestGame(20);
        $game->addPlayer($player2);
        $player1 = $game->getPlayer(1);
        $player2->setPosition($player1->getPositionClone()->addY($player1->getHeadHeight() + 10));

        $game->start();
        $p2pos = $game->getPlayer(2)->getPositionClone();
        $this->assertGreaterThan(0, $p2pos->y);
        $this->assertPositionSame(new Point(0, $player1->getHeadHeight() + 1, 0), $p2pos);
        $this->assertFalse($player2->isFlying());
        $this->assertTrue($player2->canJump());

        for ($i = $game->getTickId(); $i <= 150; $i++) {
            $player1->moveForward();
            $game->tick($i);
        }
        $this->assertPositionSame($p2pos->clone()->setY(0), $player2->getPositionClone());
        $this->assertGreaterThan($p2pos->z, $player1->getPositionClone()->z);
    }

    public function testPlayerFallDownWhenBoosterCrouchAndGoUpWhenHeJumpAndBoosterStand(): void
    {
        $player2 = new Player(2, Color::GREEN, false);
        $game = $this->createTestGame(20);
        $game->addPlayer($player2);
        $player1 = $game->getPlayer(1);
        $player2->setPosition($player1->getPositionClone()->addY($player1->getHeadHeight() + 10));

        $game->start();
        $p2pos = $game->getPlayer(2)->getPositionClone();
        $this->assertGreaterThan(0, $p2pos->y);
        $this->assertSame($player1->getHeadHeight(), Setting::playerHeadHeightStand());
        $this->assertPositionSame(new Point(0, $player1->getHeadHeight() + 1, 0), $p2pos);
        $this->assertFalse($player2->isFlying());
        $this->assertTrue($player2->canJump());

        $player1->crouch();
        $startTickId = $game->getTickId();
        for ($i = $startTickId; $i <= $startTickId + Setting::tickCountCrouch(); $i++) {
            $game->tick($i);
        }
        $this->assertSame($player1->getHeadHeight(), Setting::playerHeadHeightCrouch());
        $this->assertPositionSame($p2pos->clone()->setY($player1->getHeadHeight() + 1), $player2->getPositionClone());

        $player1->stand();
        $player2->jump();
        $startTickId = $game->getTickId();
        for ($i = $startTickId; $i <= $startTickId + max(Setting::tickCountJump(), Setting::tickCountCrouch()); $i++) {
            $game->tick($i);
        }
        $this->assertSame($player1->getHeadHeight(), Setting::playerHeadHeightStand());
        $this->assertPositionSame($player2->getPositionClone()->setY($player1->getHeadHeight() + 1), $player2->getPositionClone());
    }

    public function testPlayerCanMoveAfterStandingOnTopOfOtherPlayer(): void
    {
        $player2 = new Player(2, Color::GREEN, false);
        $game = $this->createTestGame(20);
        $game->addPlayer($player2);
        $player1 = $game->getPlayer(1);
        $player2->setPosition($player1->getPositionClone()->addY($player1->getHeadHeight() + 10));

        $game->start();
        $p2pos = $game->getPlayer(2)->getPositionClone();
        $this->assertPositionSame(new Point(0, $player1->getHeadHeight() + 1, 0), $p2pos);
        $player2->moveRight();
        $game->tick($game->getTickId() + 1);
        $this->assertPositionNotSame($p2pos, $game->getPlayer(2)->getPositionClone());
        $this->assertTrue($player2->canJump());
    }

    public function testPlayerCannotJumpWhenOtherPlayerStandingOnThem(): void
    {
        $player2 = new Player(2, Color::GREEN, false);
        $game = $this->createTestGame(20);
        $game->addPlayer($player2);
        $player1 = $game->getPlayer(1);
        $player2->setPosition($player1->getPositionClone()->addY($player1->getHeadHeight() + 10));

        $game->start();
        $p2pos = $game->getPlayer(2)->getPositionClone();
        $this->assertPositionSame(new Point(0, $player1->getHeadHeight() + 1, 0), $p2pos);
        $player1->jump();
        $game->tick($game->getTickId() + 1);
        $this->assertSame(0, $player1->getPositionClone()->y);
        $this->assertPositionSame($p2pos, $game->getPlayer(2)->getPositionClone());
        $this->assertTrue($player2->canJump());
    }

}
