<?php

namespace Test\World;

use cs\Core\Floor;
use cs\Core\Point;
use cs\Core\World;
use Test\BaseTestCase;

class WorldTest extends BaseTestCase
{

    public function testFindFloor(): void
    {
        $game = $this->createGame();
        $world = new World($game);
        $world->addFloor(new Floor(new Point(10, 1, 0), 1, 1));

        $this->assertNull($world->findFloor(new Point(0, 2, 0), 999));
        $this->assertNull($world->findFloor(new Point(0, 1, 0), 1));
        $this->assertNull($world->findFloor(new Point(9, 1, 0), 0));
        $this->assertNull($world->findFloor(new Point(7, 1, 0), 2));
        $this->assertNull($world->findFloor(new Point(12, 1, 0), 0));
        $this->assertNull($world->findFloor(new Point(14, 1, 0), 2));
        $this->assertNotNull($world->findFloor(new Point(0, 1, 0), 10));
        $this->assertNotNull($world->findFloor(new Point(0, 1, 0), 12));
        $this->assertNotNull($world->findFloor(new Point(8, 1, 0), 2));
        $this->assertNotNull($world->findFloor(new Point(11, 1, 0), 2));
        $this->assertNotNull($world->findFloor(new Point(12, 1, 0), 2));
        $this->assertNull($world->findFloor(new Point(15, 1, 0), 3));
    }

}
