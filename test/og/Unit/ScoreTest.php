<?php

namespace Test\Unit;

use cs\Core\Player;
use cs\Core\Score;
use cs\Enum\Color;
use Test\BaseTest;

final class ScoreTest extends BaseTest
{

    public function testScoreBoardSort(): void
    {
        $score = new Score([10]);
        $score->addPlayer(new Player(1, Color::BLUE, false));
        $score->addPlayer(new Player(2, Color::ORANGE, false));
        $score->addPlayer(new Player(3, Color::GREEN, true));
        $score->addPlayer(new Player(4, Color::PURPLE, true));

        $score->getPlayerStat(1)->addDeath();
        $score->getPlayerStat(2)->addDamage(987);
        $score->getPlayerStat(2)->addKill(false);
        $score->getPlayerStat(3)->addDeath();
        $score->getPlayerStat(4)->addDamage(21);
        $score->getPlayerStat(4)->addKill(true);
        $scoreBoard = $score->toArray();
        $this->assertIsArray($scoreBoard['score'] ?? false);
        $this->assertIsArray($scoreBoard['lossBonus'] ?? false);
        $this->assertIsArray($scoreBoard['history'] ?? false);
        $this->assertIsArray($scoreBoard['firstHalfScore'] ?? false);
        $this->assertIsArray($scoreBoard['secondHalfScore'] ?? false);
        $this->assertIsArray($scoreBoard['scoreboard'] ?? false);
        $this->assertSame([
            [
                [
                    'id' => 2,
                    'kills' => 1,
                    'deaths' => 0,
                    'damage' => 100,
                ],
                [
                    'id' => 1,
                    'kills' => 0,
                    'deaths' => 1,
                    'damage' => 0,
                ],
            ],
            [
                [
                    'id' => 4,
                    'kills' => 1,
                    'deaths' => 0,
                    'damage' => 21,
                ],
                [
                    'id' => 3,
                    'kills' => 0,
                    'deaths' => 1,
                    'damage' => 0,
                ],
            ],
        ], $scoreBoard['scoreboard']);
    }

}
