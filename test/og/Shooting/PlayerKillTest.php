<?php

namespace Test\Shooting;

use cs\Core\Floor;
use cs\Core\GameException;
use cs\Core\GameProperty;
use cs\Core\HitBox;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Setting;
use cs\Core\Util;
use cs\Core\Wall;
use cs\Enum\ArmorType;
use cs\Enum\BuyMenuItem;
use cs\Enum\Color;
use cs\Enum\HitBoxType;
use cs\Enum\ItemId;
use cs\Event\AttackResult;
use cs\Event\KillEvent;
use cs\Weapon\PistolGlock;
use cs\Weapon\PistolUsp;
use cs\Weapon\RifleAk;
use cs\Weapon\RifleAWP;
use cs\Weapon\RifleM4A4;
use Test\BaseTestCase;

class PlayerKillTest extends BaseTestCase
{

    public function testOnePlayerCanKillOther(): void
    {
        $startMoney = 6000;
        $player2Commands = [
            fn(Player $p) => $p->getInventory()->earnMoney(6000 - 800),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::RIFLE_M4A4)),
            fn(Player $p) => $p->getSight()->lookHorizontal(180),
            $this->waitNTicks(RifleAk::equipReadyTimeMs) - 2,
            $this->endGame(),
        ];
        $player2 = new Player(2, Color::GREEN, false);

        $game = $this->createTestGame();
        $game->addPlayer($player2);
        $game->getPlayer(1)->setPosition(new Point(300, 0, 300));
        $player2->setPosition(new Point(300, 0, 500));
        $this->playPlayer($game, $player2Commands, $player2->getId());
        $this->assertTrue($game->getScore()->isTie());

        $result = $player2->attack();
        $gun = $player2->getEquippedItem();
        $this->assertInstanceOf(RifleM4A4::class, $gun);
        $this->assertNotNull($result);
        $hits = $result->getHits();
        $this->assertSame($gun->getKillAward(), $result->getMoneyAward());
        $this->assertCount(2, $hits);
        $headHit = $hits[0];
        $this->assertInstanceOf(HitBox::class, $headHit);
        $wall = $hits[1];
        $this->assertInstanceOf(Wall::class, $wall);
        $this->assertFalse($wall->playerWasKilled());
        $this->assertGreaterThan(0, $wall->getHitAntiForce(new Point()));

        $playerOne = $headHit->getPlayer();
        $this->assertFalse($playerOne->isAlive());
        $this->assertNull($player2->attack());
        $this->assertTrue($headHit->getType() === HitBoxType::HEAD);
        $this->assertSame($startMoney - $gun->getPrice() + $gun->getKillAward(), $player2->getMoney());
        $this->assertGreaterThan(0, $headHit->getHitAntiForce(new Point()));

        $this->expectException(GameException::class);
        $game->addPlayer(new Player($player2->getId(), Color::BLUE, true));
    }

    public function testOnePlayerCanKillOtherWallBang(): void
    {
        $player2Commands = [
            fn(Player $p) => $p->getSight()->lookHorizontal(150),
            $this->endGame(),
        ];
        $player2 = new Player(2, Color::GREEN, false);

        $game = $this->createNoPauseGame();
        $game->getPlayer(1)->setPosition(new Point(50, 0, 50));
        $game->getWorld()->addWall(new Wall(new Point(35, 1, 80), true, 20));
        $game->addPlayer($player2);
        $player2->setPosition($player2->getPositionClone()->addZ(100));
        $this->playPlayer($game, $player2Commands, $player2->getId());
        $this->assertTrue($game->getScore()->isTie());

        $result = $player2->attack();
        $gun = $player2->getEquippedItem();
        $this->assertInstanceOf(PistolUsp::class, $gun);
        $this->assertNotNull($result);
        $hits = $result->getHits();
        $this->assertSame($gun::killAward, $result->getMoneyAward());
        $this->assertCount(3, $hits);
        $this->assertInstanceOf(Wall::class, $hits[0]);
        $headHit = $hits[1];
        $this->assertInstanceOf(HitBox::class, $headHit);
        $this->assertInstanceOf(Wall::class, $hits[2]);

        $playerOne = $headHit->getPlayer();
        $this->assertFalse($playerOne->isAlive());
        $this->assertNull($player2->attack());
        $this->assertTrue($headHit->getType() === HitBoxType::HEAD);
        $this->assertSame($gun::magazineCapacity - 1, $gun->getAmmo());
    }

    public function testBulletHitOnePlayerOnlyOneHitBox(): void
    {
        $player2 = new Player(2, Color::GREEN, false);
        $game = $this->createTestGame();
        $game->addPlayer($player2);
        $player1 = $game->getPlayer(1);
        $player1->getInventory()->earnMoney(1000);
        $player1->buyItem(BuyMenuItem::KEVLAR_BODY_AND_HEAD);
        $this->assertSame(100, $player1->getArmorValue());
        $this->assertSame(ArmorType::BODY_AND_HEAD, $player1->getArmorType());
        $player2Position = $player1->getPositionClone()->addY($player1->getHeadHeight() + 10);
        $game->getWorld()->addFloor(new Floor($player2Position->clone()->addX(-10)));
        $player2->setPosition($player2Position);
        $player2->getSight()->look(0, -90);

        $result = $player2->attack();
        $this->assertLessThan(100, $player1->getArmorValue());
        $this->assertSame(ArmorType::BODY_AND_HEAD, $player1->getArmorType());
        $this->assertNotNull($result);
        $gun = $player2->getEquippedItem();
        $this->assertInstanceOf(PistolUsp::class, $gun);
        $this->assertSame($player2Position->y + $player2->getSightHeight(), $result->getBullet()->getDistanceTraveled());

        $hits = $result->getHits();
        $this->assertCount(2, $hits);

        $headShot = $hits[0];
        $this->assertInstanceOf(HitBox::class, $headShot);
        $this->assertTrue($headShot->wasHeadShot());
        $this->assertSame(HitBoxType::HEAD, $headShot->getType());
        $this->assertInstanceOf(Floor::class, $hits[1]);

        $this->assertTrue($result->somePlayersWasHit());
        $this->assertSame(0, $result->getMoneyAward());
        $this->assertSame(1, $game->getRoundNumber());
        $this->assertTrue($player1->isAlive());
        $this->assertSame(100 - 10 - 30, $player1->getArmorValue());
    }

    public function testM4KillPlayerInFourBulletsInChestWithNoKevlar(): void
    {
        $game = $this->createTestGame();
        $p2 = new Player(2, Color::BLUE, false);
        $game->addPlayer($p2);
        $p2->setPosition(new Point(500, 0, 500));
        $game->getPlayer(1)->setPosition($p2->getPositionClone()->addZ(-200));

        $finished = false;
        $attackCallback = function (Player $p) use ($game) {
            $this->assertSame(1, $game->getRoundNumber());
            $res = $p->attack();
            $this->assertNotNull($res);
            $this->assertTrue($res->somePlayersWasHit());
            $hits = $res->getHits();
            $this->assertCount(2, $hits);
            $hit = $hits[0];
            $this->assertInstanceOf(HitBox::class, $hit);
            $this->assertSame(HitBoxType::CHEST, $hit->getType());
        };


        $this->playPlayer($game, [
            fn(Player $p) => $p->getInventory()->earnMoney(5000),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::RIFLE_M4A4)),
            fn(Player $p) => $p->getSight()->look(180, -10),
            $this->waitNTicks(RifleM4A4::equipReadyTimeMs),
            $attackCallback,
            $this->waitNTicks(RifleM4A4::recoilResetMs),
            $attackCallback,
            $this->waitNTicks(RifleM4A4::recoilResetMs),
            $attackCallback,
            $this->waitNTicks(RifleM4A4::recoilResetMs),
            $attackCallback,
            $this->waitNTicks(RifleM4A4::recoilResetMs),
            fn() => $this->assertSame(2, $game->getRoundNumber()),
            function () use (&$finished) {
                $finished = true;
            },
            $this->endGame(),
        ], $p2->getId());
        $this->assertTrue($finished);
    }

    public function testUspKillPlayerInThreeBulletsInChestWithNoKevlar(): void
    {
        $player2Commands = [
            fn(Player $p) => $p->getSight()->look(180, 19),
            fn(Player $p) => $p->crouch(),
            $this->waitNTicks(max(Setting::tickCountCrouch(), PistolUsp::equipReadyTimeMs)),
            $this->endGame(),
        ];
        $player2 = new Player(2, Color::GREEN, false);
        $this->assertSame(0, $player2->getArmorValue());
        $this->assertSame(ArmorType::NONE, $player2->getArmorType());

        $game = $this->createTestGame();
        $game->addPlayer($player2);
        $player1 = $game->getPlayer(1);
        $player1->setPosition(new Point(300, 0, 300));
        $player2->setPosition(new Point(300, 0, 350));
        $this->playPlayer($game, $player2Commands, $player2->getId());

        $this->assertSame(100, $player1->getHealth());
        $result = $player2->attack();
        $this->assertNotNull($result);
        $this->assertTrue($result->somePlayersWasHit());
        $gun = $player2->getEquippedItem();
        $this->assertInstanceOf(PistolUsp::class, $gun);
        $this->assertSame(0, $result->getMoneyAward());
        $this->assertSame(1, $game->getRoundNumber());

        $hits = $result->getHits();
        $this->assertCount(2, $hits);
        $bodyShot = $hits[0];
        $this->assertInstanceOf(HitBox::class, $bodyShot);
        $this->assertSame(HitBoxType::CHEST, $bodyShot->getType());
        $this->assertInstanceOf(Wall::class, $hits[1]);

        $this->assertLessThan(100, $player1->getHealth());
        $this->assertTrue($player1->isAlive());
        $this->assertTrue($player2->isAlive());

        $tickId = $game->getTickId();
        for ($i = 1; $i <= Util::millisecondsToFrames(PistolUsp::fireRateMs + PistolUsp::recoilResetMs); $i++) {
            $game->tick(++$tickId);
        }
        $this->assertNotNull($player2->attack());
        $this->assertTrue($player1->isAlive());
        $this->assertTrue($player2->isAlive());
        $this->assertSame(1, $game->getRoundNumber());

        for ($i = 1; $i <= Util::millisecondsToFrames(PistolUsp::fireRateMs + PistolUsp::recoilResetMs); $i++) {
            $game->tick(++$tickId);
        }
        $this->assertNotNull($player2->attack());
        $this->assertFalse($player1->isAlive());
        $this->assertTrue($player2->isAlive());
        $this->assertSame(1, $game->getRoundNumber());

        for ($i = 1; $i <= Util::millisecondsToFrames(PistolUsp::fireRateMs + PistolUsp::recoilResetMs); $i++) {
            $game->tick(++$tickId);
        }
        $this->assertFalse($game->getScore()->attackersIsWinning());
        $this->assertSame(0, $game->getScore()->getScoreAttackers());
        $this->assertSame(1, $game->getScore()->getScoreDefenders());
        $this->assertSame(0, $game->getScore()->getNumberOfLossRoundsInRow(false));
        $this->assertSame(1, $game->getScore()->getNumberOfLossRoundsInRow(true));
        $this->assertFalse($game->getScore()->isTie());
        $this->assertSame(2, $game->getRoundNumber());
    }

    public function testPlayerCanDodgeBulletByCrouch(): void
    {
        $player2 = new Player(2, Color::GREEN, false);
        $game = $this->createTestGame();
        $game->getPlayer(1)->setPosition(new Point(500, 0, 250));
        $game->addPlayer($player2);
        $player2->setPosition(new Point(500, 0, 500));
        $player2->getSight()->look(180, 0);
        $game->getPlayer(1)->crouch();

        for ($i = 1; $i <= Setting::tickCountCrouch(); $i++) {
            $game->tick($i);
        }
        $this->assertSame(Setting::playerHeadHeightCrouch(), $game->getPlayer(1)->getHeadHeight());
        $this->assertSame(Setting::playerHeadHeightStand(), $player2->getHeadHeight());
        $result = $player2->attack();
        $this->assertNotNull($result);

        $hits = $result->getHits();
        $this->assertCount(1, $hits);
        $this->assertInstanceOf(Wall::class, $hits[0]);
    }

    public function testAwpOneBulletTripleHeadShotKills(): void
    {
        $gp = new GameProperty();
        $gp->start_money = 5000;

        $game = $this->createTestGame(null, $gp);
        $player2 = new Player(2, Color::GREEN, false);
        $game->addPlayer($player2);
        $player2->buyItem(BuyMenuItem::KEVLAR_BODY_AND_HEAD);
        $player2->setPosition(new Point(300, 0, 300));
        $player3 = new Player(3, Color::ORANGE, false);
        $game->addPlayer($player3);
        $player3->buyItem(BuyMenuItem::KEVLAR_BODY_AND_HEAD);
        $player3->setPosition(new Point(500, 0, 500));
        $player4 = new Player(4, Color::YELLOW, false);
        $game->addPlayer($player4);
        $player4->buyItem(BuyMenuItem::KEVLAR_BODY_AND_HEAD);
        $player4->setPosition(new Point(700, 0, 700));
        $player5 = new Player(5, Color::PURPLE, false);
        $game->addPlayer($player5);
        $player5->buyItem(BuyMenuItem::KEVLAR_BODY_AND_HEAD);
        $player5->setPosition(new Point(900, 0, 900));

        $this->playPlayer($game, [
            fn(Player $p) => $this->assertSame(1, $game->getRoundNumber()),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::RIFLE_AWP)),
            $this->waitNTicks(RifleAWP::equipReadyTimeMs),
            function (Player $p) {
                $p->getSight()->look(45, 0);
                $this->assertNull($p->attackSecondary());
                $this->assertSame(1, $p->getEquippedItem()->getScopeLevel());
                $ar = $p->attack();
                $this->assertInstanceOf(AttackResult::class, $ar);
                $this->assertTrue($ar->somePlayersWasHit());
                $hits = $ar->getHits();
                $this->assertCount(4, $hits);
                $hit = array_pop($hits);
                $this->assertInstanceOf(HitBox::class, $hit);
                $this->assertFalse($hit->playerWasKilled());
                foreach ($hits as $hit) {
                    $this->assertTrue($hit->playerWasKilled());
                    $this->assertTrue($hit->wasHeadShot());
                }
            },
            fn(Player $p) => $this->assertSame(1, $game->getRoundNumber()),
            fn(Player $p) => $this->assertCount(2, $game->getAlivePlayers()),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            $this->waitNTicks(PistolGlock::fireRateMs),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            fn(Player $p) => $this->assertSame(2, $game->getRoundNumber()),
            $this->endGame(),
        ]);
    }

    public function testPlayerCanDodgeBulletByCrouchWithAngleDown(): void
    {
        $player2 = new Player(2, Color::GREEN, false);

        $game = $this->createTestGame();
        $game->addPlayer($player2);
        $game->getPlayer(1)->crouch();

        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(180, -18),
            $this->waitXTicks(Setting::tickCountCrouch()),
            fn(Player $p) => $this->assertSame(Setting::playerHeadHeightStand(), $p->getHeadHeight()),
            fn(Player $p) => $this->assertSame(Setting::playerHeadHeightCrouch(), $game->getPlayer(1)->getHeadHeight()),
            function (Player $p) {
                $this->assertSame(-18.0, $p->getSight()->getRotationVertical());
                $result = $p->attack();
                $this->assertNotNull($result);

                $hits = $result->getHits();
                $this->assertCount(1, $hits);
                $this->assertInstanceOf(Wall::class, $hits[0]);
            },
            $this->endGame(),
        ], $player2->getId());
    }

    public function testPlayerHeadShotCrouchingPlayer(): void
    {
        $player2 = new Player(2, Color::GREEN, false);

        $game = $this->createTestGame();
        $game->getPlayer(1)->crouch();
        $game->addPlayer($player2);
        $game->getPlayer(1)->setPosition(new Point(300, 0, 300));
        $player2->setPosition(new Point(300, 0, 500));

        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(180, -18),
            $this->waitXTicks(Setting::tickCountCrouch()),
            fn(Player $p) => $this->assertSame(Setting::playerHeadHeightStand(), $p->getHeadHeight()),
            fn(Player $p) => $this->assertSame(Setting::playerHeadHeightCrouch(), $game->getPlayer(1)->getHeadHeight()),
            function (Player $p) {
                $result = $p->attack();
                $this->assertNotNull($result);

                $hits = $result->getHits();
                $this->assertTrue($result->somePlayersWasHit());
                $this->assertGreaterThan(0, $result->getMoneyAward());
                $this->assertCount(2, $hits);
                $this->assertInstanceOf(HitBox::class, $hits[0]);
                $this->assertInstanceOf(Wall::class, $hits[1]);
            },
            $this->endGame(),
        ], $player2->getId());
    }

    public function testHitTwoPlayersSharingSamePosition(): void
    {
        $p2 = new Player(2, Color::GREEN, false);
        $p3 = new Player(3, Color::GREEN, false);

        $game = $this->createTestGame();

        $game->addPlayer($p2);
        $game->addPlayer($p3);
        $p2->setPosition(new Point(300, 0, 300));
        $p3->setPosition($p2->getPositionClone());

        $this->playPlayer($game, [
            fn(Player $p) => $p->setPosition(new Point(500, 0, 300)),
            fn(Player $p) => $p->getSight()->look(-90, 0),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs),
            function (Player $p) {
                $result = $p->attack();
                $this->assertNotNull($result);

                $hits = $result->getHits();
                $this->assertCount(3, $hits);
                $this->assertInstanceOf(HitBox::class, $hits[0]);
                $this->assertInstanceOf(HitBox::class, $hits[1]);
                $this->assertInstanceOf(Wall::class, $hits[2]);
            },
            $this->endGame(),
        ]);
    }

    public function testArmorShooting(): void
    {
        $game = $this->createTestGame();
        $p2 = new Player(2, Color::GREEN, false);
        $game->addPlayer($p2);
        $p1 = $game->getPlayer(1);

        $p1->getSight()->look(-90, -10);
        $p1->setPosition(new Point(500, 0, 500));

        $p2->getSight()->look(-90, -90);
        $p2->setPosition(new Point(300, 0, 500));
        $p2->buyItem(BuyMenuItem::KEVLAR_BODY);

        $game->addPlayer(new Player(3, Color::ORANGE, false));
        $game->getPlayer(3)->setPosition(new Point(350, 0, 500));
        $game->getPlayer(3)->suicide();

        $this->playPlayer($game, [
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs),
            function (Player $p) use ($p2) {
                $this->assertSame(100, $p2->getHealth());
                $this->assertSame(100, $p2->getArmorValue());

                $result = $this->assertPlayerHit($p->attack());
                $hits = $result->getHits();
                $this->assertCount(2, $hits);

                $bodyShot = $hits[0];
                $this->assertInstanceOf(HitBox::class, $bodyShot);
                $this->assertSame(HitBoxType::BACK, $bodyShot->getType());

                $this->assertLessThan(100, $p2->getHealth());
                $this->assertLessThan(100, $p2->getArmorValue());
                $this->assertTrue($p->isAlive());
                $this->assertTrue($p2->isAlive());
            },
            fn() => $this->assertSame(1, $game->getRoundNumber()),
            $this->waitNTicks(PistolGlock::recoilResetMs),
            fn(Player $p) => $p->getSight()->look(-90, 0),
            fn(Player $p) => $this->assertCount(2, $this->assertPlayerHit($p->attack())->getHits()),
            $this->endGame(),
        ]);
    }

    public function testPlayerHorizontalVerticalBullet(): void
    {
        $player2 = new Player(2, Color::GREEN, false);

        $game = $this->createTestGame();
        $game->getPlayer(1)->crouch();
        $game->addPlayer($player2);
        $game->getPlayer(1)->setPosition(new Point(300, 0, 300));
        $player2->setPosition(new Point(400, 0, 500));

        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(206, -11),
            $this->waitXTicks(Setting::tickCountCrouch()),
            fn(Player $p) => $this->assertSame(Setting::playerHeadHeightStand(), $p->getHeadHeight()),
            fn(Player $p) => $this->assertSame(Setting::playerHeadHeightCrouch(), $game->getPlayer(1)->getHeadHeight()),
            function (Player $p) {
                $result = $p->attack();
                $this->assertNotNull($result);

                $hits = $result->getHits();
                $this->assertTrue($result->somePlayersWasHit());
                $this->assertGreaterThan(0, $result->getMoneyAward());
                $this->assertCount(2, $hits);
                $this->assertInstanceOf(HitBox::class, $hits[0]);
                $this->assertInstanceOf(Wall::class, $hits[1]);
            },
            $this->endGame(),
        ], $player2->getId());
    }

    public function testPlayerHorizontalVerticalUpBullet(): void
    {
        $player2 = new Player(2, Color::GREEN, false);
        $player2->crouch();

        $game = $this->createTestGame();
        $game->addPlayer($player2);
        $game->getPlayer(1)->setPosition(new Point(300, 0, 300));
        $player2->setPosition(new Point(400, 0, 500));

        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(207, 9),
            $this->waitXTicks(Setting::tickCountCrouch()),
            fn(Player $p) => $this->assertSame(Setting::playerHeadHeightCrouch(), $p->getHeadHeight()),
            fn(Player $p) => $this->assertSame(Setting::playerHeadHeightStand(), $game->getPlayer(1)->getHeadHeight()),
            function (Player $p) {
                $result = $p->attack();
                $this->assertNotNull($result);

                $hits = $result->getHits();
                $this->assertTrue($result->somePlayersWasHit());
                $this->assertGreaterThan(0, $result->getMoneyAward());
                $this->assertCount(2, $hits);
                $this->assertInstanceOf(HitBox::class, $hits[0]);
                $this->assertInstanceOf(Wall::class, $hits[1]);
            },
            $this->endGame(),
        ], $player2->getId());
    }

    public function testPlayerHorizontalVerticalUpBullet2(): void
    {
        $player2 = new Player(2, Color::GREEN, false);
        $player2->crouch();

        $game = $this->createTestGame();
        $game->getPlayer(1)->setPosition(new Point(1440, 0, 1457));
        $game->addPlayer($player2);
        $player2->setPosition(new Point(989, 0, 1037));

        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(47, 5),
            $this->waitXTicks(Setting::tickCountCrouch()),
            fn(Player $p) => $this->assertSame(Setting::playerHeadHeightCrouch(), $p->getHeadHeight()),
            fn(Player $p) => $this->assertSame(Setting::playerHeadHeightStand(), $game->getPlayer(1)->getHeadHeight()),
            function (Player $p) {
                $result = $p->attack();
                $this->assertNotNull($result);

                $hits = $result->getHits();
                $this->assertGreaterThan(0, $result->getMoneyAward());
                $this->assertCount(1, $hits);
                $this->assertInstanceOf(HitBox::class, $hits[0]);
            },
            $this->endGame(),
        ], $player2->getId());
    }

    public function testMultiRoundKills(): void
    {
        $player2 = new Player(2, Color::GREEN, false);
        $game = $this->createNoPauseGame(15);
        $game->addPlayer($player2);
        $p1 = $game->getPlayer(1);

        $afterRoundWaitTicks = 2;
        $killEventsCount = 0;
        $game->onEvents(function (array $events) use (&$killEventsCount): void {
            foreach ($events as $event) {
                if ($event instanceof KillEvent) {
                    $killEventsCount++;
                    $this->assertTrue($event->wasHeadShot());
                    $this->assertSame(ItemId::$map[PistolUsp::class], $event->getAttackItemId());
                }
            }
        });
        $this->playPlayer($game, [
            fn() => $p1->setPosition(new Point(500, 0, 300)),
            fn(Player $p) => $p->setPosition(new Point(300, 0, 300)),
            fn(Player $p) => $p->getSight()->lookHorizontal(90),
            function (Player $p) {
                $ar = $p->attack();
                $this->assertNotNull($ar);
                $this->assertNotEmpty($ar->getHits());
            },
            $this->waitXTicks($afterRoundWaitTicks),
            fn() => $p1->setPosition(new Point(500, 0, 300)),
            fn(Player $p) => $p->setPosition(new Point(300, 0, 300)),
            fn(Player $p) => $p->getSight()->lookHorizontal(90),
            function (Player $p) {
                $ar = $p->attack();
                $this->assertNotNull($ar);
                $this->assertNotEmpty($ar->getHits());
            },
            $this->waitXTicks($afterRoundWaitTicks),
            fn() => $p1->setPosition(new Point(500, 0, 300)),
            fn(Player $p) => $p->setPosition(new Point(300, 0, 300)),
            fn(Player $p) => $p->getSight()->lookHorizontal(90),
            function (Player $p) {
                $ar = $p->attack();
                $this->assertNotNull($ar);
                $this->assertNotEmpty($ar->getHits());
            },
            $this->waitXTicks($afterRoundWaitTicks),
            $this->endGame(),
        ], $player2->getId());


        $this->assertSame(3, $killEventsCount);
        $this->assertSame(3, $game->getScore()->getScoreDefenders());
        $this->assertSame(3 + 1, $game->getRoundNumber());
        $this->assertSame(6500, $p1->getMoney());
        $this->assertSame(11450, $player2->getMoney());
    }

    public function testPlayerCannotKillDeadPlayer(): void
    {
        $game = $this->createNoPauseGame(15);
        $game->addPlayer(new Player(2, Color::GREEN, false));
        $game->getPlayer(2)->setPosition(new Point(200, 0, 500));
        $game->addPlayer(new Player(3, Color::GREEN, false));
        $game->getPlayer(3)->setPosition(new Point(999, 0, 10));

        $this->playPlayer($game, [
            fn(Player $p) => $p->setPosition(new Point(200, 0, 300)),
            fn(Player $p) => $p->getSight()->look(0, 0),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs),
            fn(Player $p) => $this->assertPlayerHit($p->attack()),
            fn() => $this->assertFalse($game->getPlayer(2)->isAlive()),
            $this->waitNTicks(PistolGlock::fireRateMs),
            fn(Player $p) => $this->assertPlayerNotHit($p->attack()),
            fn(Player $p) => $this->assertSame(1, $game->getRoundNumber()),
            $this->endGame(),
        ]);
    }

    public function testPlayerHitBoxReset(): void
    {
        $game = $this->createNoPauseGame(15);
        $game->addPlayer(new Player(2, Color::GREEN, false));

        $this->playPlayer($game, [
            fn() => $game->getPlayer(2)->setPosition(new Point(200, 0, 500)),
            fn() => $game->getPlayer(2)->getSight()->lookHorizontal(180),
            fn(Player $p) => $p->setPosition(new Point(200, 0, 300)),
            fn(Player $p) => $p->getSight()->look(0, -20),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs),
            fn(Player $p) => $this->assertSame(0, $this->assertPlayerHit($p->attack())->getMoneyAward()),
            fn(Player $p) => $this->assertSame(1, $game->getRoundNumber()),
            $this->waitNTicks(PistolGlock::fireRateMs),
            fn(Player $p) => $this->assertSame(0, $this->assertPlayerHit($p->attack())->getMoneyAward()),
            fn(Player $p) => $this->assertSame(1, $game->getRoundNumber()),
            $this->waitNTicks(PistolGlock::fireRateMs),
            fn(Player $p) => $this->assertGreaterThan(0, $this->assertPlayerHit($p->attack())->getMoneyAward()),
            fn(Player $p) => $this->assertSame(2, $game->getRoundNumber()),
            fn() => $game->getPlayer(2)->setPosition(new Point(200, 0, 500)),
            fn() => $game->getPlayer(2)->getSight()->lookHorizontal(180),
            fn(Player $p) => $p->setPosition(new Point(200, 0, 300)),
            fn(Player $p) => $p->getSight()->look(0, -20),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs),
            function (Player $p) {
                $result = $this->assertPlayerHit($p->attack());
                $hits = $result->getHits();
                $this->assertCount(2, $hits);
                $this->assertInstanceOf(Floor::class, $hits[1]);
                $playerHit = $hits[0];
                $this->assertInstanceOf(HitBox::class, $playerHit);
                $this->assertSame(HitBoxType::STOMACH, $playerHit->getType());
                $this->assertFalse($playerHit->playerWasKilled());
                $this->assertSame(0, $playerHit->getMoneyAward());
            },
            fn() => $this->assertTrue($game->getPlayer(2)->isAlive()),
            $this->endGame(),
        ]);

        $this->assertSame(2, $game->getRoundNumber());
    }

    public function testBulletCanHitTwoPlayers(): void
    {
        $game = $this->createNoPauseGame();
        $game->addPlayer(new Player(2, Color::BLUE, false));
        $game->addPlayer(new Player(3, Color::GREEN, false));

        $this->playPlayer($game, [
            fn(Player $p) => $p->setPosition(new Point(600, 0, 500)),
            fn(Player $p) => $p->getSight()->look(-90, 0),
            fn() => $game->getPlayer(2)->setPosition(new Point(400, 0, 500)),
            fn() => $game->getPlayer(3)->setPosition(new Point(200, 0, 500)),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs),
            function (Player $p) use ($game) {
                $result = $this->assertPlayerHit($p->attack());
                $hits = $result->getHits();
                $this->assertCount(3, $hits);
                $this->assertInstanceOf(Wall::class, $hits[2]);
                $this->assertSame(1, $game->getRoundNumber());
                $this->assertGreaterThan(0, $result->getMoneyAward());

                $playerHit = $hits[1];
                $this->assertInstanceOf(HitBox::class, $playerHit);
                $this->assertSame(3, $playerHit->getPlayer()->getId());
                $this->assertFalse($playerHit->playerWasKilled());

                $playerHit = $hits[0];
                $this->assertInstanceOf(HitBox::class, $playerHit);
                $this->assertSame(2, $playerHit->getPlayer()->getId());
                $this->assertTrue($playerHit->playerWasKilled());
            },
            fn() => $this->assertSame(1, $game->getRoundNumber()),
            fn() => $this->assertFalse($game->getPlayer(2)->isAlive()),
            fn() => $this->assertTrue($game->getPlayer(3)->isAlive()),
            fn() => $this->assertLessThan(100, $game->getPlayer(3)->getHealth()),
            fn() => $this->assertGreaterThan(25, $game->getPlayer(3)->getHealth()),
            $this->endGame(),
        ]);
    }

}
