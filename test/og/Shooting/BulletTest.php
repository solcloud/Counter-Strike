<?php

namespace Test\Shooting;

use cs\Core\Action;
use cs\Core\Floor;
use cs\Core\GameProperty;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Wall;
use cs\Enum\BuyMenuItem;
use cs\Event\AttackResult;
use cs\Weapon\RifleAk;
use Test\BaseTestCase;

class BulletTest extends BaseTestCase
{

    public function _testBulletHitFloor(int $angleHorizontal, int $angleVertical): AttackResult
    {
        $spawn = new Point(Action::playerGunHeightStand(), 0, Action::playerGunHeightStand());
        $playerCommands = [
            fn(Player $p) => $p->buyItem(BuyMenuItem::RIFLE_AK),
            fn(Player $p) => $p->setPosition($spawn),
            fn(Player $p) => $p->getSight()->lookAt($angleHorizontal, $angleVertical),
            $this->waitNTicks(RifleAk::equipReadyTimeMs) - 2,
            $this->endGame(),
        ];

        $game = $this->createGame([GameProperty::START_MONEY => 16000]);
        $game->getWorld()->addWall(new Wall(new Point(0, $spawn->y, $spawn->z + Action::playerGunHeightStand() + 1), true, 2 * $spawn->x));
        $game->getWorld()->addWall(new Wall(new Point($spawn->x + Action::playerGunHeightStand() + 1, $spawn->y, 0), false, 2 * $spawn->z));
        $game->getWorld()->addFloor(new Floor(new Point(0, Action::playerGunHeightStand() * 2, 0), PHP_INT_MAX, PHP_INT_MAX));
        $this->playPlayer($game, $playerCommands);

        $player = $game->getPlayer(1);
        $result = $player->attack();
        $this->assertInstanceOf(AttackResult::class, $result, "Angles [{$angleHorizontal},{$angleVertical}]");
        $this->assertCount(1, $result->getHits(), "Angles [{$angleHorizontal},{$angleVertical}]");
        $hitTarget = $result->getHits()[0];
        $this->assertTrue(($hitTarget instanceof Wall || $hitTarget instanceof Floor), "Angles [{$angleHorizontal},{$angleVertical}]");
        $this->assertGreaterThanOrEqual(Action::playerGunHeightStand(), $result->getBullet()->getDistanceTraveled(), "Angles [{$angleHorizontal},{$angleVertical}]");
        $this->assertLessThan(sqrt(pow(Action::playerGunHeightStand(), 2) + pow(Action::playerGunHeightStand(), 2)), $result->getBullet()->getDistanceTraveled(), "Angles [{$angleHorizontal},{$angleVertical}]");
        if ($angleVertical < 0) {
            $this->assertLessThan($spawn->y + Action::playerGunHeightStand(), $result->getBullet()->getPosition()->y, "Angles [{$angleHorizontal},{$angleVertical}]");
        } elseif ($angleVertical === 0) {
            $this->assertSame($spawn->y + Action::playerGunHeightStand(), $result->getBullet()->getPosition()->y, "Angles [{$angleHorizontal},{$angleVertical}]");
        } else {
            $this->assertGreaterThan($spawn->y + Action::playerGunHeightStand(), $result->getBullet()->getPosition()->y, "Angles [{$angleHorizontal},{$angleVertical}]");
        }
        if ($angleVertical % 90 === 0) {
            return $result;
        }

        if (($angleHorizontal > 0 && $angleHorizontal < 90) || $angleHorizontal > 270) {
            $this->assertGreaterThan($spawn->z, $result->getBullet()->getPosition()->z, "Angles [{$angleHorizontal},{$angleVertical}]");
        } elseif ($angleHorizontal === 0) {
            $this->assertSame($spawn->x, $result->getBullet()->getPosition()->x, "Angles [{$angleHorizontal},{$angleVertical}]");
        } else {
            $this->assertLessThan($spawn->z, $result->getBullet()->getPosition()->z, "Angles [{$angleHorizontal},{$angleVertical}]");
        }
        return $result;
    }

    public function testBulletHitFloor(): void
    {
        $this->_testBulletHitFloor(0, -89);

        $maxDistance = (int)floor(sqrt(pow(Action::playerGunHeightStand(), 2) + pow(Action::playerGunHeightStand(), 2)));
        $result = $this->_testBulletHitFloor(45, -45);
        $this->assertSame($maxDistance, $result->getBullet()->getDistanceTraveled());
        $result = $this->_testBulletHitFloor(45, 45);
        $this->assertSame($maxDistance, $result->getBullet()->getDistanceTraveled());
        $floor = $result->getHits()[0];
        $this->assertInstanceOf(Floor::class, $floor);

        $result = $this->_testBulletHitFloor(0, -90);
        $this->assertSame(Action::playerGunHeightStand(), $result->getBullet()->getDistanceTraveled());
        $result = $this->_testBulletHitFloor(1, -90);
        $this->assertSame(Action::playerGunHeightStand(), $result->getBullet()->getDistanceTraveled());

        $result1 = $this->_testBulletHitFloor(1, -89);
        $result2 = $this->_testBulletHitFloor(1, -79);
        $this->assertGreaterThan($result1->getBullet()->getDistanceTraveled(), $result2->getBullet()->getDistanceTraveled());

        $verticals = [-90, -89, -81, -70, -61, -32, -12, -2, -1, 0, 1, 2, 7, 29, 54, 89, 90];
        $horizontals = [0, 9, 27, 61, 92, 149, 179, 197, 228, 276, 312, 358];
        foreach ($verticals as $vertical) {
            foreach ($horizontals as $horizontal) {
                $this->_testBulletHitFloor($horizontal, $vertical);
            }
        }
    }

}
