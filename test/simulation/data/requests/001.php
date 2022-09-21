<?php

use cs\Core\Game;
use cs\Core\Point;
use Test\BaseTest;

return function (BaseTest $test, Game $game): void {
    $test->assertPositionNotSame(new Point(1245, 0, 50), $game->getPlayer(1)->getPositionImmutable());
    $test->assertPositionSame(new Point(1459, 0, 45), $game->getPlayer(1)->getPositionImmutable());
};
