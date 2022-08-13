<?php

namespace Test\Movement;

use cs\Core\Box;
use cs\Core\Floor;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Util;
use cs\Core\Wall;
use Test\BaseTestCase;

class WorldCollisionTest extends BaseTestCase
{

    public function testPlayerCollisionWithWall(): void
    {
        $game = $this->createOneRoundGame(3);
        $wall = new Wall(new Point(0, 0, 2 * Player::speedMove));
        $game->getWorld()->addWall($wall);
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertPlayerPosition($game, new Point(0, 0, $wall->getBase() - 1));
    }

    public function testPlayerCollisionWithBox(): void
    {
        $game = $this->createOneRoundGame(3);
        $game->getPlayer(1)->playerBoundingRadius = Player::playerBoundingRadius;
        $game->getWorld()->addBox(new Box((new Point())->setZ(Player::playerBoundingRadius + 1), 10 * Player::speedMove, Player::headHeightStand, 1));
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->moveForward());
        $game->start();
        $this->assertPlayerPosition($game, new Point());
    }

    public function testPlayerCollisionWithBoxAngle(): void
    {
        $game = $this->createOneRoundGame(3);
        $game->getPlayer(1)->playerBoundingRadius = Player::playerBoundingRadius;
        $game->getWorld()->addBox(new Box((new Point())->setZ(Player::playerBoundingRadius + 1), 10 * Player::speedMove, Player::headHeightStand, 1));
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->getSight()->lookHorizontal(45);
            $state->getPlayer(1)->moveForward();
        });
        $game->start();
        $this->assertSame(0, $game->getPlayer(1)->getPositionImmutable()->z);
    }

    public function testPlayerCollisionWithBoxAngle2(): void
    {
        $game = $this->createOneRoundGame(3);
        $game->getPlayer(1)->playerBoundingRadius = Player::playerBoundingRadius;
        $game->getWorld()->addBox(new Box((new Point())->setZ(Player::playerBoundingRadius + 1), 10 * Player::speedMove, Player::headHeightStand, 1));
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->moveForward();
            $state->getPlayer(1)->moveRight();
        });
        $game->start();
        $this->assertSame(0, $game->getPlayer(1)->getPositionImmutable()->z);
    }

    public function testPlayerDieOnFallDamage(): void
    {
        $playerCommands = [
            fn(Player $p) => $p->setPosition(new Point(0, Player::headHeightStand * 6, 0)),
            $this->waitXTicks(Player::speedFall * 6),
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
                $this->assertSame(1 + Player::speedMove, $p->getPositionImmutable()->getZ());
            },
            fn(Player $p) => $p->moveLeft(),
            fn(Player $p) => $p->moveLeft(),
            $this->endGame(),
        ];

        $game = $this->createGame();
        $game->getWorld()->addWall($wall);
        $this->playPlayer($game, $playerCommands);
        $this->assertPlayerPosition($game, new Point(0, 0, Player::speedMove + 1));
    }

    public function testPlayerCollisionWithWallWalkBypassRound(): void
    {
        $walls = [
            new Wall(new Point(1 * Player::speedMove, 0, 1 * Player::speedMove - 1), false, Player::speedMove),
            new Wall(new Point(1 * Player::speedMove, 0, 1 * Player::speedMove), true, Player::speedMove),
        ];
        $playerCommands = [
            fn(Player $p) => $p->moveForward(),
            function (Player $p) {
                $this->assertPositionSame(new Point(0, 0, Player::speedMove), $p->getPositionImmutable());
            },
            fn(Player $p) => $p->moveRight(),
            fn(Player $p) => $p->moveRight(),
            function (Player $p) {
                $this->assertPositionSame(new Point(1 * Player::speedMove - 1, 0, 1 * Player::speedMove), $p->getPositionImmutable());
            },
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveRight(),
            fn(Player $p) => $p->moveBackward(),
            fn(Player $p) => $p->moveBackward(),
            function (Player $p) {
                $this->assertSame(Player::speedMove + 1, $p->getPositionImmutable()->z);
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
        $this->assertPlayerPosition($game, new Point(3 * Player::speedMove - 1, 0, 0));
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
        $spawnPosition = new Point(0, Player::speedFall * 2, 0);
        $playerCommands = [
            Util::millisecondsToFrames(Player::speedFall),
            $this->endGame(),
        ];

        $game = $this->createGame();
        $game->getWorld()->addFloor(new Floor(new Point(0, $floorYPos, 0)));
        $game->getPlayer(1)->setPosition($spawnPosition);

        $this->playPlayer($game, $playerCommands);
        $this->assertPositionSame(new Point(0, $floorYPos, 0), $game->getPlayer(1)->getPositionImmutable());
    }

    public function testGravityFloorCatchThick(): void
    {
        $floorYPos = 2;
        $spawnPosition = new Point(5, Player::speedFall * 2, 10);
        $playerCommands = [
            Util::millisecondsToFrames(Player::speedFall),
            $this->endGame(),
        ];

        $game = $this->createGame();
        $game->getWorld()->addFloor(new Floor(new Point(0, $floorYPos, 0), 20, 20));
        $game->getPlayer(1)->setPosition($spawnPosition);

        $this->playPlayer($game, $playerCommands);
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
            Player::tickCountJump * 2,
            function (Player $p): void {
                $this->assertTrue($p->canJump());
            },
        ];
        $game = $this->simulateGame($playerCommands);
        $this->assertPositionSame(new Point(), $game->getPlayer(1)->getPositionImmutable());
    }

    public function testPlayerJumpCeiling(): void
    {
        $ceiling = new Floor(new Point(0, Player::speedJump * (Player::tickCountJump - 2), 0));
        $game = $this->createOneRoundGame(Player::tickCountJump);
        $game->getWorld()->addFloor($ceiling);
        $game->getPlayer(1)->jump();
        $game->onTick(fn(GameState $state) => $this->assertLessThan($ceiling->getY(), $state->getPlayer(1)->getPositionImmutable()->y));
        $game->start();
    }

    public function testJumpCourse(): void
    {
        $playerCommands = [
            function (Player $p): void {
                $p->moveForward();
                $p->jump();
            },
            Player::tickCountJump * 3,
            function (Player $p): void {
                $p->jump();
                $p->moveForward();
            },
            Player::tickCountJump * 3,
            function (Player $p): void {
                $p->moveForward();
                $p->jump();
            },
            Player::tickCountJump * 3,
            function (Player $p): void {
                $p->jump();
                $p->moveForward();
            },
            Player::tickCountJump * 3,
            $this->endGame(),
        ];

        $steps = 4;
        $game = $this->createGame();
        for ($i = 1; $i <= $steps; $i++) {
            $game->getWorld()->addFloor(new Floor(new Point(0, $i, $i * Player::speedMove)));
        }
        $this->playPlayer($game, $playerCommands);
        $this->assertPositionSame(new Point(0, $steps, $steps * Player::speedMove), $game->getPlayer(1)->getPositionImmutable());
    }


}
