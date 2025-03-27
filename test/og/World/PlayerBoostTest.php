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
        $game = $this->createTestGameNoPause(20);
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
        $game = $this->createTestGameNoPause(Setting::tickCountCrouch() * 5);
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
        $game = $this->createTestGameNoPause(20);
        $game->addPlayer($player2);
        $player1 = $game->getPlayer(1);
        $player2->setPosition($player1->getPositionClone()->addY($player1->getHeadHeight() + 10));

        $game->start();
        $p2pos = $game->getPlayer(2)->getPositionClone();
        $this->assertGreaterThan(0, $p2pos->y);
        $this->assertSame($player1->getHeadFloor()->getY(), $player1->getHeadHeight() + 1);
        $this->assertPositionSame(new Point(0, $player1->getHeadFloor()->getY(), 0), $p2pos);
        $this->assertFalse($player2->isFlying());
        $this->assertTrue($player2->canJump());

        for ($i = $game->getTickId(); $i <= 150; $i++) {
            $player1->moveForward();
            $game->tick();
        }
        $this->assertPositionSame($p2pos->clone()->setY(0), $player2->getPositionClone());
        $this->assertGreaterThan($p2pos->z, $player1->getPositionClone()->z);
    }

    public function testPlayerFallDownWhenBoosterCrouchAndGoUpWhenHeJumpAndBoosterStand(): void
    {
        $player2 = new Player(2, Color::GREEN, false);
        $game = $this->createTestGameNoPause(20);
        $game->addPlayer($player2);
        $player1 = $game->getPlayer(1);
        $player1->setPosition(new Point(500, 0, 500));
        $player2->setPosition($player1->getPositionClone()->addY($player1->getHeadHeight() + 10));
        $game->addPlayer(new Player(3, Color::ORANGE, true));
        $game->getPlayer(3)->setPosition(new Point(1500, 0, 1500));

        $game->start();
        $p2pos = $game->getPlayer(2)->getPositionClone();
        $this->assertGreaterThan(0, $p2pos->y);
        $this->assertSame($player1->getHeadHeight(), Setting::playerHeadHeightStand());
        $this->assertPositionSame(new Point(500, $player1->getHeadHeight() + 1, 500), $p2pos);
        $this->assertFalse($player2->isFlying());
        $this->assertTrue($player2->canJump());

        $player1->crouch();
        for ($i = 1; $i <= Setting::tickCountCrouch(); $i++) {
            $game->tick();
        }

        $this->assertSame($player1->getHeadHeight(), Setting::playerHeadHeightCrouch());
        $this->assertPositionSame($p2pos->clone()->setY($player1->getHeadHeight() + 1), $player2->getPositionClone());
        $player1->stand();
        for ($i = 1; $i <= Setting::tickCountCrouch(); $i++) {
            $game->tick();
        }
        $this->assertSame($player1->getHeadHeight(), Setting::playerHeadHeightCrouch());
        $this->assertPositionSame($p2pos->clone()->setY($player1->getHeadHeight() + 1), $player2->getPositionClone());

        $player2->jump();
        for ($i = 1; $i <= 2 * max(Setting::tickCountJump(), Setting::tickCountCrouch()); $i++) {
            $game->tick();
        }
        $this->assertSame($player1->getHeadHeight(), Setting::playerHeadHeightStand());
        $boostPosition = $player2->getPositionClone()->setY($player1->getHeadHeight() + 1);
        $this->assertPositionSame($boostPosition, $player2->getPositionClone());

        $player1->suicide();
        $game->tick();
        $this->assertPositionNotSame($boostPosition, $player2->getPositionClone());
        $this->assertSame($boostPosition->y - Setting::fallAmountPerTick(), $player2->getPositionClone()->y);
        for ($i = 1; $i <= 2 * Setting::tickCountJump(); $i++) {
            $game->tick();
        }
        $this->assertPositionSame($boostPosition->setY(0), $player2->getPositionClone());
        $this->assertSame(1, $game->getRoundNumber());
    }

    public function testPlayerCanMoveAfterStandingOnTopOfOtherPlayer(): void
    {
        $player2 = new Player(2, Color::GREEN, false);
        $game = $this->createTestGameNoPause(20);
        $game->addPlayer($player2);
        $player1 = $game->getPlayer(1);
        $player2->setPosition($player1->getPositionClone()->addY($player1->getHeadHeight() + 10));

        $game->start();
        $p2pos = $game->getPlayer(2)->getPositionClone();
        $this->assertPositionSame(new Point(0, $player1->getHeadHeight() + 1, 0), $p2pos);
        $player2->moveRight();
        $game->tick();
        $this->assertPositionNotSame($p2pos, $game->getPlayer(2)->getPositionClone());
        $this->assertTrue($player2->canJump());
    }

    public function testPlayerCannotJumpWhenOtherPlayerStandingOnThem(): void
    {
        $player2 = new Player(2, Color::GREEN, false);
        $game = $this->createTestGameNoPause(20);
        $game->addPlayer($player2);
        $player1 = $game->getPlayer(1);
        $player2->setPosition($player1->getPositionClone()->addY($player1->getHeadHeight() + 4));
        $player1->jump();
        $this->assertTrue($player1->isJumping());
        $game->tick();
        $this->assertFalse($player1->isJumping());

        $game->start();
        $p2pos = $game->getPlayer(2)->getPositionClone();
        $this->assertPositionSame(new Point(0, $player1->getHeadHeight() + 1, 0), $p2pos);
        $player1->jump();
        $game->tick();
        $this->assertSame(0, $player1->getPositionClone()->y);
        $this->assertPositionSame($p2pos, $game->getPlayer(2)->getPositionClone());
        $this->assertTrue($player2->canJump());
        $this->assertTrue($player1->canJump());
        $this->assertFalse($player1->isJumping());
    }

    public function testPunishTripleBoost(): void
    {
        $game = $this->createTestGameNoPause(20);
        $p1 = $game->getPlayer(1);
        $p2 = new Player(2, Color::GREEN, false);
        $p3 = new Player(3, Color::ORANGE, false);
        $game->addPlayer($p2);
        $game->addPlayer($p3);
        $p1->setPosition(new Point(500, 0, 500));
        $p2->setPosition($p1->getPositionClone()->addY($p1->getHeadHeight() + 2));
        $p3->setPosition($p2->getPositionClone()->addY($p1->getHeadHeight() + 2));

        $game->start();
        $this->assertFalse($p1->isAlive());
    }

}
