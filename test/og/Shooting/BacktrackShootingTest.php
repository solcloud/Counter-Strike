<?php

namespace Test\Shooting;

use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Util;
use cs\Core\Wall;
use cs\Enum\Color;
use cs\Enum\GameOverReason;
use cs\Enum\SoundType;
use cs\Event\GameOverEvent;
use cs\Event\SoundEvent;
use cs\Weapon\PistolGlock;
use Test\BaseTestCase;
use Test\TestGame;

class BacktrackShootingTest extends BaseTestCase
{

    private function _setupGame1(int $backtrackTickCount): TestGame
    {
        $property = $this->createNoPauseGameProperty();
        $property->backtrack_history_tick_count = $backtrackTickCount;
        $game = $this->createTestGame(null, $property);
        $player1 = $game->getPlayer(1);
        $player1->setPosition($player1->getPositionClone()->addX($player1->getBoundingRadius()));
        $player1->getSight()->look(0, 0);
        $player1->equipSecondaryWeapon();
        $glock = $player1->getEquippedItem();
        $this->assertInstanceOf(PistolGlock::class, $glock);
        $wall = new Wall(
            new Point($player1->getPositionClone()->x + 1, 0, $player1->getPositionClone()->z + $player1->getBoundingRadius() + 30), true, 800
        );
        $game->getWorld()->addWall($wall->setPenetrable(false));

        $player2 = new Player(2, Color::GREEN, false);
        $game->addPlayer($player2);
        $player2->setPosition($player1->getPositionClone()->addZ($player1->getBoundingRadius() + 100));
        $game->getWorld()->addWall(new Wall(new Point(0, 0, $player2->getPositionClone()->z + 300), true, 800));

        $this->assertTrue($player1->isAlive());
        $this->assertTrue($player2->isAlive());

        return $game;
    }

    public function testNoBacktrackKill(): void
    {
        $game = $this->_setupGame1(0);
        $result = null;
        $game->onTick(function (GameState $state) use ($game, &$result) {
            if ($state->getTickId() <= Util::millisecondsToFrames(PistolGlock::equipReadyTimeMs)) {
                return;
            }

            $result = $this->assertPlayerHit($state->getPlayer(1)->attack());
            $this->assertFalse($state->getPlayer(2)->isAlive());
            $game->quit(GameOverReason::TIE);
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
                $result = $this->assertPlayerNotHit($state->getPlayer(1)->attack());
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
                $result = $this->assertPlayerHit($state->getPlayer(1)->attack());
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
                $result = $this->assertPlayerHit($state->getPlayer(1)->attack());
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
                $result = $this->assertPlayerNotHit($state->getPlayer(1)->attack());
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
        $deadSound = null;
        $player2InitialPosition = $game->getPlayer(2)->getPositionClone();
        $game->onEvents(function (array $events) use (&$deadSound, $player2InitialPosition): void {
            foreach ($events as $event) {
                if ($event instanceof SoundEvent && $event->type === SoundType::PLAYER_DEAD) {
                    $this->assertNull($deadSound);
                    $deadSound = $event;
                    $this->assertPositionSame($player2InitialPosition, $deadSound->position);
                }
            }
        });
        $game->onTick(function (GameState $state) use ($game, &$i, &$result, $player2InitialPosition) {
            if ($state->getTickId() <= Util::millisecondsToFrames(PistolGlock::equipReadyTimeMs)) {
                return;
            }

            $state->getPlayer(2)->moveRight();
            if ($i === 3) {
                $this->assertPositionNotSame($player2InitialPosition, $state->getPlayer(2)->getPositionClone());
                $result = $this->assertPlayerHit($state->getPlayer(1)->attack());
                $this->assertFalse($state->getPlayer(2)->isAlive());
            }
            if ($i === 4) {
                $game->quit(GameOverReason::TIE);
                $tickEvents = $game->consumeTickEvents();
                $this->assertCount(1, $tickEvents);
                $this->assertInstanceOf(GameOverEvent::class, $tickEvents[0]);
            }
            $i++;
        });
        $game->start();
        $this->assertPositionNotSame($player2InitialPosition, $game->getPlayer(2)->getPositionClone());
        $this->assertNotNull($result);
        $this->assertNotNull($deadSound);
    }

}
