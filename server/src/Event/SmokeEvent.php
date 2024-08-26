<?php

namespace cs\Event;

use cs\Core\Column;
use cs\Core\GameException;
use cs\Core\Point;
use cs\Enum\SoundType;

final class SmokeEvent extends VolumetricEvent
{
    public const MAX_HEIGHT = 350;
    public const MAX_CORNER_HEIGHT = 270;

    private int $maxHeight = self::MAX_HEIGHT;

    protected function setup(): void
    {
        if (min(self::MAX_CORNER_HEIGHT, self::MAX_HEIGHT) < $this->partHeight) {
            throw new GameException('Part height is too high'); // @codeCoverageIgnore
        }
    }

    protected function shrinkPart(Column $column): void
    {
        $sound = new SoundEvent($column->center, SoundType::SMOKE_FADE);
        $sound->addExtra('id', $this->id);
        $this->world->makeSound($sound);

        $this->parts = []; // just do single shrink event
    }

    protected function expandPart(Point $center): Column
    {
        $count = count($this->parts);
        if ($count > 10 && $count % 2 === 0) {
            $this->maxHeight = max(self::MAX_CORNER_HEIGHT, $this->maxHeight - 1);
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
        $sound = new SoundEvent($column->center, SoundType::SMOKE_SPAWN);
        $sound->addExtra('id', $this->id);
        $sound->addExtra('height', $column->height);
        $this->world->makeSound($sound);

        $this->world->smokeTryToExtinguishFlames($column);

        return $column;
    }

}
