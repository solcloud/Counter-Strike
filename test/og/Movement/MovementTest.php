<?php

namespace Test\Movement;

use cs\Core\Box;
use cs\Core\Floor;
use cs\Core\GameProperty;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Setting;
use cs\Core\Wall;
use cs\Enum\BuyMenuItem;
use cs\Enum\Color;
use cs\Enum\SoundType;
use cs\Event\SoundEvent;
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

    public function testPlayerStopOnWallsDiagonalSmallAngle(): void
    {
        $game = $this->createTestGame(60);
        $p = $game->getPlayer(1);
        $br = $p->getBoundingRadius();
        $p->getSight()->lookHorizontal(1);
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());

        $wallFront = new Wall(new Point(0, 0, $br + Setting::moveDistancePerTick() - 4), true, 300);
        $wallRight = new Wall(new Point($br + Setting::moveDistancePerTick(), 0, 0), false, 300);
        $game->getWorld()->addWall($wallFront);
        $game->getWorld()->addWall($wallRight);

        $game->start();
        $this->assertPlayerPosition($game, new Point($wallRight->getBase() - $br - 1, 0, $wallFront->getBase() - $br - 1));
    }

    public function testPlayerStopOnSmallWallsDiagonalNoFloorToStep(): void
    {
        $game = $this->createTestGame(60);
        $p = $game->getPlayer(1);
        $br = $p->getBoundingRadius();
        $p->getSight()->lookHorizontal(1);
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());

        $wallFront = new Wall(new Point(0, 0, $br + Setting::moveDistancePerTick() - 4), true, 300);
        $wallRight = new Wall(new Point($br + Setting::moveDistancePerTick(), 0, 0), false, 300, 2);
        $game->getWorld()->addWall($wallFront);
        $game->getWorld()->addWall($wallRight);

        $game->start();
        $this->assertPlayerPosition($game, new Point($wallRight->getBase() - $br - 1, 0, $wallFront->getBase() - $br - 1));
    }

    public function testPlayerStopOnOtherPlayerDiagonalSmallAngle(): void
    {
        $game = $this->createTestGame(60);
        $p = $game->getPlayer(1);
        $br = $p->getBoundingRadius();
        $p->getSight()->lookHorizontal(1);
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());

        $p2 = new Player(2, Color::BLUE, false);
        $game->addPlayer($p2);
        $p2->setPosition(new Point(3 * $br, 0, 2 * $br - 1));
        $wallFront = new Wall(new Point(0, 0, $br + Setting::moveDistancePerTick() - 4), true, 300);
        $game->getWorld()->addWall($wallFront);

        $game->start();
        $this->assertPlayerPosition($game, new Point(54, 0, 45));
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

    public function testPlayerStopOnBoxBoundingRadius(): void
    {
        $game = $this->createTestGame(2);
        $boundingRadius = $game->getPlayer(1)->getBoundingRadius();
        $box = new Box(new Point(0, 0, 2 * Setting::moveDistancePerTick() - $boundingRadius), 1, Setting::playerObstacleOvercomeHeight() + 1, 100);
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->getWorld()->addBox($box);
        $game->getWorld()->addFloor(new Floor(new Point(0, Setting::playerObstacleOvercomeHeight(), 2 * Setting::moveDistancePerTick() - $boundingRadius)));
        $game->start();
        $this->assertSame($boundingRadius, $game->getPlayer(1)->getBoundingRadius());
        $this->assertGreaterThan(0, $box->getBase()->z);
        $this->assertPlayerPosition($game, new Point(0, 0, $box->getBase()->z - $boundingRadius - 1));
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

        $game = $this->createOneRoundGame();
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->getWorld()->addWall($wall);
        $game->getPlayer(1)->setPosition(new Point());
        $game->getWorld()->addFloor(new Floor(new Point(0, $wall->getCeiling(), $wall->getStart()->z), 1, Setting::moveDistancePerTick()));
        $game->start();
        $this->assertPlayerPosition($game, new Point(0, $wall->getCeiling(), Setting::moveDistancePerTick()));
    }

    public function testPlayerMakeStepNoise(): void
    {
        $game = $this->createOneRoundGame(2);
        $stepSound = null;
        $game->onEvents(function (array $events) use (&$stepSound): void {
            foreach ($events as $event) {
                if ($event instanceof SoundEvent && $event->type === SoundType::PLAYER_STEP) {
                    $stepSound = $event;
                    $this->assertSame(1, $event->getPlayerId());
                }
            }
        });
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());

        $game->start();
        $this->assertNotNull($stepSound);
    }

    public function testPlayerSlowMovementWhenHaveGun(): void
    {
        $game = $this->createOneRoundGame(1, [GameProperty::START_MONEY => 8123]);
        $this->assertTrue($game->getPlayer(1)->buyItem(BuyMenuItem::RIFLE_AK));
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertGreaterThan(0, $game->getPlayer(1)->getPositionClone()->z);
        $this->assertLessThan(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionClone()->z);
    }

    public function testPlayerSlowMovementWhenShot(): void
    {
        $tickMax = 5;
        $game = $this->createTestGame($tickMax);
        $p2 = new Player(2, Color::YELLOW, false);
        $game->addPlayer($p2);
        $p2->getSight()->look(180, -10);
        $p2->setPosition($p2->getPositionClone()->addZ(Setting::moveDistancePerTick() * 10));
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->moveForward();
            if ($state->getTickId() === 2) {
                $this->assertNotNull($state->getPlayer(2)->attack());
                $this->assertLessThan(100, $state->getPlayer(1)->getHealth());
                $this->assertTrue($state->getPlayer(1)->isAlive());
            }
        });
        $game->start();
        $this->assertGreaterThan(Setting::moveDistancePerTick() * 3, $game->getPlayer(1)->getPositionClone()->z);
        $this->assertLessThan(Setting::moveDistancePerTick() * $tickMax, $game->getPlayer(1)->getPositionClone()->z);
    }

    public function testPlayerConsistentMovement(): void
    {
        $start = new Point(500, 0, 500);
        $end = null;

        $game = $this->createTestGame();
        $p = $game->getPlayer(1);
        $p->setPosition($start);
        $this->playPlayer($game, [
            fn(Player $p) => $p->getInventory()->earnMoney(9000),
            fn(Player $p) => $p->getSight()->lookHorizontal(45),
            function (Player $p) {
                $p->moveForward();
                $this->assertFalse($p->isWalking());
                $this->assertTrue($p->isRunning());
            },
            fn(Player $p) => $p->moveForward(),
            function (Player $p) use (&$end) {
                $end = $p->getPositionClone();
            },
            fn(Player $p) => $p->getSight()->lookHorizontal(225),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            $this->endGame(),
        ]);

        $this->assertInstanceOf(Point::class, $end);
        $this->assertPositionSame($start, $p->getPositionClone());
        $this->assertPositionNotSame($end, $p->getPositionClone());
    }

    public function testPlayerSlowMovementWhenInScope(): void
    {
        $start = new Point(500, 0, 500);
        $end = null;

        $game = $this->createTestGame();
        $p = $game->getPlayer(1);
        $p->setPosition($start);
        $this->playPlayer($game, [
            fn(Player $p) => $p->getInventory()->earnMoney(9000),
            fn(Player $p) => $p->buyItem(BuyMenuItem::RIFLE_AWP),
            fn(Player $p) => $p->getSight()->lookHorizontal(45),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            function (Player $p) use (&$end) {
                $end = $p->getPositionClone();
                $p->attackSecondary();
            },
            fn(Player $p) => $p->getSight()->lookHorizontal(225),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            $this->endGame(),
        ]);

        $this->assertNotNull($end);
        $this->assertPositionNotSame($start, $p->getPositionClone());
        $this->assertGreaterThan($start->x, $p->getPositionClone()->x);
        $this->assertGreaterThan($start->z, $p->getPositionClone()->z);
        $this->assertPositionSame(new Point(520, 0, 520), $p->getPositionClone());
    }

    public function testPlayerSlowMovementWhenFlying(): void
    {
        $game = $this->createOneRoundGame();
        $game->getPlayer(1)->setPosition(new Point(0, Setting::playerHeadHeightStand(), 0));
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertGreaterThan(0, $game->getPlayer(1)->getPositionClone()->y);
        $this->assertGreaterThan(0, $game->getPlayer(1)->getPositionClone()->z);
        $this->assertLessThan(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionClone()->z);
    }

    public function testPlayerSlowMovementAndLowerHeadWhenCrouching(): void
    {
        $headHeight = Setting::playerHeadHeightStand();
        $game = $this->createTestGame(Setting::tickCountCrouch());
        $game->getPlayer(1)->crouch();
        $game->getPlayer(1)->speedRun();
        $game->getPlayer(1)->moveForward();

        $game->onAfterTick(function (GameState $state) use (&$headHeight) {
            $this->assertLessThanOrEqual($headHeight, $state->getPlayer(1)->getHeadHeight());
            $headHeight = $state->getPlayer(1)->getHeadHeight();
            $this->assertLessThan(Setting::playerHeadHeightStand(), $headHeight);
        });
        $game->start();
        $this->assertLessThan(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionClone()->z);
        $this->assertSame(Setting::moveDistanceCrouchPerTick(), $game->getPlayer(1)->getPositionClone()->z);
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
        $this->assertSame(0, $game->getPlayer(1)->getPositionClone()->x);
        $this->assertGreaterThan(0, $game->getPlayer(1)->getPositionClone()->z);
        $this->assertLessThan(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionClone()->z);
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
        $this->assertSame(0, $game->getPlayer(1)->getPositionClone()->x);
        $this->assertGreaterThan(0, $game->getPlayer(1)->getPositionClone()->z);
        $this->assertLessThan(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionClone()->z);
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
        $this->assertSame(0, $game->getPlayer(1)->getPositionClone()->z);
        $this->assertGreaterThan(0, $game->getPlayer(1)->getPositionClone()->x);
        $this->assertLessThan(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionClone()->x);
    }

    public function testPlayerMultiJump(): void
    {
        $game = $this->createNoPauseGame();
        $this->playPlayer($game, [
            fn(Player $p) => $this->assertFalse($p->isJumping()),
            fn(Player $p) => $this->assertTrue($p->canJump()),
            fn(Player $p) => $p->jump(),
            fn(Player $p) => $this->assertTrue($p->isJumping()),
            fn(Player $p) => $this->assertFalse($p->canJump()),
            fn(Player $p) => $p->jump(),
            fn(Player $p) => $p->jump(),
            function (Player $p) {
                $this->assertLessThan(Setting::playerHeadHeightStand(), $p->getPositionClone()->y);
            },
            fn(Player $p) => $p->jump(),
            $this->waitXTicks(Setting::tickCountJump() * 2),
            $this->endGame(),
        ]);
        $this->assertSame(0, $game->getPlayer(1)->getPositionClone()->y);
        $this->assertFalse($game->getPlayer(1)->isFlying());
        $this->assertFalse($game->getPlayer(1)->isJumping());
    }

    public function testPlayerStopWhenRotatingFastWhileJumping(): void
    {
        $game = $this->simulateGame([
            function (Player $p) {
                $p->getSight()->lookHorizontal(0);
                $p->moveForward();
                $p->jump();
            },
            function (Player $p) {
                $p->moveForward();
                $p->getSight()->lookHorizontal(161);
            },
            function (Player $p) {
                $p->moveForward();
                $p->getSight()->lookHorizontal(0);
            },
            function (Player $p) {
                $p->moveForward();
            },
        ]);
        $this->assertSame(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionClone()->z);
    }

    public function testPlayerCornerFloorCatch(): void
    {
        $game = $this->createTestGameNoPause(20);
        $p = $game->getPlayer(1);
        $p->setPosition(new Point(100, 700, 100));
        $radius = $p->getBoundingRadius();
        $this->assertGreaterThan(0, $radius);
        $game->getWorld()->addFloor(new Floor(new Point(100 + $radius, 100, 100 + $radius), 1, 1));
        $game->start();

        $this->assertNotSame(0, $p->getPositionClone()->y);
        $this->assertSame(100, $p->getPositionClone()->y);
        $this->assertTrue($p->isAlive());
        $this->assertLessThan(100, $p->getHealth());
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
        $this->assertLessThan(Setting::playerHeadHeightStand(), $game->getPlayer(1)->getPositionClone()->y);
    }

    public function testPlayerVelocity(): void
    {
        $maxTick = 4;
        $constantDistance = $maxTick * Setting::moveDistancePerTick();
        $game = $this->createTestGame($maxTick);
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertSame($constantDistance, $game->getPlayer(1)->getPositionClone()->z);

        $game = $this->createTestGame($maxTick);
        $game->getPlayer(1)->setVelocity(10);
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertGreaterThan(1, $game->getPlayer(1)->getPositionClone()->z);
        $this->assertLessThan($constantDistance, $game->getPlayer(1)->getPositionClone()->z);
    }

    public function testPlayerDiagonalMove(): void
    {
        $game = $this->createOneRoundGame();
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->moveForward();
            $state->getPlayer(1)->moveRight();
        });
        $game->start();
        $this->assertLessThan(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionClone()->z);
        $this->assertLessThan(Setting::moveDistancePerTick(), $game->getPlayer(1)->getPositionClone()->x);
    }

    public function testPlayerMouseOrthogonalMovement(): void
    {
        $origin = new Point(1234, 0, 1234);
        $playerCommands = [
            fn(Player $p) => $p->setPosition($origin),
            fn(Player $p) => $p->moveForward(),
            function (Player $p) use ($origin) {
                $this->assertSame($origin->z + Setting::moveDistancePerTick(), $p->getPositionClone()->z);
            },
            fn(Player $p) => $p->getSight()->lookHorizontal(90),
            fn(Player $p) => $p->moveForward(),
            function (Player $p) use ($origin) {
                $this->assertSame($origin->x + Setting::moveDistancePerTick(), $p->getPositionClone()->x);
            },
            fn(Player $p) => $p->getSight()->lookHorizontal(180),
            fn(Player $p) => $p->moveForward(),
            function (Player $p) use ($origin) {
                $this->assertSame($origin->z, $p->getPositionClone()->z);
            },
            fn(Player $p) => $p->getSight()->lookHorizontal(270),
            fn(Player $p) => $p->moveForward(),
            function (Player $p) use ($origin) {
                $this->assertSame($origin->z, $p->getPositionClone()->z);
            },
        ];
        $game = $this->simulateGame($playerCommands);
        $this->assertPositionSame($origin, $game->getPlayer(1)->getPositionClone());
    }

    public function testPlayerCrouchingStanding(): void
    {
        $this->simulateGame([
            fn(Player $p) => $this->assertFalse($p->isCrouching()),
            fn(Player $p) => $p->crouch(),
            fn(Player $p) => $this->assertTrue($p->isCrouching()),
            $this->waitXTicks(Setting::tickCountCrouch()),
            fn(Player $p) => $this->assertTrue($p->isCrouching()),
            function (Player $p) {
                $p->stand();
                $p->stand();
            },
            fn(Player $p) => $this->assertTrue($p->isCrouching()),
            $this->waitXTicks(Setting::tickCountCrouch()),
            fn(Player $p) => $this->assertFalse($p->isCrouching()),
            $this->endGame(),
        ]);
    }

    public function testPlayerCrouchingAndCannotStandWhenOtherPlayerChillingOnTop(): void
    {
        $game = $this->createNoPauseGame();
        $game->addPlayer(new Player(2, Color::GREEN, false));
        $p2 = $game->getPlayer(2);
        $p2->setHeadHeight(2); // for continue/break infection detection in isCollisionWithOtherPlayers

        $this->playPlayer($game, [
            fn(Player $p) => $this->assertSame(Setting::playerHeadHeightStand(), $p->getHeadHeight()),
            fn(Player $p) => $p->crouch(),
            $this->waitXTicks(Setting::tickCountCrouch()),
            fn(Player $p) => $this->assertSame(Setting::playerHeadHeightCrouch(), $p->getHeadHeight()),
            function (Player $p) use ($p2) {
                $p2->setPosition($p->getPositionClone()->addY($p->getHeadHeight() + Setting::crouchDistancePerTick() * 2));
                $p->stand();
            },
            fn(Player $p) => $this->assertGreaterThan(Setting::playerHeadHeightCrouch(), $p->getHeadHeight()),
            $this->waitXTicks(Setting::tickCountCrouch()),
            $this->endGame(),
        ]);

        $this->assertSame(Setting::playerHeadHeightCrouch(), $game->getPlayer(1)->getHeadHeight());
        $this->assertSame(Setting::playerHeadHeightCrouch() + 1, $game->getPlayer(2)->getPositionClone()->y);
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
        $this->assertPositionSame($origin, $game->getPlayer(1)->getPositionClone());
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
                $this->assertPositionSame(new Point($origin->x + Setting::moveDistancePerTick(), $origin->y, $origin->z - Setting::moveDistancePerTick()), $p->getPositionClone());
                $this->assertSame($p->getSight()->getRotationHorizontal(), 270.0);
            },
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->getSight()->lookHorizontalOffset(90),
            fn(Player $p) => $p->moveForward(),
        ];
        $game = $this->simulateGame($playerCommands);
        $this->assertPositionSame($origin, $game->getPlayer(1)->getPositionClone());
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
        $this->assertPositionSame($origin, $game->getPlayer(1)->getPositionClone());
    }

}
