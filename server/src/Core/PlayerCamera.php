<?php

namespace cs\Core;

class PlayerCamera
{
    private float $rotationHorizontal = 0.0;
    private float $rotationVertical = 0.0;

    public function look(float $angleHorizontal, float $angleVertical): void
    {
        $this->lookHorizontal($angleHorizontal);
        $this->lookVertical($angleVertical);
    }

    public function lookVertical(float $angle): void
    {
        if ($angle < -90) {
            $angle = -90;
        }
        if ($angle > 90) {
            $angle = 90;
        }

        $this->rotationVertical = $angle;
    }

    public function lookHorizontal(float $angle): void
    {
        $this->rotationHorizontal = Util::normalizeAngle($angle);
    }

    public function lookHorizontalOffset(float $angle): void
    {
        $this->lookHorizontal($this->rotationHorizontal + $angle);
    }

    public function getRotationHorizontal(): float
    {
        return $this->rotationHorizontal;
    }

    public function getRotationVertical(): float
    {
        return $this->rotationVertical;
    }

    /**
     * @return array<string,float>
     */
    public function toArray(): array
    {
        return [
            'horizontal' => $this->getRotationHorizontal(),
            'vertical'   => $this->getRotationVertical(),
        ];
    }


}
