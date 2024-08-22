<?php

namespace cs\Core;

use GraphPHP\Graph\DiGraph;
use GraphPHP\Node\Node;

final class Graph extends DiGraph
{
    /** @var array<string,string[]> */
    private array $neighbors;

    public function getNodesCount(): int
    {
        return count($this->nodes);
    }

    public function getEdgeCount(): int
    {
        return count($this->edges);
    }

    /** @return Node[] */
    public function getGeneratedNeighbors(string $nodeId): array
    {
        $neighbors = [];
        foreach ($this->neighbors[$nodeId] ?? [] as $nodeKey) {
            $neighbors[] = $this->getNodeById($nodeKey) ?? throw new GameException("Node '{$nodeKey}' not found");
        }

        return $neighbors;
    }

    public function generateNeighbors(): void
    {
        $this->neighbors = [];
        foreach ($this->edges as $edge) {
            $this->neighbors[$edge->getSource()->getId()][] = $edge->getTarget()->getId();
        }
    }

    /**
     * @return array<string,string[]>
     * @internal
     */
    public function internalGetGeneratedNeighbors(): array
    {
        return $this->neighbors;
    }

}
