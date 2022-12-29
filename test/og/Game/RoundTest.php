<?php

namespace Test\Game;

use cs\Core\GameProperty;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Util;
use cs\Enum\BuyMenuItem;
use cs\Enum\SoundType;
use cs\Equipment\Bomb;
use cs\Event\GameOverEvent;
use cs\Event\KillEvent;
use cs\Event\PauseEndEvent;
use cs\Event\PauseStartEvent;
use cs\Event\RoundEndCoolDownEvent;
use cs\Event\RoundEndEvent;
use cs\Event\RoundStartEvent;
use cs\Event\SoundEvent;
use cs\Weapon\PistolGlock;
use cs\Weapon\RifleAk;
use Test\BaseTestCase;

class RoundTest extends BaseTestCase
{

    public function testMsToTickConstantTenOnTest(): void
    {
        $this->assertSame(0, Util::millisecondsToFrames(0));
        $this->assertSame(1, Util::millisecondsToFrames(1));
        $this->assertSame(1, Util::millisecondsToFrames(10));
        $this->assertSame(123, Util::millisecondsToFrames(1230));
        $this->assertSame(240, Util::millisecondsToFrames(RifleAk::reloadTimeMs));
    }

    public function testRoundEndWhenNoPlayersAreAlive(): void
    {
        $playerCommands = [
            fn(Player $p) => $p->jump(),
            fn(Player $p) => $p->suicide(),
            $this->endGame(),
        ];

        $break = false;
        $killEvents = [];
        $dropEvents = [];
        $game = $this->createNoPauseGame();
        $game->onEvents(function (array $events) use (&$killEvents, &$dropEvents, &$break): void {
            if ($break) {
                return;
            }
            if ($events[0] instanceof GameOverEvent) {
                $break = true;
            }

            foreach ($events as $event) {
                if ($event instanceof KillEvent) {
                    $killEvents[] = $event;
                }
                if ($event instanceof SoundEvent && $event->type === SoundType::ITEM_DROP) {
                    $dropEvents[] = $event;
                }
            }
        });

        $this->assertSame(1, $game->getRoundNumber());
        $this->playPlayer($game, $playerCommands);
        $this->assertSame(2, $game->getRoundNumber());

        $this->assertTrue($break);
        $this->assertFalse($game->getPlayer(1)->isAlive());

        $this->assertCount(1, $killEvents);
        $killEvent = $killEvents[0];
        $this->assertInstanceOf(KillEvent::class, $killEvent);
        $this->assertFalse($killEvent->wasHeadShot());
        $this->assertSame(1, $killEvent->getPlayerDead()->getId());
        $this->assertSame(1, $killEvent->getPlayerCulprit()->getId());

        $this->assertCount(2, $dropEvents);
        $drop1 = $dropEvents[0];
        $drop2 = $dropEvents[1];
        $this->assertInstanceOf(SoundEvent::class, $drop1);
        $this->assertInstanceOf(SoundEvent::class, $drop2);
        $this->assertInstanceOf(PistolGlock::class, $drop1->getItem());
        $this->assertInstanceOf(Bomb::class, $drop2->getItem());
        $pp = $game->getPlayer(1)->getPositionClone();
        $pr = $game->getPlayer(1)->getBoundingRadius();
        $this->assertGreaterThan(0, $pp->y);
        $this->assertSame(0, $drop1->position->y);
        $this->assertLessThanOrEqual($pr, abs($drop1->position->x - $pp->x));
        $this->assertLessThanOrEqual($pr, abs($drop1->position->z - $pp->z));
        $this->assertSame(0, $drop2->position->y);
        $this->assertLessThanOrEqual($pr, abs($drop2->position->x - $pp->x));
        $this->assertLessThanOrEqual($pr, abs($drop2->position->z - $pp->z));
    }

    public function testSkippingTicksPlayerSimulation(): void
    {
        $called = false;
        $playerCommands = [
            $this->waitXTicks(10),
            fn(Player $p) => $p->buyItem(BuyMenuItem::RIFLE_AK),
            function (Player $p) use (&$called): void {
                $this->assertInstanceOf(RifleAk::class, $p->getEquippedItem());
                $called = true;
            },
        ];

        $game = $this->simulateGame($playerCommands, [GameProperty::START_MONEY => 16000]);
        $this->assertSame(12, $game->getTickId());
        $this->assertTrue($called);
    }

    public function testFreezeTime(): void
    {
        $game = $this->createGame([
            GameProperty::FREEZE_TIME_SEC => 0,
        ]);
        $this->assertTrue($game->isPaused());
        $game->tick(0);
        $this->assertFalse($game->isPaused());
        $events = $game->consumeTickEvents();
        $this->assertCount(3, $events);
        $this->assertInstanceOf(PauseStartEvent::class, $events[0]);
        $this->assertInstanceOf(PauseEndEvent::class, $events[1]);
        $this->assertInstanceOf(RoundStartEvent::class, $events[2]);
    }

    public function testFreezeTime1(): void
    {
        $game = $this->createGame([
            GameProperty::FREEZE_TIME_SEC => 1,
        ]);

        $tickId = false;
        foreach (range(0, (int)(1000 / Util::$TICK_RATE)) as $tickId) {
            $this->assertTrue($game->isPaused(), "Tick: {$tickId}");
            $game->tick($tickId);
        }
        $this->assertIsInt($tickId);
        $game->tick($tickId++);
        $this->assertFalse($game->isPaused());
    }

    public function testRoundEndEventFiredOncePerRoundEndActually(): void
    {
        $maxRounds = 5;
        $game = $this->createGame([
            GameProperty::MAX_ROUNDS    => $maxRounds,
            GameProperty::ROUND_TIME_MS => 1,
        ]);

        $roundEndEventsCount = 0;
        $roundCoolDownEventsCount = 0;
        $game->setTickMax($maxRounds * 2);
        $game->onTick(function (GameState $state) {
            $state->getPlayer(1)->moveForward();
        });
        $game->onEvents(function (array $events) use (&$roundEndEventsCount, &$roundCoolDownEventsCount): void {
            foreach ($events as $event) {
                if ($event instanceof RoundEndEvent) {
                    $roundEndEventsCount++;
                }
                if ($event instanceof RoundEndCoolDownEvent) {
                    $roundCoolDownEventsCount++;
                }
            }
        });
        $game->start();
        $this->assertSame($maxRounds, $roundEndEventsCount);
        $this->assertSame($maxRounds - 2, $roundCoolDownEventsCount); // (firstRound + halfTime)
        $this->assertSame($maxRounds + 1, $game->getRoundNumber());
    }

    public function testHalfTimeSwitch(): void
    {
        $maxRounds = 5;
        $game = $this->createGame([
            GameProperty::MAX_ROUNDS           => $maxRounds,
            GameProperty::ROUND_TIME_MS        => 1,
            GameProperty::HALF_TIME_FREEZE_SEC => 0,
            GameProperty::START_MONEY          => 3000,
        ]);
        $game->setTickMax($maxRounds * 2);

        $this->assertTrue($game->getPlayer(1)->isPlayingOnAttackerSide());
        $this->assertTrue($game->getPlayer(1)->buyItem(BuyMenuItem::RIFLE_AK));
        $this->assertTrue($game->getScore()->isTie());

        $game->start();
        $this->assertSame($maxRounds + 1, $game->getRoundNumber());
        $this->assertFalse($game->getPlayer(1)->isPlayingOnAttackerSide());
        $this->assertNotInstanceOf(RifleAk::class, $game->getPlayer(1)->getEquippedItem());
        $this->assertFalse($game->getScore()->isTie());
        $this->assertTrue($game->getScore()->defendersIsWinning());
        $this->assertFalse($game->getScore()->attackersIsWinning());
        $this->assertSame(3, $game->getScore()->getScoreDefenders());
        $this->assertSame(2, $game->getScore()->getScoreAttackers());
    }

}
