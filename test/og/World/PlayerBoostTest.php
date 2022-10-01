<?php

namespace Test\World;

use cs\Core\Player;
use cs\Core\Point;
use cs\Enum\Color;
use Test\BaseTestCase;

class PlayerBoostTest extends BaseTestCase
{

    public function testPlayerCanStandOnTopOfOtherPlayer(): void
    {
        $player2 = new Player(2, Color::GREEN, false);
        $game = $this->createTestGame(20);
        $game->addPlayer($player2);
        $player1 = $game->getPlayer(1);
        $player2->setPosition($player1->getPositionImmutable()->addY($player1->getHeadHeight() + 10));

        $game->start();
        $this->assertPositionSame(new Point(0, $player1->getHeadHeight(), 0), $game->getPlayer(2)->getPositionImmutable());
    }

}
