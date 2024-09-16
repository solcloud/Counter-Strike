<?php

namespace cs\Event;

use cs\Core\Column;
use cs\Core\GameException;
use cs\Core\Point;
use cs\Enum\SoundType;
use cs\Equipment\Smoke;

final class SmokeEvent extends VolumetricEvent
{

    private int $maxHeight = Smoke::MAX_HEIGHT;

    protected function setup(): void
    {
        if (min(Smoke::MAX_CORNER_HEIGHT, Smoke::MAX_HEIGHT) < $this->partHeight) {
            throw new GameException('Part height is too high'); // @codeCoverageIgnore
        }
    }

    protected function shrinkPart(Column $column): void
    {
        $soundEvent = new SoundEvent($column->center, SoundType::SMOKE_FADE);
        $soundEvent->addExtra('id', $this->id);
        $this->world->makeSound($soundEvent);

        $this->parts = []; // just do single shrink event
    }

    protected function expandPart(Point $center): Column
    {
        $count = count($this->parts);
        if ($count > 10 && $count % 2 === 0) {
            $this->maxHeight = max(Smoke::MAX_CORNER_HEIGHT, $this->maxHeight - 1);
        }

        $height = $this->partHeight;
        $candidate = $center->clone()->addY($height);
        for ($i = $height; $i <= $this->maxHeight; $i++) {
            $candidate->addY(1);
            if ($this->world->findFloorSquare($candidate, $this->partRadius)) {
                break;
            }
            $height++;
        }

        $column = new Column($center, $this->partRadius, $height);
        $soundEvent = new SoundEvent($column->center, SoundType::SMOKE_SPAWN);
        $soundEvent->addExtra('id', $this->id);
        $soundEvent->addExtra('height', $column->height);
        $this->world->makeSound($soundEvent);

        $this->world->smokeTryToExtinguishFlames($column);

        return $column;
    }

}
