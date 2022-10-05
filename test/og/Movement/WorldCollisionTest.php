<?php

namespace Test\Movement;

use cs\Core\Box;
use cs\Core\Floor;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Setting;
use cs\Core\Wall;
use cs\Enum\Color;
use Test\BaseTestCase;

class WorldCollisionTest extends BaseTestCase
{

    public function testPlayerCollisionWithWall(): void
    {
        $game = $this->createOneRoundGame(3);
        $wall = new Wall(new Point(0, 0, 2 * Setting::moveDistancePerTick()));
        $game->getWorld()->addWall($wall);
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertPlayerPosition($game, new Point(0, 0, $wall->getBase() - 1));
    }

    public function testPlayerCollisionWithBox(): void
    {
        $game = $this->createTestGame(3);
        $box = new Box((new Point())->setZ(Setting::playerBoundingRadius() + 1), 10 * Setting::moveDistancePerTick(), Setting::playerHeadHeightStand(), 1);
        $game->getWorld()->addBox($box);
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertPlayerPosition($game, new Point());
    }

    public function testPlayerCollisionWithBoxAngle(): void
    {
        $game = $this->createTestGame(3);
        $game->getWorld()->addBox(new Box((new Point())->setZ(Setting::playerBoundingRadius() + 1), 10 * Setting::moveDistancePerTick(), Setting::playerHeadHeightStand(), 1));
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->getSight()->lookHorizontal(45);
            $state->getPlayer(1)->moveForward();
        });
        $game->start();
        $this->assertGreaterThan(0, $game->getPlayer(1)->getPositionImmutable()->x);
        $this->assertSame(0, $game->getPlayer(1)->getPositionImmutable()->z);
    }

    public function testPlayerCollisionWithBoxDoubleMovement(): void
    {
        $game = $this->createTestGame(3);
        $game->getWorld()->addBox(new Box((new Point())->setZ(Setting::playerBoundingRadius() + 1), 10 * Setting::moveDistancePerTick(), Setting::playerHeadHeightStand(), 1));
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->moveForward();
            $state->getPlayer(1)->moveLeft();
        });
        $game->start();
        $this->assertSame(0, $game->getPlayer(1)->getPositionImmutable()->z);
        $this->assertSame(0, $game->getPlayer(1)->getPositionImmutable()->x);
    }

    public function testPlayerCollisionWithBoxDoubleMovement2(): void
    {
        $ticks = 3;
        $game = $this->createTestGame($ticks);
        $box = new Box((new Point())->setZ(Setting::playerBoundingRadius() + 10), 10 * Setting::moveDistancePerTick(), Setting::playerHeadHeightStand(), 1);
        $game->getWorld()->addBox($box);
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->getSight()->lookHorizontal(1);
            $state->getPlayer(1)->moveForward();
            $state->getPlayer(1)->moveRight();
        });
        $game->start();
        $this->assertSame(0, $game->getPlayer(1)->getPositionImmutable()->y);
        $this->assertSame($box->getBase()->z - Setting::playerBoundingRadius() - 1, $game->getPlayer(1)->getPositionImmutable()->z);
        $this->assertGreaterThan($ticks, $game->getPlayer(1)->getPositionImmutable()->x);
    }

    public function testPlayerCollisionWithBoxAngle2(): void
    {
        $game = $this->createTestGame(3);
        $game->getWorld()->addBox(new Box((new Point())->setZ(Setting::playerBoundingRadius() + 1), 10 * Setting::moveDistancePerTick(), Setting::playerHeadHeightStand(), 1));
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->moveForward();
            $state->getPlayer(1)->moveRight();
        });
        $game->start();
        $this->assertSame(0, $game->getPlayer(1)->getPositionImmutable()->z);
        $this->assertGreaterThan(0, $game->getPlayer(1)->getPositionImmutable()->x);
    }

    public function testPlayerDieOnFallDamage(): void
    {
        $yStart = Setting::playerHeadHeightStand() * 6;
        $playerCommands = [
            fn(Player $p) => $p->setPosition(new Point(0, $yStart, 0)),
            $this->waitXTicks((int)ceil($yStart / Setting::fallAmountPerTick())),
            $this->endGame(),
        ];

        $game = $this->simulateGame($playerCommands);
        $this->assertFalse($game->getPlayer(1)->isAlive());
    }

    public function testPlayerCollisionWithWallWalkBypass(): void
    {
        $wall = new Wall(new Point(0, 0, 2), true);
        $playerCommands = [
            fn(Player $p) => $p->moveForward(),
            function (Player $p) {
                $this->assertSame(1, $p->getPositionImmutable()->getZ());
            },
            fn(Player $p) => $p->moveRight(),
            fn(Player $p) => $p->moveForward(),
            function (Player $p) {
                $this->assertSame(1 + Setting::moveDistancePerTick(), $p->getPositionImmutable()->getZ());
            },
            fn(Player $p) => $p->moveLeft(),
            fn(Player $p) => $p->moveLeft(),
            $this->endGame(),
        ];

        $game = $this->createGame();
        $game->getWorld()->addWall($wall);
        $this->playPlayer($game, $playerCommands);
        $this->assertPlayerPosition($game, new Point(0, 0, Setting::moveDistancePerTick() + 1));
    }

    public function testPlayerCollisionWithWallWalkBypassRound(): void
    {
        $walls = [
            new Wall(new Point(1 * Setting::moveDistancePerTick(), 0, 1 * Setting::moveDistancePerTick() - 1), false, Setting::moveDistancePerTick()),
            new Wall(new Point(1 * Setting::moveDistancePerTick(), 0, 1 * Setting::moveDistancePerTick()), true, Setting::moveDistancePerTick()),
        ];
        $playerCommands = [
            fn(Player $p) => $p->moveForward(),
            function (Player $p) {
                $this->assertPositionSame(new Point(0, 0, Setting::moveDistancePerTick()), $p->getPositionImmutable());
            },
            fn(Player $p) => $p->moveRight(),
            fn(Player $p) => $p->moveRight(),
            function (Player $p) {
                $this->assertPositionSame(new Point(1 * Setting::moveDistancePerTick() - 1, 0, 1 * Setting::moveDistancePerTick()), $p->getPositionImmutable());
            },
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveRight(),
            fn(Player $p) => $p->moveBackward(),
            fn(Player $p) => $p->moveBackward(),
            function (Player $p) {
                $this->assertSame(Setting::moveDistancePerTick() + 1, $p->getPositionImmutable()->z);
            },
            fn(Player $p) => $p->moveRight(),
            fn(Player $p) => $p->moveBackward(),
            fn(Player $p) => $p->moveBackward(),
            $this->endGame(),
        ];

        $game = $this->createGame();
        foreach ($walls as $wall) {
            $game->getWorld()->addWall($wall);
        }
        $this->playPlayer($game, $playerCommands);
        $this->assertPlayerPosition($game, new Point(3 * Setting::moveDistancePerTick() - 1, 0, 0));
    }

    public function testPlayerCollisionWithOtherPlayerRadius(): void
    {
        $game = $this->createTestGame(10);
        $player2 = new Player(2, Color::GREEN, false);
        $game->addPlayer($player2);
        $player2Position = new Point(0, 0, 2 * $player2->getBoundingRadius() + 10);
        $player2->setPosition($player2Position);

        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertPositionSame($player2->getPositionImmutable(), $player2Position);
        $this->assertSame($player2Position->z - 2 * $player2->getBoundingRadius() - 1, $game->getPlayer(1)->getPositionImmutable()->z);
    }

    public function testGravity(): void
    {
        $ticks = 4;
        $startY = 20;
        $spawnPosition = new Point(0, $startY, 0);
        $game = $this->createOneRoundGame($ticks);
        $game->getPlayer(1)->setPosition($spawnPosition);
        $this->playPlayer($game, [$ticks]);
        $this->assertLessThan($startY, $game->getPlayer(1)->getPositionImmutable()->getY());
    }

    public function testGravityFloorCatch(): void
    {
        $floorYPos = 2;
        $spawnPosition = new Point(0, $floorYPos + 10, 0);

        $game = $this->createTestGame(4);
        $game->getWorld()->addFloor(new Floor(new Point(0, $floorYPos, 0)));
        $game->getPlayer(1)->setPosition($spawnPosition);

        $game->start();
        $this->assertPositionSame(new Point(0, $floorYPos, 0), $game->getPlayer(1)->getPositionImmutable());
    }

    public function testGravityFloorCatchThick(): void
    {
        $floorYPos = 2;
        $spawnPosition = new Point(0, $floorYPos + 10, 0);

        $game = $this->createTestGame(4);
        $game->getWorld()->addFloor(new Floor(new Point(0, $floorYPos, 0), 20, 20));
        $game->getPlayer(1)->setPosition($spawnPosition);

        $game->start();
        $this->assertPositionSame(new Point($spawnPosition->x, $floorYPos, $spawnPosition->z), $game->getPlayer(1)->getPositionImmutable());
    }

    public function testPlayerJump(): void
    {
        $playerCommands = [
            function (Player $p): void {
                $this->assertTrue($p->canJump());
                $this->assertFalse($p->isJumping());
                $this->assertSame(0, $p->getPositionImmutable()->y);
            },
            fn(Player $p) => $p->jump(),
            function (Player $p): void {
                $this->assertTrue($p->isJumping());
                $this->assertFalse($p->canJump());
                $this->assertGreaterThan(0, $p->getPositionImmutable()->y);
            },
            $this->waitXTicks(Setting::tickCountJump() * 2),
            function (Player $p): void {
                $this->assertTrue($p->canJump());
            },
        ];
        $game = $this->simulateGame($playerCommands);
        $this->assertPositionSame(new Point(), $game->getPlayer(1)->getPositionImmutable());
    }

    public function testPlayerJumpCeiling(): void
    {
        $ceiling = new Floor(new Point(0, Setting::playerJumpHeight() / 2, 0));
        $game = $this->createOneRoundGame(Setting::tickCountJump());
        $game->getWorld()->addFloor($ceiling);
        $game->getPlayer(1)->jump();
        $game->onTick(function (GameState $state) use ($ceiling): void {
            $this->assertTrue($state->getPlayer(1)->isJumping());
            $this->assertTrue($state->getPlayer(1)->isFlying());
            if ($state->getTickId() > 0) {
                $this->assertGreaterThan(0, $state->getPlayer(1)->getPositionImmutable()->y);
            }
            $this->assertLessThan($ceiling->getY(), $state->getPlayer(1)->getPositionImmutable()->y);
        });
        $game->start();
    }

    public function testJumpSpeed(): void
    {
        $game = $this->createTestGame(2);
        $game->onTick(function (GameState $state): void {
            $state->getPlayer(1)->moveRight();
            $state->getPlayer(1)->jump();
        });
        $game->start();
        $this->assertSame(1, $game->getTickId());
        $this->assertPlayerPosition($game, new Point(2 * Setting::moveDistancePerTick(), 2 * Setting::jumpDistancePerTick(), 0));
    }

    public function testCanJumpOnBox(): void
    {
        $tickCount = Setting::tickCountJump() * 2 + 4;
        $game = $this->createOneRoundGame($tickCount);
        $game->onTick(function (GameState $state): void {
            if ($state->getTickId() === 0) {
                $state->getPlayer(1)->jump();
            }
            $state->getPlayer(1)->moveRight();
        });
        $box = new Box(new Point(Setting::moveDistancePerTick() / 2, 0, 0), $tickCount * Setting::moveDistancePerTick(), Setting::playerHeadHeightCrouch(), 1);
        $game->getWorld()->addBox($box);
        $game->start();
        $this->assertGreaterThan(0, $box->heightY);
        $this->assertFalse($game->getPlayer(1)->isFlying());
        $this->assertSame($box->heightY, $game->getPlayer(1)->getPositionImmutable()->y);
        $this->assertLessThan(Setting::moveDistancePerTick() * $tickCount, $game->getPlayer(1)->getPositionImmutable()->x);
        $this->assertGreaterThan(Setting::moveDistancePerTick() * $tickCount / 2, $game->getPlayer(1)->getPositionImmutable()->x);
    }

    public function testCanJumpOnBoxBoundingRadius(): void
    {
        $tickCount = Setting::tickCountJump() * 2 + 4;
        $game = $this->createTestGame($tickCount);
        $game->onTick(function (GameState $state): void {
            if ($state->getTickId() === 1) {
                $state->getPlayer(1)->jump();
            }
            $state->getPlayer(1)->moveRight();
        });
        $box = new Box(new Point((int)floor(Setting::playerBoundingRadius() * 2.5), 0, 0), $tickCount * Setting::moveDistancePerTick(), Setting::playerHeadHeightCrouch(), 1);
        $game->getWorld()->addBox($box);
        $game->start();
        $this->assertGreaterThan(0, $box->heightY);
        $this->assertFalse($game->getPlayer(1)->isFlying());
        $this->assertSame($box->heightY, $game->getPlayer(1)->getPositionImmutable()->y);
        $this->assertLessThan(Setting::moveDistancePerTick() * $tickCount, $game->getPlayer(1)->getPositionImmutable()->x);
        $this->assertGreaterThan(Setting::moveDistancePerTick() * $tickCount / 2, $game->getPlayer(1)->getPositionImmutable()->x);
    }

    public function testCanJumpOverWall(): void
    {
        $tickCount = Setting::tickCountJump() * 2;
        $game = $this->createOneRoundGame($tickCount);
        $game->onTick(function (GameState $state): void {
            if ($state->getTickId() === 1) {
                $state->getPlayer(1)->jump();
            }
            if ($state->getTickId() === 1 + Setting::tickCountJump()) {
                $this->assertSame(Setting::playerJumpHeight() - 1, $state->getPlayer(1)->getPositionImmutable()->y);
            }
            $state->getPlayer(1)->moveRight();
        });
        $box = new Box(new Point(Setting::moveDistancePerTick(), 0, 0), Setting::moveDistancePerTick(), Setting::playerJumpHeight() - 1, 1);
        $game->getWorld()->addBox($box);
        $game->start();
        $this->assertGreaterThan(0, $box->heightY);
        $this->assertFalse($game->getPlayer(1)->isFlying());
        $this->assertSame(0, $game->getPlayer(1)->getPositionImmutable()->y);
        $this->assertGreaterThan($box->getBase()->x + $box->widthX, $game->getPlayer(1)->getPositionImmutable()->x);
    }

    public function testCanJumpOverWallBoundingRadius(): void
    {
        $tickCount = Setting::tickCountJump() * 2 + 2;
        $game = $this->createTestGame($tickCount);
        $game->onTick(function (GameState $state): void {
            if ($state->getTickId() === 1) {
                $state->getPlayer(1)->jump();
            }
            if ($state->getTickId() === 1 + Setting::tickCountJump()) {
                $this->assertSame(Setting::playerJumpHeight() - 1, $state->getPlayer(1)->getPositionImmutable()->y);
            }
            $state->getPlayer(1)->moveRight();
        });
        $box = new Box(new Point(Setting::moveDistancePerTick(), 0, 0), Setting::moveDistancePerTick(), Setting::playerJumpHeight() - 1, 1);
        $game->getWorld()->addBox($box);
        $game->start();
        $this->assertGreaterThan(0, $box->heightY);
        $this->assertSame(0, $game->getPlayer(1)->getPositionImmutable()->y);
        $this->assertGreaterThan($box->getBase()->x + $box->widthX, $game->getPlayer(1)->getPositionImmutable()->x);
        $this->assertFalse($game->getPlayer(1)->isFlying());
    }

    public function testJumpCourse(): void
    {
        $playerCommands = [
            function (Player $p): void {
                $p->moveForward();
                $p->jump();
            },
            $this->waitXTicks(Setting::tickCountJump() * 2),
            function (Player $p): void {
                $p->jump();
                $p->moveForward();
            },
            $this->waitXTicks(Setting::tickCountJump() * 2),
            function (Player $p): void {
                $p->moveForward();
                $p->jump();
            },
            $this->waitXTicks(Setting::tickCountJump() * 2),
            function (Player $p): void {
                $p->jump();
                $p->moveForward();
            },
            $this->waitXTicks(Setting::tickCountJump() * 2),
            $this->endGame(),
        ];

        $steps = 4;
        $game = $this->createGame();
        for ($i = 1; $i <= $steps; $i++) {
            $floor = new Floor(
                new Point(
                    0,
                    $i * Setting::jumpDistancePerTick(),
                    (int)ceil($i * Setting::moveDistancePerTick() * Setting::jumpMovementSpeedMultiplier())
                ),
                1, Setting::moveDistancePerTick()
            );
            $game->getWorld()->addFloor($floor);
        }
        $this->playPlayer($game, $playerCommands);
        $this->assertPositionSame(new Point(0, $floor->getY(), $floor->getStart()->z), $game->getPlayer(1)->getPositionImmutable());
    }


}
