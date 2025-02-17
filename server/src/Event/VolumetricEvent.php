<?php

namespace cs\Event;

use cs\Core\Column;
use cs\Core\GameException;
use cs\Core\Graph;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Sequence;
use cs\Core\World;
use cs\Interface\ForOneRoundMax;
use cs\Interface\Volumetric;
use GraphPHP\Node\Node;
use SplQueue;

abstract class VolumetricEvent extends Event implements ForOneRoundMax
{
    public readonly string $id;
    private readonly int $startedTickId;
    private readonly int $spawnTickCount;
    private readonly int $spawnPartCount;
    private readonly int $maxTicksCount;
    private readonly int $partSize;
    private readonly int $maxPartCount;

    /** @var Column[] */
    public array $parts = [];
    private int $lastPartSpawnTickId;

    public readonly Point $boundaryMin;
    public readonly Point $boundaryMax;

    /** @var SplQueue<Node> $queue */
    private SplQueue $queue;
    /** @var array<string,bool> */
    private array $visited = [];

    public function __construct(
        public readonly Player     $initiator,
        public readonly Volumetric $item,
        protected readonly World   $world,
        protected readonly int     $partRadius,
        protected readonly int     $partHeight,
        private readonly Graph     $graph,
        private readonly Point     $start,
    )
    {
        $startNode = $this->graph->getNodeById($start->hash());
        if (null === $startNode) {
            throw new GameException("No node for start point: " . $start->hash()); // @codeCoverageIgnore
        }

        $this->id = Sequence::next();
        $this->partSize = $this->partRadius * 2 + 1;
        $this->startedTickId = $this->world->getTickId();
        $this->spawnTickCount = $this->timeMsToTick(20);
        $this->maxTicksCount = $this->timeMsToTick($this->item->getMaxTimeMs());

        $partArea = ($this->partSize) ** 2;
        $this->spawnPartCount = (int)ceil($this->item->getSpawnAreaMetersSquared() * 100 / $partArea);
        $this->maxPartCount = (int)ceil($this->item->getMaxAreaMetersSquared() / $partArea);

        $this->setup();
        $this->queue = new SplQueue();
        $this->queue->enqueue($startNode);

        $this->boundaryMin = $start->clone();
        $this->boundaryMax = $start->clone()->addPart(1, 1, 1);
    }

    protected abstract function setup(): void;

    private function shrink(int $tick): void
    {
        for ($i = 1; $i <= $this->spawnPartCount; $i++) {
            $part = array_pop($this->parts);
            if ($part === null) {
                return;
            }

            if ($part->active) {
                $this->shrinkPart($part);
            }
        }

        $this->onProcess($tick);
    }

    protected abstract function shrinkPart(Column $column): void;

    protected abstract function expandPart(Point $center): Column;

    protected function onProcess(int $tick): void
    {
        // empty hook
    }

    public function process(int $tick): void
    {
        if ($this->startedTickId + 1 === $tick) { // initial expand on "first" tick so we get base event fired first (in constructor started tick)
            $this->expand($tick);
        }
        if ([] === $this->parts) {
            $this->runOnCompleteHooks();
            return;
        }
        if ($tick >= $this->startedTickId + $this->maxTicksCount) {
            $this->shrink($tick);
            return;
        }

        if ($tick >= $this->lastPartSpawnTickId + $this->spawnTickCount) {
            $this->expand($tick);
        }

        $this->onProcess($tick);
    }

    private function expand(int $tick): void
    {
        $candidates = $this->loadParts();
        if ($candidates === []) {
            $this->lastPartSpawnTickId = $tick + $this->maxTicksCount;
            return;
        }

        foreach ($candidates as $candidate) {
            $part = $this->expandPart($candidate);

            $this->boundaryMin->set(
                min($this->boundaryMin->x, $candidate->x - $part->radius),
                min($this->boundaryMin->y, $candidate->y - 0),
                min($this->boundaryMin->z, $candidate->z - $part->radius),
            );
            $this->boundaryMax->set(
                max($this->boundaryMax->x, $candidate->x + $part->radius),
                max($this->boundaryMax->y, $candidate->y + $part->height),
                max($this->boundaryMax->z, $candidate->z + $part->radius),
            );

            $this->parts[] = $part;
        }
        $this->lastPartSpawnTickId = $tick;
    }

    /** @return Point[] */
    private function loadParts(): array
    {
        $loadCount = $this->maxPartCount - count($this->parts);

        $output = [];
        while (!$this->queue->isEmpty() && count($output) < min($this->spawnPartCount, $loadCount)) {
            $current = $this->queue->dequeue();
            $currentKey = $current->getId();
            if (array_key_exists($currentKey, $this->visited)) {
                continue;
            }

            $this->visited[$currentKey] = true;
            /** @var Point $point */
            $point = $current->getData();
            $output[] = $point;

            foreach ($this->graph->getGeneratedNeighbors($currentKey) as $node) {
                $this->queue->enqueue($node);
            }
        }

        return $output;
    }

    public function getItem(): Volumetric
    {
        return $this->item;
    }

    /** @codeCoverageIgnore */
    #[\Override]
    public function serialize(): array
    {
        return [
            'id' => $this->id,
            'size' => $this->partSize,
            'position' => $this->start->toArray(),
            'time' => $this->item->getMaxTimeMs(),
            'count' => $this->maxPartCount,
        ];
    }

}
