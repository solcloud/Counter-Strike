<?php

namespace cs\Event;

use cs\Core\Column;
use cs\Core\NavigationMesh;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Sequence;
use cs\Core\World;
use cs\Interface\ForOneRoundMax;
use cs\Interface\Volumetric;
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

    protected readonly int $partRadius;
    protected readonly int $partHeight;

    public readonly Point $boundaryMin;
    public readonly Point $boundaryMax;

    /** @var SplQueue<string> $queue */
    private SplQueue $queue;
    /** @var array<string,bool> */
    private array $visited = [];

    public function __construct(
        public readonly Player          $initiator,
        public readonly Volumetric      $item,
        protected readonly World        $world,
        private readonly NavigationMesh $navmesh,
        private readonly Point          $start,
    )
    {
        $this->id = Sequence::next();
        $this->partRadius = $this->navmesh->tileSizeHalf;
        $this->partSize = $this->partRadius * 2 + 1;
        $this->partHeight = $this->navmesh->colliderHeight;
        $this->startedTickId = $this->world->getTickId();
        $this->spawnTickCount = $this->timeMsToTick(20);
        $this->maxTicksCount = $this->timeMsToTick($this->item->getMaxTimeMs());

        $partArea = ($this->partSize) ** 2;
        $this->spawnPartCount = (int)ceil($this->item->getSpawnAreaMetersSquared() * 100 / $partArea);
        $this->maxPartCount = (int)ceil($this->item->getMaxAreaMetersSquared() / $partArea);

        $this->setup();
        $this->queue = new SplQueue();
        $this->queue->enqueue($start->hash());

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
            $currentKey = $this->queue->dequeue();
            if (array_key_exists($currentKey, $this->visited)) {
                continue;
            }

            $this->visited[$currentKey] = true;
            $output[] = Point::fromHash($currentKey);

            foreach ($this->navmesh->getGeneratedNeighbors($currentKey) as $nodeKey) {
                $this->queue->enqueue($nodeKey);
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
