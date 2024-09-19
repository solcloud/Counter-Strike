<?php

namespace Test\Unit;

use cs\Core\Box;
use cs\Core\Floor;
use cs\Core\GameException;
use cs\Core\Point;
use cs\Core\Wall;
use Test\BaseTest;

class BoxTest extends BaseTest
{

    public function testBox(): void
    {
        $width = 19;
        $height = 79;
        $depth = 41;
        $point = new Point(10, 20, 50);

        $box = new Box($point, $width, $height, $depth);
        $walls = $box->getWalls();
        $floors = $box->getFloors();
        $this->assertCount(4, $walls);
        $this->assertCount(2, $floors);

        $bottomFloor = $floors[0];
        $this->assertInstanceOf(Floor::class, $bottomFloor);
        $this->assertSame($point->y, $bottomFloor->getY());
        $this->assertPositionSame($point, $bottomFloor->getStart());
        $pointEnd = $point->clone()->addX($width)->addZ($depth);
        $this->assertPositionSame($pointEnd, $bottomFloor->getEnd());
        $topFloor = $floors[1];
        $this->assertInstanceOf(Floor::class, $topFloor);
        $this->assertSame($point->y + $height, $topFloor->getY());
        $this->assertPositionSame($point->clone()->addY($height), $topFloor->getStart());
        $pointEnd = $point->clone()->addY($height)->addX($width)->addZ($depth);
        $this->assertPositionSame($pointEnd, $topFloor->getEnd());


        $frontWall = $walls[0];
        $this->assertInstanceOf(Wall::class, $frontWall);
        $this->assertPositionSame($point, $frontWall->getStart());
        $pointEnd = $point->clone()->addY($height)->addX($width);
        $this->assertPositionSame($pointEnd, $frontWall->getEnd());
        $backWall = $walls[1];
        $this->assertInstanceOf(Wall::class, $backWall);
        $this->assertPositionSame($point->clone()->addZ($depth), $backWall->getStart());
        $pointEnd = $point->clone()->addZ($depth)->addY($height)->addX($width);
        $this->assertPositionSame($pointEnd, $backWall->getEnd());
        $leftWall = $walls[2];
        $this->assertInstanceOf(Wall::class, $leftWall);
        $this->assertPositionSame($point, $leftWall->getStart());
        $pointEnd = $point->clone()->addZ($depth)->addY($height);
        $this->assertPositionSame($pointEnd, $leftWall->getEnd());
        $rightWall = $walls[3];
        $this->assertInstanceOf(Wall::class, $rightWall);
        $this->assertPositionSame($point->clone()->addX($width), $rightWall->getStart());
        $pointEnd = $point->clone()->addX($width)->addZ($depth)->addY($height);
        $this->assertPositionSame($pointEnd, $rightWall->getEnd());

        $this->assertSame([
            'width'  => $width,
            'height' => $height,
            'depth'  => $depth,
            'x'      => $point->x,
            'y'      => $point->y,
            'z'      => $point->z,
        ], $box->toArray());
    }

    public function testBoxPenetrableParamPropagation(): void
    {
        $box = new Box(new Point(), 100, 100, 100, Box::SIDE_BACK | Box::SIDE_BOTTOM);

        $plane = $box->getWalls()[0] ?? null;
        $this->assertInstanceOf(Wall::class, $plane);
        $plane->setHitAntiForce(101, 12, 1);
        $this->assertSame(12, $plane->getHitAntiForce($box->getBase()));
        $this->assertSame(12, $plane->getHitAntiForce($box->getBase()->clone()->addX(50)));
        $this->assertSame(12, $plane->getHitAntiForce($box->getBase()->clone()->addY(50)));
        $this->assertSame(101, $plane->getHitAntiForce($box->getBase()->clone()->addPart(50, 50, 50)));

        $plane = $box->getFloors()[0] ?? null;
        $this->assertInstanceOf(Floor::class, $plane);
        $plane->setHitAntiForce(101, 12, 1);
        $this->assertSame(12, $plane->getHitAntiForce($box->getBase()));
        $this->assertSame(12, $plane->getHitAntiForce($box->getBase()->clone()->addX(50)));
        $this->assertSame(12, $plane->getHitAntiForce($box->getBase()->clone()->addY(50)));
        $this->assertSame(101, $plane->getHitAntiForce($box->getBase()->clone()->addPart(50, 50, 50)));

        $this->expectException(GameException::class);
        $this->assertSame(12, $plane->getHitAntiForce($box->getBase()->addPart(-1, -1, -1)));
    }

    public function testBoxUnPenetrable(): void
    {
        $box = new Box(new Point(), 100, 100, 100, Box::SIDE_BACK | Box::SIDE_BOTTOM, false);
        $plane = $box->getWalls()[0] ?? null;
        $this->assertInstanceOf(Wall::class, $plane);

        $this->assertSame($plane::MAX_HIT_ANTI_FORCE, $plane->getHitAntiForce($box->getBase()));
        $this->assertSame($plane::MAX_HIT_ANTI_FORCE, $plane->getHitAntiForce($box->getBase()->clone()->addPart(50, 50, 50)));
        $this->assertSame($plane::MAX_HIT_ANTI_FORCE, $plane->getHitAntiForce($box->getBase()->clone()->addPart(-50, -50, -50)));

        $plane = $box->getFloors()[0] ?? null;
        $this->assertInstanceOf(Floor::class, $plane);

        $this->assertSame($plane::MAX_HIT_ANTI_FORCE, $plane->getHitAntiForce($box->getBase()));
        $this->assertSame($plane::MAX_HIT_ANTI_FORCE, $plane->getHitAntiForce($box->getBase()->clone()->addPart(50, 50, 50)));
        $this->assertSame($plane::MAX_HIT_ANTI_FORCE, $plane->getHitAntiForce($box->getBase()->clone()->addPart(-50, -50, -50)));
    }

    public function testBoxPlaneCounts(): void
    {
        $box = new Box(new Point(), 100, 100, 100, Box::SIDE_TOP);
        $this->assertCount(1, $box->getFloors());
        $this->assertCount(0, $box->getWalls());
    }

    public function testBoxWithoutSideThrow(): void
    {
        $this->expectException(GameException::class);
        $this->expectExceptionMessage('Choose at least one box side');
        new Box(new Point(), 1, 1, 1, 0);
    }

}
