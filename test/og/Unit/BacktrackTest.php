<?php

namespace Test\Unit;

use cs\Core\Backtrack;
use cs\Core\Game;
use cs\Core\Player;
use cs\Enum\Color;
use cs\Map\TestMap;
use Test\BaseTest;

final class BacktrackTest extends BaseTest
{
    protected function createGame(): Game
    {
        $player = new Player(1, Color::ORANGE, true);
        $game = new Game();
        $game->loadMap(new TestMap());
        $game->addPlayer($player);

        return $game;
    }

    public function test1(): void
    {
        $game = $this->createGame();
        $player = $game->getPlayer(1);
        $player->getSight()->look(123, -9);
        $origPosition = $player->getPositionClone();
        $origPlayerHeadHeight = $player->getHeadHeight();
        $modifiedHeadHeight = $origPlayerHeadHeight - 3;

        $backtrack = new Backtrack($game, 123);
        $this->assertCount(1, $backtrack->getStates());
        $this->assertSame([0], $backtrack->getStates());

        $backtrack->startState();
        $backtrack->addStateData($player);
        $backtrack->finishState();
        $this->assertCount(1, $backtrack->getStates());
        $this->assertSame([0], $backtrack->getStates());

        $player->setPosition($origPosition->clone()->addX(10));
        $player->setHeadHeight($modifiedHeadHeight);
        $player->getSight()->look(12, 13);

        $backtrack->startState();
        $backtrack->addStateData($player);
        $backtrack->finishState();

        $this->assertCount(1, $backtrack->getStates());
        $this->assertSame([1], $backtrack->getStates());

        $this->assertSame($modifiedHeadHeight, $player->getHeadHeight());
        $this->assertSame(12.0, $player->getSight()->getRotationHorizontal());
        $this->assertPositionSame($origPosition->clone()->addX(10), $player->getPositionClone());

        $backtrack->saveState();
        $states = $backtrack->getStates();
        $this->assertCount(1, $states);
        $backtrack->apply($states[0], $player->getId());
        $this->assertSame($origPlayerHeadHeight, $player->getHeadHeight());
        $this->assertSame(123.0, $player->getSight()->getRotationHorizontal());
        $this->assertSame(-9.0, $player->getSight()->getRotationVertical());
        $this->assertPositionSame($origPosition, $player->getPositionClone());
        $backtrack->restoreState();

        $this->assertSame($modifiedHeadHeight, $player->getHeadHeight());
        $this->assertSame(12.0, $player->getSight()->getRotationHorizontal());
        $this->assertSame(13.0, $player->getSight()->getRotationVertical());
        $this->assertPositionSame($origPosition->clone()->addX(10), $player->getPositionClone());
    }

    public function testEmptyStateIsSkippedInGetStates(): void
    {
        $game = $this->createGame();
        $player = $game->getPlayer(1);
        $backtrack = new Backtrack($game, 5);
        $this->assertSame([0], $backtrack->getStates());

        $backtrack->startState();
        $backtrack->addStateData($player);
        $backtrack->finishState();
        $this->assertSame([0], $backtrack->getStates());

        $backtrack->startState();
        $backtrack->finishState();
        $this->assertSame([1], $backtrack->getStates());

        $backtrack->startState();
        $backtrack->addStateData($player);
        $backtrack->finishState();
        $this->assertSame([2], $backtrack->getStates());

        $backtrack->startState();
        $backtrack->finishState();
        $this->assertSame([1, 3], $backtrack->getStates());

        $backtrack->startState();
        $backtrack->addStateData($player);
        $backtrack->finishState();
        $this->assertSame([2, 4], $backtrack->getStates());

        $backtrack->startState();
        $backtrack->addStateData($player);
        $backtrack->finishState();
        $this->assertSame([1, 3, 5], $backtrack->getStates());

        $backtrack->startState();
        $backtrack->finishState();
        $this->assertSame([1, 2, 4], $backtrack->getStates());

        $backtrack->startState();
        $backtrack->finishState();
        $this->assertSame([2, 3, 5], $backtrack->getStates());

        $backtrack->startState();
        $backtrack->finishState();
        $this->assertSame([3, 4], $backtrack->getStates());

        $backtrack->startState();
        $backtrack->addStateData($player);
        $backtrack->finishState();
        $this->assertSame([4, 5], $backtrack->getStates());

        $backtrack->startState();
        $backtrack->finishState();
        $this->assertSame([1, 5], $backtrack->getStates());

        $backtrack->apply(2, $player->getId());
        $backtrack->apply(1, -1);
    }
}
