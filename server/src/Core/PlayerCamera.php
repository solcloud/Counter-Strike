<?php

namespace cs\Core;

class PlayerCamera
{
    private int $rotationHorizontal = 0;
    private int $rotationVertical = 0;

    public function reset(): void
    {
        $this->rotationVertical = $this->rotationHorizontal = 0;
    }

    public function lookAt(int $angleHorizontal, int $angleVertical): void
    {
        $this->lookHorizontal($angleHorizontal);
        $this->lookVertical($angleVertical);
    }

    public function lookVertical(int $angle): void
    {
        if ($angle < -90) {
            $angle = -90;
        }
        if ($angle > 90) {
            $angle = 90;
        }

        $this->rotationVertical = $angle;
    }

    private function normalizeAngle(int $angle): int
    {
        return Util::normalizeAngle($angle);
    }

    public function lookHorizontal(int $angle): void
    {
        $this->rotationHorizontal = $this->normalizeAngle($angle);
    }

    public function lookHorizontalOffset(int $angle): void
    {
        $this->lookHorizontal($this->rotationHorizontal + $angle);
    }

    public function getRotationHorizontal(): int
    {
        return $this->rotationHorizontal;
    }

    public function getRotationVertical(): int
    {
        return $this->rotationVertical;
    }

    /**
     * @return array<string,int>
     */
    public function toArray()
    {
        return [
            "horizontal" => $this->getRotationHorizontal(),
            "vertical"   => $this->getRotationVertical(),
        ];
    }


}
