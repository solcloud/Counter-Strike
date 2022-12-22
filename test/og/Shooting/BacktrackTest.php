<?php

namespace Test\Shooting;

use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Util;
use cs\Core\Wall;
use cs\Enum\Color;
use cs\Enum\GameOverReason;
use cs\Weapon\PistolGlock;
use Test\BaseTestCase;
use Test\TestGame;

class BacktrackTest extends BaseTestCase
{

    private function _setupGame1(int $backtrackTickCount): TestGame
    {
        $property = $this->createNoPauseGameProperty();
        $property->backtrack_history_tick_count = $backtrackTickCount;
        $game = $this->createTestGame(null, $property);
        $player1 = $game->getPlayer(1);
        $player1->setPosition($player1->getPositionClone()->addX($player1->getBoundingRadius()));
        $player1->getSight()->lookHorizontal(1);
        $player1->equipSecondaryWeapon();
        $game->getWorld()->addWall(
            new Wall(
                new Point($player1->getBoundingRadius(), 1, $player1->getPositionClone()->z + $player1->getBoundingRadius() + 30),
                true,
                20
            )
        );

        $player2 = new Player(2, Color::GREEN, false);
        $game->addPlayer($player2);
        $player2->setPosition($player1->getPositionClone()->addZ($player1->getBoundingRadius() + 100));

        $this->assertTrue($player1->isAlive());
        $this->assertTrue($player2->isAlive());

        return $game;
    }

    public function testNoBacktrackKill(): void
    {
        $game = $this->_setupGame1(0);
        $i = 0;
        $result = null;
        $game->onTick(function (GameState $state) use ($game, &$i, &$result) {
            if ($state->getTickId() <= Util::millisecondsToFrames(PistolGlock::equipReadyTimeMs)) {
                return;
            }

            if ($i === 0) {
                $result = $state->getPlayer(1)->attack();
                $this->assertNotNull($result);

                $this->assertTrue($result->somePlayersWasHit());
                $this->assertFalse($state->getPlayer(2)->isAlive());
                $game->quit(GameOverReason::TIE);
            }
        });
        $game->start();
        $this->assertNotNull($result);
    }

    public function test1BacktrackDisable(): void
    {
        $game = $this->_setupGame1(0);
        $i = 0;
        $result = null;
        $game->onTick(function (GameState $state) use ($game, &$i, &$result) {
            if ($state->getTickId() <= Util::millisecondsToFrames(PistolGlock::equipReadyTimeMs)) {
                return;
            }

            $state->getPlayer(2)->moveRight();
            if ($i === 1) {
                $result = $state->getPlayer(1)->attack();
                $this->assertNotNull($result);

                $this->assertFalse($result->somePlayersWasHit());
                $this->assertTrue($state->getPlayer(2)->isAlive());
                $game->quit(GameOverReason::TIE);
            }
            $i++;
        });
        $game->start();
        $this->assertNotNull($result);
    }

    public function test1BacktrackEnableOneTick(): void
    {
        $game = $this->_setupGame1(1);
        $i = 0;
        $result = null;
        $game->onTick(function (GameState $state) use ($game, &$i, &$result) {
            if ($state->getTickId() <= Util::millisecondsToFrames(PistolGlock::equipReadyTimeMs)) {
                return;
            }

            $state->getPlayer(2)->moveRight();
            if ($i === 1) {
                $result = $state->getPlayer(1)->attack();
                $this->assertNotNull($result);

                $this->assertTrue($result->somePlayersWasHit());
                $this->assertFalse($state->getPlayer(2)->isAlive());
                $game->quit(GameOverReason::TIE);
            }
            $i++;
        });
        $game->start();
        $this->assertNotNull($result);
    }

    public function test1BacktrackEnableTwoTick(): void
    {
        $game = $this->_setupGame1(2);
        $i = 0;
        $result = null;
        $game->onTick(function (GameState $state) use ($game, &$i, &$result) {
            if ($state->getTickId() <= Util::millisecondsToFrames(PistolGlock::equipReadyTimeMs)) {
                return;
            }

            $state->getPlayer(2)->moveRight();
            if ($i === 2) {
                $result = $state->getPlayer(1)->attack();
                $this->assertNotNull($result);

                $this->assertTrue($result->somePlayersWasHit());
                $this->assertFalse($state->getPlayer(2)->isAlive());
                $game->quit(GameOverReason::TIE);
            }
            $i++;
        });
        $game->start();
        $this->assertNotNull($result);
    }

    public function test1BacktrackEnableTwoTickNoHitWhenPlayerThreeTickAwayFromHit(): void
    {
        $game = $this->_setupGame1(2);
        $i = 0;
        $result = null;
        $game->onTick(function (GameState $state) use ($game, &$i, &$result) {
            if ($state->getTickId() <= Util::millisecondsToFrames(PistolGlock::equipReadyTimeMs)) {
                return;
            }

            $state->getPlayer(2)->moveRight();
            if ($i === 3) {
                $result = $state->getPlayer(1)->attack();
                $this->assertNotNull($result);

                $this->assertFalse($result->somePlayersWasHit());
                $this->assertTrue($state->getPlayer(2)->isAlive());
                $game->quit(GameOverReason::TIE);
            }
            $i++;
        });
        $game->start();
        $this->assertNotNull($result);
    }

    public function test1BacktrackEnableFourTick(): void
    {
        $game = $this->_setupGame1(4);
        $i = 0;
        $result = null;
        $game->onTick(function (GameState $state) use ($game, &$i, &$result) {
            if ($state->getTickId() <= Util::millisecondsToFrames(PistolGlock::equipReadyTimeMs)) {
                return;
            }

            $state->getPlayer(2)->moveRight();
            if ($i === 3) {
                $result = $state->getPlayer(1)->attack();
                $this->assertNotNull($result);

                $this->assertTrue($result->somePlayersWasHit());
                $this->assertFalse($state->getPlayer(2)->isAlive());
                $game->quit(GameOverReason::TIE);
            }
            $i++;
        });
        $game->start();
        $this->assertNotNull($result);
    }

}
