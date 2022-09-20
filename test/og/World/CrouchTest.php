<?php

namespace Test\World;

use cs\Core\Box;
use cs\Core\Player;
use Test\BaseTestCase;

class CrouchTest extends BaseTestCase
{

    public function testCanCrouchUnderWall(): void
    {
        $game = $this->createTestGame();
        $player = $game->getPlayer(1);

        $scale = $player->getBoundingRadius();
        $start = $player->getPositionImmutable()->clone()->addZ(3 * $scale);
        $ceiling = new Box($start->clone()->addY($player::headHeightCrouch + 1)->addX(-2 * $scale), 4 * $scale, $scale, 10);
        $game->getWorld()->addBox($ceiling);
        $game->getWorld()->addBox(new Box($start->clone()->addX(-2 * $scale - 1), $scale, $player::headHeightStand, 10));
        $game->getWorld()->addBox(new Box($start->clone()->addX($scale + 1), $scale, $player::headHeightStand, 10));

        $commands = [
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            function (Player $p) use ($start) {
                $this->assertSame($start->z - $p->getBoundingRadius() - 1, $p->getPositionImmutable()->z);
            },
            fn(Player $p) => $p->crouch(),
            $this->waitXTicks(Player::tickCountCrouch),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            $this->endGame(),
        ];

        $this->playPlayer($game, $commands);
        $this->assertGreaterThan($start->z + $scale, $player->getPositionImmutable()->z);
    }

    public function testCanCrouchIntoTunnel(): void
    {
        $depth = Player::speedMoveCrouch * 4;
        $game = $this->createTestGame();
        $player = $game->getPlayer(1);

        $scale = $player->getBoundingRadius();
        $start = $player->getPositionImmutable()->clone()->addZ(3 * $scale);
        $ceiling = new Box($start->clone()->addY($player::headHeightCrouch + 1)->addX(-2 * $scale), 4 * $scale, $scale, $depth);
        $game->getWorld()->addBox($ceiling);
        $game->getWorld()->addBox(new Box($start->clone()->addX(-2 * $scale - 1), $scale, $player::headHeightStand, $depth));
        $game->getWorld()->addBox(new Box($start->clone()->addX($scale + 1), $scale, $player::headHeightStand, $depth));

        $commands = [
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            function (Player $p) use ($start) {
                $this->assertSame($start->z - $p->getBoundingRadius() - 1, $p->getPositionImmutable()->z);
            },
            fn(Player $p) => $p->crouch(),
            $this->waitXTicks(Player::tickCountCrouch),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->stand(),
            fn(Player $p) => $p->stand(),
            function (Player $p) use ($ceiling) {
                $this->assertSame(Player::headHeightCrouch, $p->getHeadHeight());
                $this->assertLessThan($ceiling->getBase()->y, $p->getPositionImmutable()->y + $p->getHeadHeight());
            },
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->stand(),
            $this->waitXTicks(Player::tickCountCrouch),
            fn(Player $p) => $p->moveBackward(),
            fn(Player $p) => $p->moveBackward(),
            $this->endGame(),
        ];

        $this->playPlayer($game, $commands);
        $this->assertSame($ceiling->getBase()->z + $ceiling->depthZ + $player->getBoundingRadius() + 1, $player->getPositionImmutable()->z);
        $this->assertSame(Player::headHeightStand, $player->getHeadHeight());
    }


}
