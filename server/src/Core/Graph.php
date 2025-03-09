<?php

namespace cs\Core;

use GraphPHP\Graph\DiGraph;

final class Graph extends DiGraph
{
    public function getNodesCount(): int
    {
        return count($this->nodes);
    }

    public function getEdgeCount(): int
    {
        return count($this->edges);
    }

    /** @return array<string,list<string>> */
    public function generateNeighbors(): array
    {
        $neighbors = [];
        foreach ($this->edges as $edge) {
            $neighbors[$edge->getSource()->getId()][] = $edge->getTarget()->getId();
        }

        return $neighbors;
    }

}
