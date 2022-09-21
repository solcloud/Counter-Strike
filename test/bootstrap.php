<?php

namespace Test;

require __DIR__ . '/../vendor/autoload.php';

use cs\Core\Game;
use cs\Core\Point;
use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{

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
        $this->assertPositionSame($expectedPosition, $game->getPlayer($playerId)->getPositionImmutable());
    }

}
