<?php

namespace Test\Unit;

use cs\Core\Floor;
use cs\Core\PlaneBuilder;
use cs\Core\Point;
use cs\Core\Wall;
use Test\BaseTest;

class PlaneBuilderTest extends BaseTest
{
    public function testAABBFloor(): void
    {
        $pb = new PlaneBuilder();
        $planes = $pb->fromQuad(
            new Point(1, 1, 30), new Point(10, 1, 30),
            new Point(1, 1, 2), new Point(10, 1, 2),
        );
        $this->assertCount(1, $planes);
        $floor = $planes[0];
        $this->assertInstanceOf(Floor::class, $floor);
        $this->assertPositionSame(new Point(1, 1, 2), $floor->getStart());
        $this->assertPositionSame(new Point(10, 1, 30), $floor->getEnd());
        $this->assertSame(9, $floor->width);
        $this->assertSame(28, $floor->depth);
    }

    public function testAABBFloor2(): void
    {
        $pb = new PlaneBuilder();
        $points = [
            new Point(1, 1, 30), new Point(10, 1, 30),
            new Point(1, 1, 2), new Point(10, 1, 2),
        ];
        shuffle($points);

        $planes = $pb->fromQuad(...$points);
        $this->assertCount(1, $planes);
        $floor = $planes[0];
        $this->assertInstanceOf(Floor::class, $floor);
        $this->assertPositionSame(new Point(1, 1, 2), $floor->getStart());
        $this->assertPositionSame(new Point(10, 1, 30), $floor->getEnd());
        $this->assertSame(9, $floor->width);
        $this->assertSame(28, $floor->depth);
    }

    public function testAABBFloor3(): void
    {
        $expected = new Floor(new Point(rand(-100, 100), rand(-100, 100), rand(-100, 100)), rand(1, 100), rand(1, 100));
        $pb = new PlaneBuilder();
        $points = [
            $expected->getStart()->clone(),
            $expected->getStart()->clone()->addZ($expected->depth),
            $expected->getStart()->clone()->addX($expected->width),
            $expected->getStart()->clone()->addX($expected->width)->addZ($expected->depth),
        ];
        shuffle($points);

        $planes = $pb->fromQuad(...$points);
        $this->assertCount(1, $planes);
        $floor = $planes[0];
        $this->assertInstanceOf($expected::class, $floor);
        $this->assertPositionSame($expected->getStart(), $floor->getStart());
        $this->assertPositionSame($expected->getEnd(), $floor->getEnd());
        $this->assertSame($expected->width, $floor->width);
        $this->assertSame($expected->depth, $floor->depth);
    }

    public function testTriangle(): void
    {
        $pb = new PlaneBuilder();
        $planes = $pb->fromTriangle(
            new Point(2, 1, 3),
            new Point(4, 3, 5),
            new Point(6, 2, 1),
        );
        $this->assertCount(21, $planes);
    }

    public function testTriangleVoxelSize(): void
    {
        $pb = new PlaneBuilder();
        $planes = $pb->create(
            new Point(6, 3, 9),
            new Point(12, 18, 15),
            new Point(18, 12, 3),
            null,
            3.9,
        );
        $this->assertCount(9, $planes);
    }

    public function testTriangleBoundary(): void
    {
        $pb = new PlaneBuilder();
        $planes = $pb->create(
            new Point(2, 1, 6),
            new Point(12, 4, 22),
            new Point(5, 11, -1),
        );
        $this->assertCount(426, $planes);

        $boundaryMin = (new Point())->setScalar(PHP_INT_MAX);
        $boundaryMax = (new Point())->setScalar(PHP_INT_MIN);
        foreach ($planes as $plane) {
            $boundaryMin->set(
                min($boundaryMin->x, $plane->getStart()->x),
                min($boundaryMin->y, $plane->getStart()->y),
                min($boundaryMin->z, $plane->getStart()->z),
            );
            $boundaryMax->set(
                max($boundaryMax->x, $plane->getEnd()->x),
                max($boundaryMax->y, $plane->getEnd()->y),
                max($boundaryMax->z, $plane->getEnd()->z),
            );
        }
        $this->assertPositionSame(new Point(2, 1, 0), $boundaryMin);
        $this->assertPositionSame(new Point(12, 11, 22), $boundaryMax);
    }

    public function testTriangleBoundaryNegative(): void
    {
        $pb = new PlaneBuilder();
        $planes = $pb->create(
            new Point(2, 1, 6),
            new Point(12, 4, 22),
            new Point(5, 11, -1),
            null,
            -1.0,
        );
        $this->assertCount(438, $planes);

        $boundaryMin = (new Point())->setScalar(PHP_INT_MAX);
        $boundaryMax = (new Point())->setScalar(PHP_INT_MIN);
        foreach ($planes as $plane) {
            $boundaryMin->set(
                min($boundaryMin->x, $plane->getStart()->x),
                min($boundaryMin->y, $plane->getStart()->y),
                min($boundaryMin->z, $plane->getStart()->z),
            );
            $boundaryMax->set(
                max($boundaryMax->x, $plane->getEnd()->x),
                max($boundaryMax->y, $plane->getEnd()->y),
                max($boundaryMax->z, $plane->getEnd()->z),
            );
        }
        $this->assertPositionSame(new Point(2, 1, -1), $boundaryMin);
        $this->assertPositionSame(new Point(13, 12, 23), $boundaryMax);
    }

    public function testAABBWallX(): void
    {
        $pb = new PlaneBuilder();
        $planes = $pb->fromQuad(
            new Point(1, 5, 2), new Point(10, 5, 2),
            new Point(1, 1, 2), new Point(10, 1, 2),
        );
        $this->assertCount(1, $planes);
        $wall = $planes[0];
        $this->assertInstanceOf(Wall::class, $wall);
        $this->assertPositionSame(new Point(1, 1, 2), $wall->getStart());
        $this->assertPositionSame(new Point(10, 5, 2), $wall->getEnd());
        $this->assertTrue($wall->isWidthOnXAxis());
        $this->assertSame(9, $wall->width);
        $this->assertSame(4, $wall->height);
    }

    public function testAABBWallZ(): void
    {
        $pb = new PlaneBuilder();
        $planes = $pb->fromQuad(
            new Point(1, 7, 2), new Point(1, 7, 12),
            new Point(1, 5, 2), new Point(1, 5, 12),
        );
        $this->assertCount(1, $planes);
        $wall = $planes[0];
        $this->assertInstanceOf(Wall::class, $wall);
        $this->assertPositionSame(new Point(1, 5, 2), $wall->getStart());
        $this->assertPositionSame(new Point(1, 7, 12), $wall->getEnd());
        $this->assertFalse($wall->isWidthOnXAxis());
        $this->assertSame(10, $wall->width);
        $this->assertSame(2, $wall->height);
    }

    public function testSimpleWall(): void
    {
        $widthOnXAxis = (bool)rand(0, 1);
        $expected = new Wall(new Point(rand(-100, 100), rand(-100, 100), rand(-100, 100)), $widthOnXAxis, rand(1, 100), rand(1, 100));
        $pb = new PlaneBuilder();
        $points = [
            $expected->getStart()->clone(),
            $expected->getStart()->clone()->addY($expected->height),
            $expected->getEnd()->clone(),
            $expected->getEnd()->clone()->addY(-$expected->height),
        ];
        shuffle($points);

        $planes = $pb->fromQuad(...$points);
        $this->assertCount(1, $planes);
        $wall = $planes[0];
        $this->assertInstanceOf($expected::class, $wall);
        $this->assertPositionSame($expected->getStart(), $wall->getStart());
        $this->assertPositionSame($expected->getEnd(), $wall->getEnd());
        $this->assertSame($expected->width, $wall->width);
        $this->assertSame($expected->height, $wall->height);
        $this->assertSame($expected->isWidthOnXAxis(), $wall->isWidthOnXAxis());
    }

    public function testRotatedSimpleWall(): void
    {
        $pb = new PlaneBuilder();
        $points = [
            new Point(1, 2, 3),
            new Point(1, 8, 3),
            new Point(2, 2, 6),
            new Point(2, 8, 6),
        ];
        shuffle($points);

        $planes = $pb->fromQuad(...$points);
        $this->assertCount(3, $planes);
        $this->assertInstanceOf(Wall::class, $planes[0]);
        $this->assertInstanceOf(Wall::class, $planes[1]);
        $this->assertInstanceOf(Wall::class, $planes[2]);
        $this->assertPositionSame(new Point(2, 2, 5), $planes[2]->getStart());
        $this->assertPositionSame(new Point(2, 8, 6), $planes[2]->getEnd());
    }

    public function testRotatedWallJaggedness(): void
    {
        $pb = new PlaneBuilder();
        $points = [
            new Point(1, 2, 3),
            new Point(1, 8, 3),
            new Point(2, 2, 6),
            new Point(2, 8, 6),
            4.1,
        ];

        $planes = $pb->fromQuad(...$points);
        $this->assertCount(2, $planes);
        $this->assertPositionSame(new Point(1, 2, 3), $planes[0]->getStart());
        $this->assertPositionSame(new Point(1, 8, 6), $planes[0]->getEnd());
        $this->assertPositionSame(new Point(1, 2, 6), $planes[1]->getStart());
        $this->assertPositionSame(new Point(2, 8, 6), $planes[1]->getEnd());
    }

    public function testRotatedWall(): void
    {
        $pb = new PlaneBuilder();
        $points = [
            new Point(1, 2, 3),
            new Point(1, 8, 3),
            new Point(8, 2, 6),
            new Point(8, 8, 6),
        ];
        shuffle($points);

        $planes = $pb->fromQuad(...$points);
        $this->assertGreaterThan(2, count($planes));
        $startPlane = array_shift($planes);
        $this->assertInstanceOf(Wall::class, $startPlane);
        $widthOnXAxis = $startPlane->isWidthOnXAxis();
        $start = $startPlane->getStart();
        $end = $start->clone()->addPart($widthOnXAxis ? $startPlane->width : 0, $startPlane->height, $widthOnXAxis ? 0 : $startPlane->width);
        $this->assertPositionSame($startPlane->getEnd(), $end);
        $previousEnd = $end->clone();
        $height = $startPlane->height;
        foreach ($planes as $plane) {
            $this->assertInstanceOf(Wall::class, $plane);
            $widthOnXAxis = $plane->isWidthOnXAxis();
            $this->assertPositionSame($end->addY(-$height), $plane->getStart());
            $this->assertTrue($previousEnd->x <= $plane->getStart()->x && $previousEnd->z <= $plane->getStart()->z);
            $this->assertTrue($plane->getEnd()->x > $previousEnd->x || $plane->getEnd()->z > $previousEnd->z);

            $end->setFrom($plane->getStart());
            $end->addPart($widthOnXAxis ? $plane->width : 0, $height, $widthOnXAxis ? 0 : $plane->width);
            $this->assertPositionSame($end, $plane->getEnd());
            $previousEnd->setFrom($end);
        }
    }

    public function testRotatedWall2(): void
    {
        $pb = new PlaneBuilder();
        $points = [
            new Point(1, 0, 0),
            new Point(1, 1, 0),
            new Point(0, 0, 1),
            new Point(0, 1, 1),
        ];
        shuffle($points);

        $planes = $pb->create(...$points);
        $this->assertCount(2, $planes);
        $this->assertPositionSame(new Point(0, 0, 1), $planes[0]->getStart());
        $this->assertPositionSame(new Point(1, 1, 1), $planes[0]->getEnd());
        $this->assertPositionSame(new Point(1, 0, 0), $planes[1]->getStart());
        $this->assertPositionSame(new Point(1, 1, 1), $planes[1]->getEnd());
    }

    public function testRotatedWallQuadrant3(): void
    {
        $pb = new PlaneBuilder();
        $points = [
            new Point(2, 2, 3),
            new Point(2, 8, 3),
            new Point(1, 2, 6),
            new Point(1, 8, 6),
        ];
        shuffle($points);

        $planes = $pb->create(...$points);
        $this->assertCount(3, $planes);
        $startPlane = array_shift($planes);
        $this->assertInstanceOf(Wall::class, $startPlane);
        $widthOnXAxis = ($startPlane->getPlane() == 'xy');
        $start = $startPlane->getStart();
        $end = $start->clone()->addPart($widthOnXAxis ? $startPlane->width : 0, $startPlane->height, $widthOnXAxis ? 0 : $startPlane->width);
        $this->assertPositionSame($startPlane->getEnd(), $end);
        $endWall = array_pop($planes);
        $this->assertPositionSame(new Point(2, 2, 3), $endWall->getStart());
        $this->assertPositionSame(new Point(2, 8, 4), $endWall->getEnd());
    }

    public function testRampOnZ1(): void
    {
        $pb = new PlaneBuilder();
        $points = [
            new Point(1, 2, 0),
            new Point(5, 2, 0),
            new Point(1, 3, 1),
            new Point(5, 3, 1),
        ];
        shuffle($points);

        $planes = $pb->fromQuad(...$points);
        $this->assertCount(2, $planes);

        $plane = $planes[0];
        $this->assertInstanceOf(Wall::class, $plane);
        $this->assertPositionSame(new Point(1, 2, 0), $plane->getStart());
        $this->assertPositionSame(new Point(5, 3, 0), $plane->getEnd());


        $plane = $planes[1];
        $this->assertInstanceOf(Floor::class, $plane);
        $this->assertPositionSame(new Point(1, 3, 0), $plane->getStart());
        $this->assertPositionSame(new Point(5, 3, 1), $plane->getEnd());
    }

    public function testRampOnZ2(): void
    {
        $pb = new PlaneBuilder();
        $points = [
            new Point(1, 3, 0),
            new Point(50, 3, 0),
            new Point(1, 2, 2),
            new Point(50, 2, 2),
        ];
        shuffle($points);

        $planes = $pb->fromQuad(...$points);
        $this->assertCount(3, $planes);

        $plane = $planes[0];
        $this->assertInstanceOf(Floor::class, $plane);
        $this->assertPositionSame(new Point(1, 3, 0), $plane->getStart());
        $this->assertPositionSame(new Point(50, 3, 1), $plane->getEnd());

        $plane = $planes[1];
        $this->assertInstanceOf(Wall::class, $plane);
        $this->assertTrue($plane->isWidthOnXAxis());
        $this->assertPositionSame(new Point(1, 2, 1), $plane->getStart());
        $this->assertPositionSame(new Point(50, 3, 1), $plane->getEnd());


        $plane = $planes[2];
        $this->assertInstanceOf(Floor::class, $plane);
        $this->assertPositionSame(new Point(1, 2, 1), $plane->getStart());
        $this->assertPositionSame(new Point(50, 2, 2), $plane->getEnd());
    }

    public function testRampOnX1(): void
    {
        $pb = new PlaneBuilder();
        $points = [
            new Point(1, 2, 1),
            new Point(5, 3, 1),
            new Point(1, 2, 30),
            new Point(5, 3, 30),
        ];
        shuffle($points);

        $planes = $pb->fromQuad(...$points);
        $this->assertCount(2, $planes);

        $plane = $planes[0];
        $this->assertInstanceOf(Floor::class, $plane);
        $this->assertSame(4, $plane->width);
        $this->assertSame(29, $plane->depth);
        $this->assertPositionSame(new Point(1, 2, 1), $plane->getStart());
        $this->assertPositionSame(new Point(5, 2, 30), $plane->getEnd());


        $plane = $planes[1];
        $this->assertInstanceOf(Wall::class, $plane);
        $this->assertFalse($plane->isWidthOnXAxis());
        $this->assertSame(29, $plane->width);
        $this->assertPositionSame(new Point(5, 2, 1), $plane->getStart());
        $this->assertPositionSame(new Point(5, 3, 30), $plane->getEnd());
    }

    public function testRampOnX2(): void
    {
        $pb = new PlaneBuilder();
        $points = [
            new Point(0, 0, 0),
            new Point(0, 0, 40),
            new Point(200, 200, 0),
            new Point(200, 200, 40),
        ];
        shuffle($points);

        $planes = $pb->fromQuad(...$points);
        $this->assertCount(400, $planes);
        $this->assertPositionSame(new Point(), $planes[0]->getStart());
        $this->assertPositionSame(new Point(200, 200, 40), $planes[399]->getEnd());
    }

    public function testRampJaggy(): void
    {
        $pb = new PlaneBuilder();
        $points = [
            new Point(0, 0, 0),
            new Point(0, 0, 40),
            new Point(200, 200, 0),
            new Point(200, 200, 40),
            9999.1,
        ];

        $planes = $pb->fromQuad(...$points);
        $this->assertCount(2, $planes);
        $this->assertPositionSame(new Point(), $planes[0]->getStart());
        $this->assertPositionSame(new Point(200, 0, 40), $planes[0]->getEnd());
        $this->assertPositionSame(new Point(200, 0, 0), $planes[1]->getStart());
        $this->assertPositionSame(new Point(200, 200, 40), $planes[1]->getEnd());
    }

}
