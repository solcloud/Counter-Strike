<?php

namespace Test\World;

use cs\Core\Box;
use cs\Core\Floor;
use cs\Core\GameException;
use cs\Core\PathFinder;
use cs\Core\Point;
use cs\Core\World;
use Test\BaseTestCase;

final class NavigationMeshTest extends BaseTestCase
{

    public function testConvertPointToNavMeshPoint(): void
    {
        $data = [
            3 => [
                ['2,0,2', new Point(1, 0, 1)],
                ['2,0,2', new Point(2, 0, 2)],
                ['2,0,2', new Point(3, 0, 3)],
                ['5,0,2', new Point(4, 0, 1)],
                ['5,0,2', new Point(5, 0, 1)],
                ['5,0,2', new Point(6, 0, 1)],
                ['8,0,5', new Point(9, 0, 4)],
            ],
            31 => [
                ['16,0,16', new Point(3, 0, 1)],
                ['47,0,16', new Point(32, 0, 2)],
                ['47,0,16', new Point(42, 0, 17)],
                ['47,0,47', new Point(42, 0, 59)],
                ['47,0,47', new Point(59, 0, 59)],
                ['47,0,47', new Point(59, 0, 59)],
                ['326,333,326', new Point(333, 333, 333)],
                ['450,0,295', new Point(450, 0, 285)],
                ['450,0,295', new Point(461, 0, 285)],
                ['1566,50,16', new Point(1570, 50, 26)],
            ],
        ];
        $world = new World($this->createTestGame());
        foreach ($data as $tileSize => $tests) {
            $finder = new PathFinder($world, $tileSize, 10);
            foreach ($tests as [$expected, $point]) {
                $msg = "Size {$tileSize} point {$point->hash()}";
                $finder->convertToNavMeshNode($point);
                $this->assertSame($expected, $point->hash(), $msg);
            }
        }
    }

    public function testSimple(): void
    {
        $game = $this->createTestGame();
        $game->getWorld()->addBox(new Box(new Point(), 10, 1000, 10));
        $game->getTestMap()->startPointForNavigationMesh->set(5, 0, 5);

        $path = $game->getWorld()->buildNavigationMesh(3, 100);
        $this->assertSame(9, $path->getGraph()->getNodesCount());
        $this->assertSame(24, $path->getGraph()->getEdgeCount());

        $start = new Point(4, 0, 1);
        $path->convertToNavMeshNode($start);
        $startNode = $path->getGraph()->getNodeById($start->hash());
        $this->assertNotNull($startNode);
        $this->assertCount(3, $path->getGraph()->getNeighbors($startNode));
        $path->getGraph()->generateNeighbors();
        $this->assertCount(3, $path->getGraph()->getGeneratedNeighbors($startNode->getId()));
        foreach ($path->getGraph()->getGeneratedNeighbors($startNode->getId()) as $neighbor) {
            $path->getGraph()->removeNodeById($neighbor->getId());
        }
        $this->expectException(GameException::class);
        $path->getGraph()->getGeneratedNeighbors($startNode->getId());
    }

    public function testBoundary(): void
    {
        $game = $this->createTestGame();
        $game->getWorld()->addBox(new Box(new Point(), 10, 1000, 10));
        $boxPoint = new Point(5, 0, 1);
        $game->getWorld()->addBox(new Box($boxPoint, 10, 1000, 10));
        $game->getTestMap()->startPointForNavigationMesh->set(1, 0, 9);
        $path = $game->getWorld()->buildNavigationMesh(3, 100);

        $candidate = $boxPoint->clone()->addX(-1)->setZ(5);
        $this->assertNull($path->getGraph()->getNodeById($candidate->hash()));

        $closestCandidate = $candidate->clone();
        $path->convertToNavMeshNode($closestCandidate);
        $this->assertNull($path->getGraph()->getNodeById($closestCandidate->hash()));

        $validPoint = $path->findTile($candidate, 1);
        $this->assertLessThan($closestCandidate->x, $validPoint->x);
        $this->assertNotNull($path->getGraph()->getNodeById($validPoint->hash()));
        $this->assertSame('2,0,5', $validPoint->hash());

        $orig = $candidate->clone();
        $candidate->addX(-1);
        $validPoint = $path->findTile($candidate, 1);
        $this->assertLessThan($closestCandidate->x, $validPoint->x);
        $this->assertNotNull($path->getGraph()->getNodeById($validPoint->hash()));
        $this->assertSame('2,0,5', $validPoint->hash());

        $this->assertNotSame('2,0,5', $candidate->hash());
        $path->convertToNavMeshNode($candidate);
        $this->assertSame('2,0,5', $candidate->hash());
        $this->assertPositionNotSame($orig, $candidate);
    }

    public function testBoundaryAbove(): void
    {
        $game = $this->createTestGame();
        $game->getWorld()->addBox(new Box(new Point(), 10, 1000, 10));
        $start = new Point(1, 1, 1);
        $game->getWorld()->addBox(new Box($start->clone(), 1, 1, 10));
        $game->getTestMap()->startPointForNavigationMesh->setFrom($start);
        $path = $game->getWorld()->buildNavigationMesh(3, 100);

        $candidate = $start->clone()->setY(0);
        $this->assertNull($path->getGraph()->getNodeById($candidate->hash()));

        $closestCandidate = $candidate->clone();
        $path->convertToNavMeshNode($closestCandidate);
        $this->assertNull($path->getGraph()->getNodeById($closestCandidate->hash()));

        $validPoint = $path->findTile($candidate, 1);
        $this->assertNotNull($path->getGraph()->getNodeById($validPoint->hash()));
        $this->assertSame('2,1,2', $validPoint->hash());
    }

    public function testNeighboursShareSameNodeReference(): void
    {
        $game = $this->createTestGame();
        $game->getTestMap()->startPointForNavigationMesh->set(1, 0, 1);
        $game->getWorld()->addBox(new Box(new Point(), 7, 1000, 4));
        $path = $game->getWorld()->buildNavigationMesh(3, 10);
        $graph = $path->getGraph();
        $this->assertSame(2, $graph->getNodesCount());
        $this->assertSame(2, $graph->getEdgeCount());
        $node1 = $graph->getNodeById('2,0,2');
        $this->assertNotNull($node1);
        $node2 = $graph->getNodeById('5,0,2');
        $this->assertNotNull($node2);
        $node1neighbours = $graph->getNeighbors($node1);
        $node2neighbours = $graph->getNeighbors($node2);
        $this->assertCount(1, $node1neighbours);
        $this->assertCount(1, $node2neighbours);
        $this->assertSame($node1, $node2neighbours[0]);
        $this->assertSame($node2, $node1neighbours[0]);
    }

    public function testWallBoundary(): void
    {
        $game = $this->createTestGame();
        $game->getTestMap()->startPointForNavigationMesh->set(1, 0, 1);
        $game->getWorld()->addBox(new Box(new Point(), 5, 1000, 4));
        $path = $game->getWorld()->buildNavigationMesh(3, 10);
        $graph = $path->getGraph();
        $this->assertSame(1, $graph->getNodesCount());
        $this->assertSame(0, $graph->getEdgeCount());

        $node = $graph->getNodeById('2,0,2');
        $this->assertNotNull($node);
        $this->assertCount(0, $graph->getNeighbors($node));
    }

    public function testUnderNavMesh(): void
    {
        $game = $this->createTestGame();
        $expectedPoint = new Point(2, 2, 2);
        $game->getTestMap()->startPointForNavigationMesh->set(1, 2, 1);
        $game->getWorld()->addBox(new Box(new Point(), 5, 1000, 4));
        $game->getWorld()->addFloor(new Floor(new Point(2, 2, 0), 10, 10));

        $path = $game->getWorld()->buildNavigationMesh(3, 10);
        $graph = $path->getGraph();

        $nodes = $graph->getNodes();
        $this->assertCount(1, $nodes);
        $node = $graph->getNodeById('2,2,2');
        $this->assertNotNull($node);
        $this->assertSame($node, array_shift($nodes));
        $nodePosition = $node->getData();
        $this->assertInstanceOf(Point::class, $nodePosition);
        $this->assertPositionSame($expectedPoint, $nodePosition);

        $tilePoint = $path->findTile(new Point(1, 0, 1), 1);
        $this->assertPositionSame($expectedPoint, $tilePoint);
    }

    public function testDeepHole(): void
    {
        $game = $this->createTestGame();
        $game->getTestMap()->startPointForNavigationMesh->set(1, 1000, 1);
        $game->getWorld()->addBox(new Box(new Point(), 10, 2000, 10));
        $game->getWorld()->addFloor(new Floor(new Point(1, 1000, 1), 1, 1));

        $path = $game->getWorld()->buildNavigationMesh(3, 10);
        $this->assertSame(1, $path->getGraph()->getNodesCount());
        $this->assertSame(0, $path->getGraph()->getEdgeCount());
    }

    public function testOneWayDirection(): void
    {
        $game = $this->createTestGame();
        $height = $game->getWorld()::GRENADE_NAVIGATION_MESH_OBJECT_HEIGHT;
        $doubleHeight = $height * 2;
        $game->getWorld()->addBox(new Box(new Point(), 10, 1000, 10));

        $game->getWorld()->addBox(new Box(new Point(7, 0, 0), 10, $doubleHeight, 10));
        $game->getWorld()->addBox(new Box(new Point(7, 0, 0), 10, 1000, 10));
        $game->getTestMap()->startPointForNavigationMesh->set(8, $doubleHeight, 8);

        $path = $game->getWorld()->buildNavigationMesh(3, $height);
        $this->assertSame(9, $path->getGraph()->getNodesCount());
        $this->assertSame(21, $path->getGraph()->getEdgeCount());

        $start = new Point(4, 0, 1);
        $path->convertToNavMeshNode($start);
        $startNode = $path->getGraph()->getNodeById($start->hash());
        $this->assertNotNull($startNode);
        $this->assertCount(2, $path->getGraph()->getNeighbors($startNode));

        $stepNode = $path->getGraph()->getNodeById((new Point(8, $doubleHeight, 8))->hash());
        $this->assertNotNull($stepNode);
        $this->assertSame(['path' => [], 'cost' => INF], $path->getGraph()->shortestPathDijkstra($startNode, $stepNode));
        $this->assertSame(['path' => ["8,{$doubleHeight},8", '5,0,8', '5,0,5', '5,0,2'], 'cost' => 3.0], $path->getGraph()->shortestPathDijkstra($stepNode, $startNode));

        $skyNode = $path->getGraph()->getNodeById((new Point(8, 1000, 4))->hash());
        $this->assertNull($skyNode);
    }

}
