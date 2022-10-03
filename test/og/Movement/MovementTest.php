<?php

namespace Test\Movement;

use cs\Core\Setting;
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
        $this->assertGreaterThan(0, Setting::moveDistancePerTick());
        $this->assertPlayerPosition($game, new Point(0, 0, Setting::moveDistancePerTick()));
    }

    public function testPlayerStopOnWall(): void
    {
        $wall = new Wall(new Point(0, 0, Setting::moveDistancePerTick() - 1), true);
        $game = $this->createOneRoundGame();
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->getWorld()->addWall($wall);
        $game->start();
        $this->assertGreaterThan(0, $wall->getBase());
        $this->assertPlayerPosition($game, new Point(0, 0, $wall->getBase() - 1));
    }

    public function testPlayerStopOnWallBoundingRadius(): void
    {
        $game = $this->createTestGame(2);
        $boundingRadius = $game->getPlayer(1)->getBoundingRadius();
        $wall = new Wall(new Point(0, 0, 2 * Setting::moveDistancePerTick() - $boundingRadius), true);
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->getWorld()->addWall($wall);
        $game->start();
        $this->assertSame($boundingRadius, $game->getPlayer(1)->getBoundingRadius());
        $this->assertGreaterThan(0, $wall->getBase());
        $this->assertPlayerPosition($game, new Point(0, 0, $wall->getBase() - $boundingRadius - 1));
    }

    public function testPlayerIncrementYBySteppingOnSmallWall(): void
    {
        $wall = new Wall(new Point(0, 0, (int)ceil(Setting::moveDistancePerTick() / 3)), true, 1, Setting::playerObstacleOvercomeHeight());
        $game = $this->createOneRoundGame();
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->getWorld()->addWall($wall);
        $game->start();
        $this->assertGreaterThan(0, $wall->getBase());
        $this->assertPlayerPosition($game, new Point(0, 0, $wall->getBase() - 1));

        $game->getPlayer(1)->setPosition(new Point());
        $game->getWorld()->addFloor(new Floor(new Point(0, $wall->getCeiling(), $wall->getOther()), 0, Setting::moveDistancePerTick()));
        $game->start();
        $this->assertPlayerPosition($game, new Point(0, $wall->getCeiling(), Setting::moveDistancePerTick()));
    }

    public function testPlayerSlowMovementWhenHaveGun(): void
    {
        $game = $this->createOneRoundGame(1, [GameProperty::START_MONEY => 8123]);
        $this->assertTrue($game->getPlayer(1)->buyItem(BuyMenuItem::RIFLE_AK));
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertGreaterThan(0, $game->getPlayer(1)->getPositionImmutable()->z);
        $this->assertLessThan(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionImmutable()->z);
    }

    public function testPlayerSlowMovementWhenFlying(): void
    {
        $game = $this->createOneRoundGame();
        $game->getPlayer(1)->setPosition(new Point(0, Setting::playerHeadHeightStand(), 0));
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertGreaterThan(0, $game->getPlayer(1)->getPositionImmutable()->y);
        $this->assertGreaterThan(0, $game->getPlayer(1)->getPositionImmutable()->z);
        $this->assertLessThan(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionImmutable()->z);
    }

    public function testPlayerSlowMovementAndLowerHeadWhenCrouching(): void
    {
        $headHeight = Setting::playerHeadHeightStand();
        $game = $this->createTestGame(Setting::tickCountCrouch());
        $game->getPlayer(1)->crouch();
        $game->getPlayer(1)->moveForward();

        $game->onAfterTick(function (GameState $state) use (&$headHeight) {
            $this->assertLessThanOrEqual($headHeight, $state->getPlayer(1)->getHeadHeight());
            $headHeight = $state->getPlayer(1)->getHeadHeight();
            $this->assertLessThan(Setting::playerHeadHeightStand(), $headHeight);
        });
        $game->start();
        $this->assertLessThan(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionImmutable()->z);
        $this->assertSame(Setting::moveDistanceCrouchPerTick(), $game->getPlayer(1)->getPositionImmutable()->z);
        $this->assertSame(Setting::playerHeadHeightCrouch(), $game->getPlayer(1)->getHeadHeight());
    }

    public function testPlayerSlowMovementWhenTouchingWall(): void
    {
        $game = $this->createOneRoundGame();
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->moveForward();
            $state->getPlayer(1)->moveLeft();
        });
        $game->start();
        $this->assertSame(0, $game->getPlayer(1)->getPositionImmutable()->x);
        $this->assertGreaterThan(0, $game->getPlayer(1)->getPositionImmutable()->z);
        $this->assertLessThan(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionImmutable()->z);
    }

    public function testPlayerSlowMovementWhenTouchingWallAngle(): void
    {
        $game = $this->createOneRoundGame();
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->getSight()->lookHorizontal(-44);
            $state->getPlayer(1)->moveForward();
            $state->getPlayer(1)->moveLeft();
        });
        $game->start();
        $this->assertSame(0, $game->getPlayer(1)->getPositionImmutable()->x);
        $this->assertGreaterThan(0, $game->getPlayer(1)->getPositionImmutable()->z);
        $this->assertLessThan(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionImmutable()->z);
    }

    public function testPlayerSlowMovementWhenTouchingWallAngle1(): void
    {
        $game = $this->createOneRoundGame();
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->getSight()->lookHorizontal(91);
            $state->getPlayer(1)->moveForward();
            $state->getPlayer(1)->moveRight();
        });
        $game->start();
        $this->assertSame(0, $game->getPlayer(1)->getPositionImmutable()->z);
        $this->assertGreaterThan(0, $game->getPlayer(1)->getPositionImmutable()->x);
        $this->assertLessThan(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionImmutable()->x);
    }

    public function testPlayerMultiJump(): void
    {
        $pc = [
            fn(Player $p) => $p->jump(),
            fn(Player $p) => $p->jump(),
            fn(Player $p) => $p->jump(),
            function (Player $p) {
                $this->assertLessThan(Setting::playerHeadHeightStand(), $p->getPositionImmutable()->y);
            },
            fn(Player $p) => $p->jump(),
            $this->waitXTicks(Setting::tickCountJump() * 2),
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
        $game->setTickMax(Setting::playerHeadHeightStand());
        $game->onTick(function (GameState $state): void {
            if ($state->getTickId() === 2) {
                $state->getPlayer(1)->suicide();
            }
            $state->getPlayer(1)->moveForward();
            $state->getPlayer(1)->jump();
        });
        $game->start();
        $this->assertLessThan(Setting::playerHeadHeightStand(), $game->getPlayer(1)->getPositionImmutable()->y);
    }

    public function testPlayerDiagonalMove(): void
    {
        $game = $this->createOneRoundGame();
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->moveForward();
            $state->getPlayer(1)->moveRight();
        });
        $game->start();
        $this->assertLessThan(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionImmutable()->z);
        $this->assertLessThan(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionImmutable()->x);
    }

    public function testPlayerMouseOrthogonalMovement(): void
    {
        $origin = new Point(1234, 0, 1234);
        $playerCommands = [
            fn(Player $p) => $p->setPosition($origin),
            fn(Player $p) => $p->moveForward(),
            function (Player $p) use ($origin) {
                $this->assertSame($origin->z + Setting::moveDistancePerTick(), $p->getPositionImmutable()->z);
            },
            fn(Player $p) => $p->getSight()->lookHorizontal(90),
            fn(Player $p) => $p->moveForward(),
            function (Player $p) use ($origin) {
                $this->assertSame($origin->x + Setting::moveDistancePerTick(), $p->getPositionImmutable()->x);
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
            $this->waitXTicks(Setting::tickCountCrouch()),
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
            $this->waitXTicks(Setting::tickCountCrouch()),
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
                $this->assertPositionSame(new Point($origin->x + Setting::moveDistancePerTick(), $origin->y, $origin->z - Setting::moveDistancePerTick()), $p->getPositionImmutable());
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
