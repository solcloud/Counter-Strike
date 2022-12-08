<?php

namespace Test\Shooting;

use cs\Core\HitBox;
use cs\Core\Player;
use cs\Enum\Color;
use cs\Enum\HitBoxType;
use cs\Event\AttackResult;
use cs\Weapon\Knife;
use Test\BaseTestCase;

class KnifeAttackTest extends BaseTestCase
{

    public function testBlankStab(): void
    {
        $game = $this->createNoPauseGame();
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

    public function testBackStab(): void
    {
        $game = $this->createNoPauseGame();
        $p2 = new Player(2, Color::GREEN, false);
        $game->addPlayer($p2);
        $p2->setPosition($game->getPlayer(1)->getPositionImmutable()->addZ($p2->getBoundingRadius() * 3));
        $this->playPlayer($game, [
            fn(Player $p) => $p->reload(),
            $this->waitNTicks(Knife::equipReadyTimeMs),
            fn(Player $p) => $p->getSight()->lookAt(0, -30),
            fn(Player $p) => $p->moveForward(),
            function () use ($game) {
                $knife = $game->getPlayer(1)->getEquippedItem();
                $this->assertInstanceOf(Knife::class, $knife);

                $attack = $game->getPlayer(1)->attackSecondary();
                $this->assertInstanceOf(AttackResult::class, $attack);
                $this->assertSame(0, $game->getPlayer(2)->getHealth());
                $this->assertFalse($game->getPlayer(2)->isAlive());

                $bullet = $attack->getBullet();
                $this->assertLessThanOrEqual(Knife::stabMaxDistance, $bullet->getDistanceTraveled());
                $this->assertSame($knife, $bullet->getShootItem());
                $this->assertSame(Knife::killAward, $attack->getMoneyAward());
                $this->assertTrue($attack->somePlayersWasHit());

                $hits = $attack->getHits();
                $this->assertCount(1, $hits);
                $hitBox = $hits[0];
                $this->assertInstanceOf(HitBox::class, $hitBox);
                //$this->assertSame(HitBoxType::BACK, $hitBox->getType()); once there is HitBoxBack geometry
            },
            $this->endGame(),
        ]);
    }

}
