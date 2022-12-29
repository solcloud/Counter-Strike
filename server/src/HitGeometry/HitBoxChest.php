<?php

namespace cs\HitGeometry;

use cs\Core\Player;
use cs\Core\Point;

class HitBoxChest extends SphereGroupHitBox
{
    public function __construct()
    {
        parent::__construct(function (Player $player): Point {
            return (new Point())->addY($player->getHeadHeight());
        });

        $this->createChestLeft();
        $this->createChestRight();
        $this->addHitBox(new Point(-0, -30, -1), 6);
        $this->createLimbLeft();
        $this->createLimbRight();
    }

    private function createChestLeft(): void
    {
        $this->addHitBox(new Point(-12, -50, -1), 5);
        $this->addHitBox(new Point(-12, -42, -1), 5);
        $this->addHitBox(new Point(-13, -46, 5), 6);
        $this->addHitBox(new Point(-4, -43, 7), 6);
        $this->addHitBox(new Point(-13, -41, 3), 6);
        $this->addHitBox(new Point(-5, -37, 2), 6);
        $this->addHitBox(new Point(-15, -35, -1), 6);
        $this->addHitBox(new Point(-7, -32, -1), 6);
        $this->addHitBox(new Point(-3, -25, -4), 3);
        $this->addHitBox(new Point(-3, -27, 3), 3);
        $this->addHitBox(new Point(-4, -26, -1), 3);
    }

    private function createChestRight(): void
    {
        $this->addHitBox(new Point(12, -50, -1), 5);
        $this->addHitBox(new Point(12, -42, -1), 5);
        $this->addHitBox(new Point(13, -46, 5), 6);
        $this->addHitBox(new Point(4, -43, 7), 6);
        $this->addHitBox(new Point(13, -41, 3), 6);
        $this->addHitBox(new Point(5, -37, 2), 6);
        $this->addHitBox(new Point(15, -35, -1), 6);
        $this->addHitBox(new Point(7, -32, -1), 6);
        $this->addHitBox(new Point(3, -25, -4), 3);
        $this->addHitBox(new Point(3, -27, 3), 3);
        $this->addHitBox(new Point(4, -26, -1), 3);
    }

    private function createLimbLeft(): void
    {
        $this->addHitBox(new Point(-16, -35, -4), 5);
        $this->addHitBox(new Point(-17, -40, -1), 5);
        $this->addHitBox(new Point(-19, -36, 0), 5);
        $this->addHitBox(new Point(-22, -35, 6), 5);
        $this->addHitBox(new Point(-23, -37, 11), 5);
        $this->addHitBox(new Point(-25, -40, 16), 5);
        $this->addHitBox(new Point(-28, -43, 21), 5);
        $this->addHitBox(new Point(-22, -41, 6), 5);
        $this->addHitBox(new Point(-23, -44, 11), 5);
        $this->addHitBox(new Point(-26, -45, 16), 5);
        $this->addHitBox(new Point(-28, -47, 20), 5);
        $this->addHitBox(new Point(-24, -47, 24), 5);
        $this->addHitBox(new Point(-19, -47, 27), 4);
        $this->addHitBox(new Point(-15, -47, 31), 4);
        $this->addHitBox(new Point(-10, -48, 34), 3);
    }

    private function createLimbRight(): void
    {
        $this->addHitBox(new Point(19, -38, -2), 7);
        $this->addHitBox(new Point(21, -42, 3), 7);
        $this->addHitBox(new Point(25, -47, 8), 6);
        $this->addHitBox(new Point(29, -52, 13), 6);
        $this->addHitBox(new Point(29, -53, 18), 6);
        $this->addHitBox(new Point(26, -52, 24), 5);
        $this->addHitBox(new Point(23, -52, 28), 4);
        $this->addHitBox(new Point(22, -52, 32), 3);
    }

}
