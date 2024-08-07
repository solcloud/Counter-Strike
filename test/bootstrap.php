<?php

namespace Test;

require __DIR__ . '/../vendor/autoload.php';

use cs\Core\Game;
use cs\Core\Point;
use cs\Event\AttackResult;
use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{

    public function assertPlayerHit(?AttackResult $attackResult): AttackResult
    {
        $this->assertNotNull($attackResult, 'No attack result');
        $this->assertTrue($attackResult->somePlayersWasHit(), 'No players were hit');

        return $attackResult;
    }

    public function assertPlayerNotHit(?AttackResult $attackResult): AttackResult
    {
        $this->assertNotNull($attackResult, 'No attack result');
        $this->assertFalse($attackResult->somePlayersWasHit(), 'Some players were hit');

        return $attackResult;
    }

    public function assertPositionSame(Point $expected, Point $actual, string $extraMsg = ''): void
    {
        $this->assertTrue($expected->equals($actual), "Expected: {$expected} <> {$actual} actual." . $extraMsg);
    }

    public function assertPositionNotSame(Point $expected, Point $actual, string $extraMsg = ''): void
    {
        $this->assertFalse($expected->equals($actual), "Expected: {$expected} is equal {$actual} actual." . $extraMsg);
    }

    public function assertPlayerPosition(Game $game, Point $expectedPosition, int $playerId = 1): void
    {
        $this->assertPositionSame($expectedPosition, $game->getPlayer($playerId)->getPositionClone());
    }

}
