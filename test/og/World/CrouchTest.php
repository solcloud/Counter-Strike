<?php

namespace Test\World;

use cs\Core\Box;
use cs\Core\Player;
use cs\Core\Setting;
use Test\BaseTestCase;

class CrouchTest extends BaseTestCase
{

    public function testCanCrouchUnderWall(): void
    {
        $game = $this->createTestGame();
        $player = $game->getPlayer(1);

        $scale = $player->getBoundingRadius();
        $start = $player->getPositionClone()->clone()->addZ(2 * $scale);
        $ceiling = new Box($start->clone()->addY(Setting::playerHeadHeightCrouch() + 1)->addX(-2 * $scale), 4 * $scale, $scale, 10);
        $game->getWorld()->addBox($ceiling);
        $game->getWorld()->addBox(new Box($start->clone()->addX(-2 * $scale - 1), $scale, Setting::playerHeadHeightStand(), 10));
        $game->getWorld()->addBox(new Box($start->clone()->addX($scale + 1), $scale, Setting::playerHeadHeightStand(), 10));

        $commands = [
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            function (Player $p) use ($start) {
                $this->assertSame($start->z - $p->getBoundingRadius() - 1, $p->getPositionClone()->z);
            },
            fn(Player $p) => $p->crouch(),
            $this->waitXTicks(Setting::tickCountCrouch()),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            $this->endGame(),
        ];

        $this->playPlayer($game, $commands);
        $this->assertGreaterThan($start->z + $scale, $player->getPositionClone()->z);
    }

    public function testCanCrouchIntoTunnel(): void
    {
        $depth = Setting::moveDistanceCrouchPerTick() * 3;
        $game = $this->createTestGame();
        $player = $game->getPlayer(1);

        $scale = $player->getBoundingRadius();
        $start = $player->getPositionClone()->clone()->addZ(2 * $scale);
        $ceiling = new Box($start->clone()->addY(Setting::playerHeadHeightCrouch() + 1)->addX(-2 * $scale), 4 * $scale, $scale, $depth);
        $game->getWorld()->addBox($ceiling);
        $game->getWorld()->addBox(new Box($start->clone()->addX(-2 * $scale - 1), $scale, Setting::playerHeadHeightStand(), $depth));
        $game->getWorld()->addBox(new Box($start->clone()->addX($scale + 1), $scale, Setting::playerHeadHeightStand(), $depth));

        $commands = [
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            function (Player $p) use ($start) {
                $this->assertSame($start->z - $p->getBoundingRadius() - 1, $p->getPositionClone()->z);
            },
            fn(Player $p) => $p->crouch(),
            fn(Player $p) => $this->assertTrue($p->isCrouching()),
            $this->waitXTicks(Setting::tickCountCrouch()),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->stand(),
            fn(Player $p) => $p->stand(),
            function (Player $p) use ($ceiling) {
                $this->assertSame(Setting::playerHeadHeightCrouch(), $p->getHeadHeight());
                $this->assertLessThan($ceiling->getBase()->y, $p->getPositionClone()->y + $p->getHeadHeight());
            },
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            $this->waitXTicks(Setting::tickCountCrouch()),
            fn(Player $p) => $p->moveBackward(),
            fn(Player $p) => $p->moveBackward(),
            $this->endGame(),
        ];

        $this->playPlayer($game, $commands);
        $this->assertSame($ceiling->getBase()->z + $ceiling->depthZ + $player->getBoundingRadius() + 1, $player->getPositionClone()->z);
        $this->assertSame(Setting::playerHeadHeightStand(), $player->getHeadHeight());
        $this->assertFalse($game->getPlayer(1)->isCrouching());
    }


}
