<?php

namespace Test\Movement;

use cs\Core\Floor;
use cs\Core\GameProperty;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Wall;
use cs\Enum\BuyMenuItem;
use Test\BaseTestCase;

class MovementTest extends BaseTestCase
{

    public function testPlayerMove(): void
    {
        $game = $this->createOneRoundGame();
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertGreaterThan(0, Player::speedMove);
        $this->assertPlayerPosition($game, new Point(0, 0, Player::speedMove));
    }

    public function testPlayerStopOnWall(): void
    {
        $wall = new Wall(new Point(0, 0, Player::speedMove - 1), true);
        $game = $this->createOneRoundGame();
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->getWorld()->addWall($wall);
        $game->start();
        $this->assertGreaterThan(0, $wall->getBase());
        $this->assertPlayerPosition($game, new Point(0, 0, $wall->getBase() - 1));
    }

    public function testPlayerStopOnWallBoundingRadius(): void
    {
        $boundingRadius = rand(1, 999);
        $wall = new Wall(new Point(0, 0, Player::speedMove + $boundingRadius), true);
        $game = $this->createOneRoundGame();
        $game->getPlayer(1)->playerBoundingRadius = $boundingRadius;
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->getWorld()->addWall($wall);
        $game->start();
        $this->assertSame($boundingRadius, $game->getPlayer(1)->playerBoundingRadius);
        $this->assertGreaterThan(0, $wall->getBase());
        $this->assertPlayerPosition($game, new Point(0, 0, $wall->getBase() - $boundingRadius - 1));
    }

    public function testPlayerIncrementYBySteppingOnSmallWall(): void
    {
        $wall = new Wall(new Point(0, 0, (int)ceil(Player::speedMove / 3)), true, 1, Player::obstacleOvercomeHeight);
        $game = $this->createOneRoundGame();
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->getWorld()->addWall($wall);
        $game->start();
        $this->assertGreaterThan(0, $wall->getBase());
        $this->assertPlayerPosition($game, new Point(0, 0, $wall->getBase() - 1));

        $game->getPlayer(1)->setPosition(new Point());
        $game->getWorld()->addFloor(new Floor(new Point(0, $wall->getCeiling(), $wall->getOther()), 0, Player::speedMove));
        $game->start();
        $this->assertPlayerPosition($game, new Point(0, $wall->getCeiling(), Player::speedMove));
    }

    public function testPlayerSlowMovementWhenHaveGun(): void
    {
        $game = $this->createOneRoundGame(1, [GameProperty::START_MONEY => 8123]);
        $this->assertTrue($game->getPlayer(1)->buyItem(BuyMenuItem::RIFLE_AK));
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertGreaterThan(0, $game->getPlayer(1)->getPositionImmutable()->z);
        $this->assertLessThan(Player::speedMove, $game->getPlayer(1)->getPositionImmutable()->z);
    }

    public function testPlayerSlowMovementWhenFlying(): void
    {
        $game = $this->createOneRoundGame();
        $game->getPlayer(1)->setPosition(new Point(0, 2 * Player::speedFall, 0));
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertGreaterThan(0, $game->getPlayer(1)->getPositionImmutable()->y);
        $this->assertLessThan(Player::speedMove, $game->getPlayer(1)->getPositionImmutable()->z);
    }

    public function testPlayerSlowMovementAndLowerHeadWhenCrouching(): void
    {
        $headHeight = Player::headHeightStand;
        $game = $this->createOneRoundGame(Player::tickCountCrouch);
        $game->getPlayer(1)->crouch();
        $game->getPlayer(1)->moveForward();

        $game->onTick(function (GameState $state) use (&$headHeight) {
            if ($state->getTickId() === 0) {
                return;
            }
            $this->assertLessThan($headHeight, $state->getPlayer(1)->getHeadHeight());
            $headHeight = $state->getPlayer(1)->getHeadHeight();
            $this->assertLessThan(Player::headHeightStand, $headHeight);
        });
        $game->start();
        $this->assertLessThan(Player::speedMove, $game->getPlayer(1)->getPositionImmutable()->z);
        $this->assertSame(Player::speedMoveCrouch, $game->getPlayer(1)->getPositionImmutable()->z);
        $this->assertSame(Player::headHeightCrouch, $game->getPlayer(1)->getHeadHeight());
    }

    public function TODOtestPlayerSlowMovementWhenTouchingWall(): void // TODO
    {
        $game = $this->createOneRoundGame();
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->moveForward();
            $state->getPlayer(1)->moveLeft();
        });
        $game->start();
        $this->assertGreaterThan(0, $game->getPlayer(1)->getPositionImmutable()->z);
        $this->assertLessThan(Player::speedMove, $game->getPlayer(1)->getPositionImmutable()->z);
    }

    public function testPlayerMultiJump(): void
    {
        $pc = [
            fn(Player $p) => $p->jump(),
            fn(Player $p) => $p->jump(),
            fn(Player $p) => $p->jump(),
            function (Player $p) {
                $this->assertLessThan(Player::headHeightStand, $p->getPositionImmutable()->y);
            },
            fn(Player $p) => $p->jump(),
            $this->waitXTicks(Player::tickCountJump),
        ];
        $game = $this->simulateGame($pc);
        $this->assertSame(0, $game->getPlayer(1)->getPositionImmutable()->y);
        $this->assertFalse($game->getPlayer(1)->isFlying());
        $this->assertFalse($game->getPlayer(1)->isJumping());
    }

    public function testPlayerFlyingNextRoundBugEventsReset(): void
    {
        $game = $this->createGame([
            GameProperty::MAX_ROUNDS => 2,
        ]);
        $game->setTickMax(Player::headHeightStand);
        $game->onTick(function (GameState $state): void {
            if ($state->getTickId() === 2) {
                $state->getPlayer(1)->suicide();
            }
            $state->getPlayer(1)->moveForward();
            $state->getPlayer(1)->jump();
        });
        $game->start();
        $this->assertLessThan(Player::headHeightStand, $game->getPlayer(1)->getPositionImmutable()->y);
    }

    public function testPlayerDiagonalMove(): void
    {
        $game = $this->createOneRoundGame();
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->moveForward();
            $state->getPlayer(1)->moveRight();
        });
        $game->start();
        $this->assertLessThan(Player::speedMove, $game->getPlayer(1)->getPositionImmutable()->z);
        $this->assertLessThan(Player::speedMove, $game->getPlayer(1)->getPositionImmutable()->x);
    }

    public function testPlayerMouseOrthogonalMovement(): void
    {
        $origin = new Point(1234, 0, 1234);
        $playerCommands = [
            fn(Player $p) => $p->setPosition($origin),
            fn(Player $p) => $p->moveForward(),
            function (Player $p) use ($origin) {
                $this->assertSame($origin->z + Player::speedMove, $p->getPositionImmutable()->z);
            },
            fn(Player $p) => $p->getSight()->lookHorizontal(90),
            fn(Player $p) => $p->moveForward(),
            function (Player $p) use ($origin) {
                $this->assertSame($origin->x + Player::speedMove, $p->getPositionImmutable()->x);
            },
            fn(Player $p) => $p->getSight()->lookHorizontal(180),
            fn(Player $p) => $p->moveForward(),
            function (Player $p) use ($origin) {
                $this->assertSame($origin->z, $p->getPositionImmutable()->z);
            },
            fn(Player $p) => $p->getSight()->lookHorizontal(270),
            fn(Player $p) => $p->moveForward(),
            function (Player $p) use ($origin) {
                $this->assertSame($origin->z, $p->getPositionImmutable()->z);
            },
        ];
        $game = $this->simulateGame($playerCommands);
        $this->assertPositionSame($origin, $game->getPlayer(1)->getPositionImmutable());
    }

    public function testPlayerCrouchingStanding(): void
    {
        $playerCommands = [
            function (Player $p) {
                $this->assertFalse($p->isCrouching());
            },
            fn(Player $p) => $p->crouch(),
            function (Player $p) {
                $this->assertTrue($p->isCrouching());
            },
            $this->waitXTicks(Player::tickCountCrouch),
            function (Player $p) {
                $this->assertFalse($p->isCrouching());
            },
            function (Player $p) {
                $p->stand();
                $p->stand();
            },
            function (Player $p) {
                $this->assertTrue($p->isCrouching());
            },
            $this->waitXTicks(Player::tickCountCrouch),
            function (Player $p) {
                $this->assertFalse($p->isCrouching());
            },
            $this->endGame(),
        ];

        $this->simulateGame($playerCommands);
    }

    public function testPlayerMouseOrthogonalMovement1(): void
    {
        $origin = new Point(1234, 0, 1234);
        $playerCommands = [
            fn(Player $p) => $p->setPosition($origin),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->getSight()->lookHorizontalOffset(360 + 90),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->getSight()->lookHorizontalOffset(90),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->getSight()->lookHorizontalOffset(90),
            fn(Player $p) => $p->moveForward(),
        ];
        $game = $this->simulateGame($playerCommands);
        $this->assertPositionSame($origin, $game->getPlayer(1)->getPositionImmutable());
    }

    public function testPlayerMouseOrthogonalMovement2(): void
    {
        $origin = new Point(1234, 0, 1234);
        $playerCommands = [
            fn(Player $p) => $p->setPosition($origin),
            fn(Player $p) => $p->getSight()->lookVertical(22),
            fn(Player $p) => $p->getSight()->lookHorizontal(2 * 360 + 90),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->getSight()->lookHorizontalOffset(90),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->getSight()->lookHorizontal(-90),
            function (Player $p) use ($origin) {
                $this->assertPositionSame(new Point($origin->x + Player::speedMove, $origin->y, $origin->z - Player::speedMove), $p->getPositionImmutable());
                $this->assertSame($p->getSight()->getRotationHorizontal(), 270);
            },
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->getSight()->lookHorizontalOffset(90),
            fn(Player $p) => $p->moveForward(),
        ];
        $game = $this->simulateGame($playerCommands);
        $this->assertPositionSame($origin, $game->getPlayer(1)->getPositionImmutable());
    }

    public function testPlayerMouseOrthogonalMovement3(): void
    {
        $origin = new Point(1234, 0, 1234);
        $playerCommands = [
            fn(Player $p) => $p->setPosition($origin),
            fn(Player $p) => $p->getSight()->lookVertical(22),
            fn(Player $p) => $p->getSight()->lookHorizontal(180),
            fn(Player $p) => $p->moveBackward(),
            fn(Player $p) => $p->moveLeft(),
            fn(Player $p) => $p->getSight()->lookHorizontal(360),
            fn(Player $p) => $p->moveBackward(),
            fn(Player $p) => $p->moveLeft(),
        ];
        $game = $this->simulateGame($playerCommands);
        $this->assertPositionSame($origin, $game->getPlayer(1)->getPositionImmutable());
    }

}
