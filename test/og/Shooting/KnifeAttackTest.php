<?php

namespace Test\Shooting;

use cs\Event\AttackResult;
use cs\Weapon\Knife;
use Test\BaseTestCase;

class KnifeAttackTest extends BaseTestCase
{

    public function testBlankStab(): void
    {
        $game = $this->createTestGame(null, $this->createNoPauseGameProperty());
        $this->playPlayer($game, [
            $this->waitNTicks(Knife::equipReadyTimeMs) + 1,
            function () use ($game) {
                $knife = $game->getPlayer(1)->getEquippedItem();
                $this->assertInstanceOf(Knife::class, $knife);
                $this->assertTrue($knife->canAttack($game->getTickId()));
                $this->assertTrue($game->getWorld()->canAttack($game->getPlayer(1)));
                $attack = $game->getPlayer(1)->attack();
                $this->assertInstanceOf(AttackResult::class, $attack);
                $bullet = $attack->getBullet();
                $this->assertSame(Knife::stabMaxDistance, $bullet->getDistanceTraveled());
                $this->assertSame(1, $bullet->getDamage());
                $this->assertSame($knife, $bullet->getShootItem());
                $this->assertSame(0, $attack->getMoneyAward());
                $this->assertCount(0, $attack->getHits());
            },
            $this->endGame(),
        ]);
    }

}
