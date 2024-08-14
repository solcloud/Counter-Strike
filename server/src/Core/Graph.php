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

}
