<?php

namespace Test\World;

use cs\Core\Game;
use cs\Core\Point;
use cs\Core\Wall;
use cs\Core\World;
use Test\BaseTestCase;

class WallTest extends BaseTestCase
{

    public function testIsWallAt(): void
    {
        $base = 250;
        $wallHorizontal = new Wall(new Point(0, 0, $base), true, $base);
        $wallVertical = new Wall(new Point($base, 0, 0), false, $base);
        $world = new World(new Game());
        $world->addWall($wallHorizontal);
        $world->addWall($wallVertical);

        $candidate = $world->isWallAt(new Point($base, 0, $base));
        $this->assertNotNull($candidate);
    }

}
