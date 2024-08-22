<?php

namespace cs\Event;

use cs\Core\Flame;
use cs\Core\GameException;
use cs\Core\Graph;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Util;
use cs\Core\World;
use cs\Enum\SoundType;
use cs\Interface\Flammable;
use cs\Interface\ForOneRoundMax;
use GraphPHP\Node\Node;
use SplQueue;

final class GrillEvent extends Event implements ForOneRoundMax
{
    private string $id;
    /** @var list<Flame> */
    public array $flames = [];
    private int $startedTickId;
    private int $lastFlameSpawnTickId;
    private int $spawnTickCount;
    private int $spawnFlameCount;
    private int $maxTicksCount;
    /** @var array<int,int> [playerId => tick] */
    private array $playerTickHits = [];
    private readonly int $damageCoolDownTickCount;
    private readonly int $maxFlameCount;

    public readonly Point $boundaryMin;
    public readonly Point $boundaryMax;

    /** @var SplQueue<Node> $queue */
    private SplQueue $queue;
    /** @var array<string,bool> */
    private array $visited = [];

    public function __construct(
        public readonly Player    $initiator,
        public readonly Flammable $item,
        private readonly World    $world,
        private readonly int      $flameRadius,
        private readonly int      $flameHeight,
        private readonly Graph    $graph,
        private readonly Point    $start,
    )
    {
        $flameArea = ($this->flameRadius * 2 + 1) ** 2;
        $this->spawnTickCount = Util::millisecondsToFrames(30);
        $this->spawnFlameCount = (int)ceil($this->item->getSpawnAreaMetersSquared() * 100 / $flameArea);
        $this->maxTicksCount = Util::millisecondsToFrames($this->item->getMaxTimeMs());
        $this->damageCoolDownTickCount = Util::millisecondsToFrames(100);
        $this->maxFlameCount = (int)ceil($this->item->getMaxAreaMetersSquared() / $flameArea);
        $this->startedTickId = $this->world->getTickId();

        $startNode = $this->graph->getNodeById($start->hash());
        if (null === $startNode) {
            throw new GameException("No node for start point: " . $start->hash());
        }

        $this->id = "grill-{$this->initiator->getId()}-{$this->world->getTickId()}";
        $this->boundaryMin = $start->clone();
        $this->boundaryMax = $start->clone();
        $this->queue = new SplQueue();
        $this->queue->enqueue($startNode);
        $this->igniteFlames();
    }

    private function extinguish(): void
    {
        for ($i = 1; $i <= $this->spawnFlameCount; $i++) {
            $flame = array_pop($this->flames);
            if ($flame === null) {
                return;
            }

            $sound = new SoundEvent($flame->center, SoundType::FLAME_EXTINGUISH);
            $sound->addExtra('fire', $this->id);
            $this->world->makeSound($sound);
        }
    }

    public function process(int $tick): void
    {
        if ([] === $this->flames) {
            $this->runOnCompleteHooks();
            return;
        }
        if ($tick >= $this->startedTickId + $this->maxTicksCount) {
            $this->extinguish();
            $this->world->checkFlameDamage($this, $tick);
            return;
        }

        if ($tick >= $this->lastFlameSpawnTickId + $this->spawnTickCount) {
            $this->igniteFlames();
        }

        $this->world->checkFlameDamage($this, $tick);
    }

    private function igniteFlames(): void
    {
        foreach ($this->loadFlames() as $candidate) {
            $this->boundaryMin->set(
                min($this->boundaryMin->x, $candidate->x - $this->flameRadius),
                min($this->boundaryMin->y, $candidate->y - 0),
                min($this->boundaryMin->z, $candidate->z - $this->flameRadius),
            );
            $this->boundaryMax->set(
                max($this->boundaryMax->x, $candidate->x + $this->flameRadius),
                max($this->boundaryMax->y, $candidate->y + $this->flameHeight),
                max($this->boundaryMax->z, $candidate->z + $this->flameRadius),
            );

            $flame = new Flame($candidate, $this->flameRadius, $this->flameHeight);
            $this->flames[] = $flame;
            $this->lastFlameSpawnTickId = $this->world->getTickId();
            $sound = new SoundEvent($flame->center, SoundType::FLAME_SPAWN);
            $sound->addExtra('fire', $this->id);
            $sound->addExtra('height', $flame->height);
            $sound->addExtra('size', $this->flameRadius * 2 + 1);
            $this->world->makeSound($sound);
        }
    }

    /** @return list<Point> */
    private function loadFlames(): array
    {
        $loadCount = $this->maxFlameCount - count($this->flames);

        $output = [];
        while (!$this->queue->isEmpty() && count($output) < min($this->spawnFlameCount, $loadCount)) {
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

    public function canHitPlayer(int $playerId, int $tickId): bool
    {
        return (($this->playerTickHits[$playerId] ?? 0) + $this->damageCoolDownTickCount <= $tickId);
    }

    public function playerHit(int $playerId, int $tickId): void
    {
        $this->playerTickHits[$playerId] = $tickId;
    }

    public function serialize(): array
    {
        return [
            'position' => $this->start->toArray(),
            'maxTime' => $this->item->getMaxTimeMs(),
            'maxFlames' => $this->maxFlameCount,
        ];
    }

}
