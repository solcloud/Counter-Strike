<?php

namespace cs\Event;

use cs\Core\Column;
use cs\Core\Point;
use cs\Enum\SoundType;
use cs\Interface\Flammable;

final class GrillEvent extends VolumetricEvent
{
    public const DAMAGE_COOL_DOWN_TIME_MS = 100;

    /** @var array<int,int> [playerId => tick] */
    private array $playerTickHits = [];
    private int $damageCoolDownTickCount;

    protected function setup(): void
    {
        $this->damageCoolDownTickCount = $this->timeMsToTick(self::DAMAGE_COOL_DOWN_TIME_MS);
    }

    protected function onProcess(int $tick): void
    {
        $this->world->checkFlameDamage($this, $tick);
    }

    protected function shrinkPart(Column $column): void
    {
        $sound = new SoundEvent($column->center, SoundType::FLAME_EXTINGUISH);
        $sound->addExtra('id', $this->id);
        $this->world->makeSound($sound);
    }

    protected function expandPart(Point $center): Column
    {
        $flame = new Column($center, $this->partRadius, $this->partHeight);
        if ($this->world->flameCanIgnite($flame)) {
            $sound = new SoundEvent($flame->center, SoundType::FLAME_SPAWN);
            $sound->addExtra('id', $this->id);
            $sound->addExtra('height', $flame->height);
            $this->world->makeSound($sound);
        } else {
            $flame->active = false;
        }

        return $flame;
    }

    public function extinguish(Column $flame): void
    {
        if (!$flame->active) {
            return;
        }

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

    public function getItem(): Flammable
    {
        return parent::getItem(); // @phpstan-ignore return.type
    }
}
