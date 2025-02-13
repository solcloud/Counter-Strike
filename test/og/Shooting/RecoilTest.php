<?php

namespace Test\Shooting;

use cs\Core\GameState;
use cs\Core\Point;
use cs\Core\Wall;
use cs\Enum\BuyMenuItem;
use cs\Weapon\PistolGlock;
use cs\Weapon\RifleAk;
use Test\BaseTestCase;

class RecoilTest extends BaseTestCase
{

    public function testAkRecoil(): void
    {
        $previousBulletEndPosition = new Point();
        $game = $this->createNoPauseGame();
        $game->getPlayer(1)->getInventory()->earnMoney(16000);
        $game->getPlayer(1)->buyItem(BuyMenuItem::RIFLE_AK);
        $game->getPlayer(1)->setPosition(new Point(500, 0, 500));
        $game->getPlayer(1)->getSight()->look(180, 0);
        $ak = $game->getPlayer(1)->getEquippedItem();
        $this->assertInstanceOf(RifleAk::class, $ak);

        $bulletCount = 0;
        $game->onTick(function (GameState $state) use (&$previousBulletEndPosition, &$bulletCount, $ak) {
            $result = $state->getPlayer(1)->attack();
            if ($result) {
                $bp = $result->getBullet()->getPosition();
                $this->assertFalse($bp->equals($previousBulletEndPosition), "Magazine '{$ak->getAmmo()}', Tick '{$state->getTickId()}'");
                $previousBulletEndPosition = $bp->clone();
                $bulletCount++;
            }
            if ($ak->getAmmo() === 0) {
                $state->getPlayer(1)->suicide();
            }
        });

        $game->start();
        $this->assertSame($ak::magazineCapacity, $bulletCount);
        $this->assertNull($game->getPlayer(1)->attack());
    }

    public function testRifleMovementRecoil(): void
    {
        $game = $this->createNoPauseGame();
        $game->getWorld()->addWall(new Wall(new Point(0, 0, 900), true, 99999));
        $player = $game->getPlayer(1);
        $player->setPosition($player->getPositionClone()->addZ($player->getBoundingRadius()));
        $player->getInventory()->earnMoney(16000);
        $player->buyItem(BuyMenuItem::RIFLE_AK);
        $ak = $player->getEquippedItem();
        $this->assertInstanceOf(RifleAk::class, $ak);
        $py = $player->getPositionClone()->y + $player->getSightHeight();

        $bulletsYCoords = [];
        $game->onTick(function (GameState $state) use (&$bulletsYCoords, $ak) {
            $player = $state->getPlayer(1);
            $player->moveRight();

            $result = $player->attack();
            if (!$result) {
                return;
            }
            $bulletsYCoords[] = $result->getBullet()->getPosition()->y;
            if ($ak->getAmmo() === 0) {
                $player->suicide();
            }
            $player->equipKnife();
            $player->equipPrimaryWeapon();
        });

        $game->start();
        $this->assertCount($ak::magazineCapacity, $bulletsYCoords);
        $yMatchPlayer = 0;
        foreach ($bulletsYCoords as $y) {
            if ($y === $py) {
                $yMatchPlayer++;
            }
        }
        $this->assertLessThan(ceil($ak::magazineCapacity * .1), $yMatchPlayer);
    }

    public function testPistolMovementJumpRecoil(): void
    {
        $game = $this->createNoPauseGame();
        $player = $game->getPlayer(1);
        $player->setPosition(new Point(500, 0, 500));
        $player->getSight()->look(180, 0);
        $player->equipSecondaryWeapon();
        $glock = $player->getEquippedItem();
        $this->assertInstanceOf(PistolGlock::class, $glock);
        $px = $player->getPositionClone()->x;

        $bulletsXCoords = [];
        $game->onTick(function (GameState $state) use (&$bulletsXCoords, $glock) {
            $player = $state->getPlayer(1);
            if (!$player->isJumping()) {
                $player->jump();
                return;
            }

            $result = $player->attack();
            if (!$result) {
                return;
            }
            $bulletsXCoords[] = $result->getBullet()->getPosition()->x;
            if ($glock->getAmmo() === 0) {
                $player->suicide();
            }
            $player->equipKnife();
            $player->equipSecondaryWeapon();
        });

        $game->start();
        $this->assertCount($glock::magazineCapacity, $bulletsXCoords);
        $xMatchPlayer = 0;
        foreach ($bulletsXCoords as $x) {
            if ($x === $px) {
                $xMatchPlayer++;
            }
        }
        $this->assertLessThan(ceil($glock::magazineCapacity * .2), $xMatchPlayer);
    }

    public function testPistolMovementRecoil(): void
    {
        $game = $this->createNoPauseGame();
        $player = $game->getPlayer(1);
        $player->setPosition($player->getPositionClone()->addZ($player->getBoundingRadius()));
        $player->equipSecondaryWeapon();
        $glock = $player->getEquippedItem();
        $this->assertInstanceOf(PistolGlock::class, $glock);
        $py = $player->getPositionClone()->y + $player->getSightHeight();

        $bulletsYCoords = [];
        $game->onTick(function (GameState $state) use (&$bulletsYCoords, $glock) {
            $player = $state->getPlayer(1);
            $player->moveRight();

            $result = $player->attack();
            if (!$result) {
                return;
            }
            $bulletsYCoords[] = $result->getBullet()->getPosition()->y;
            if ($glock->getAmmo() === 0) {
                $player->suicide();
            }
            $player->equipKnife();
            $player->equipSecondaryWeapon();
        });

        $game->start();
        $this->assertCount($glock::magazineCapacity, $bulletsYCoords);
        $yMatchPlayer = 0;
        foreach ($bulletsYCoords as $y) {
            if ($y === $py) {
                $yMatchPlayer++;
            }
        }
        $this->assertLessThan(ceil($glock::magazineCapacity * .3), $yMatchPlayer);
    }

    public function testPistolMovementWalkRecoil(): void
    {
        $game = $this->createNoPauseGame();
        $player = $game->getPlayer(1);
        $player->setPosition($player->getPositionClone()->addZ($player->getBoundingRadius()));
        $player->equipSecondaryWeapon();
        $glock = $player->getEquippedItem();
        $this->assertInstanceOf(PistolGlock::class, $glock);
        $py = $player->getPositionClone()->y + $player->getSightHeight();

        $bulletsYCoords = [];
        $game->onTick(function (GameState $state) use (&$bulletsYCoords, $glock) {
            $player = $state->getPlayer(1);
            $player->moveRight();
            $player->speedWalk();

            $result = $player->attack();
            if (!$result) {
                return;
            }
            $bulletsYCoords[] = $result->getBullet()->getPosition()->y;
            if ($glock->getAmmo() === 0) {
                $player->suicide();
            }
            $player->equipKnife();
            $player->equipSecondaryWeapon();
        });

        $game->start();
        $this->assertCount($glock::magazineCapacity, $bulletsYCoords);
        $yMatchPlayer = 0;
        foreach ($bulletsYCoords as $y) {
            if ($y === $py) {
                $yMatchPlayer++;
            }
        }
        $this->assertLessThan(ceil($glock::magazineCapacity * .5), $yMatchPlayer);
    }

}
