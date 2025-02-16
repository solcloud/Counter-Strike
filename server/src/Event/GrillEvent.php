<?php

namespace cs\Event;

use cs\Core\Column;
use cs\Core\Point;
use cs\Core\Setting;
use cs\Enum\SoundType;

final class GrillEvent extends VolumetricEvent
{
    public const int DAMAGE_COOL_DOWN_TIME_MS = 100;

    /** @var array<int,int> [playerId => tick] */
    private array $playerTickHits = [];
    private int $damageCoolDownTickCount;

    protected function setup(): void
    {
        $this->damageCoolDownTickCount = $this->timeMsToTick(self::DAMAGE_COOL_DOWN_TIME_MS);
    }

    #[\Override]
    protected function onProcess(int $tick): void
    {
        $this->world->checkFlameDamage($this, $tick);
    }

    protected function shrinkPart(Column $column): void
    {
        $soundEvent = new SoundEvent($column->center, SoundType::FLAME_EXTINGUISH);
        $soundEvent->addExtra('id', $this->id);
        $this->world->makeSound($soundEvent);
    }

    protected function expandPart(Point $center): Column
    {
        assert($this->partHeight < Setting::playerHeadHeightCrouch());
        $flame = new Column($center, $this->partRadius, $this->partHeight);
        if ($this->world->flameCanIgnite($flame)) {
            $soundEvent = new SoundEvent($flame->center, SoundType::FLAME_SPAWN);
            $soundEvent->addExtra('id', $this->id);
            $soundEvent->addExtra('height', $flame->height);
            $this->world->makeSound($soundEvent);
        } else {
            $flame->active = false;
        }

        return $flame;
    }

    public function extinguish(Column $flame): void
    {
        $flame->active = false;
        $this->shrinkPart($flame);
    }

    public function canHitPlayer(int $playerId, int $tickId): bool
    {
        return (($this->playerTickHits[$playerId] ?? 0) + $this->damageCoolDownTickCount <= $tickId);
    }

    public function playerHit(int $playerId, int $tickId): void
    {
        $this->playerTickHits[$playerId] = $tickId;
    }

}
