<?php

namespace Test\Inventory;

use cs\Core\Box;
use cs\Core\GameProperty;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Wall;
use cs\Core\World;
use cs\Enum\ArmorType;
use cs\Enum\BuyMenuItem;
use cs\Enum\Color;
use cs\Enum\InventorySlot;
use cs\Enum\SoundType;
use cs\Equipment\Bomb;
use cs\Equipment\Decoy;
use cs\Equipment\Flashbang;
use cs\Equipment\HighExplosive;
use cs\Equipment\Kevlar;
use cs\Event\SoundEvent;
use cs\Weapon\Knife;
use cs\Weapon\PistolGlock;
use cs\Weapon\PistolUsp;
use cs\Weapon\RifleAk;
use cs\Weapon\RifleAWP;
use cs\Weapon\RifleM4A4;
use ReflectionProperty;
use Test\BaseTestCase;

class InventoryTest extends BaseTestCase
{
    public function testPlayerInventory(): void
    {
        $game = $this->createOneRoundGame();
        $game->onTick(fn(GameState $state) => $state->getPlayer(1)->equipPrimaryWeapon());
        $game->start();

        $knife = $game->getPlayer(1)->getEquippedItem();
        $this->assertInstanceOf(Knife::class, $knife);
        $this->assertFalse($knife->isUserDroppable());
    }

    public function testPlayerInventoryEquipUnEquipAble(): void
    {
        $game = $this->createTestGame();
        $this->assertInstanceOf(Knife::class, $game->getPlayer(1)->getEquippedItem());
        $game->getPlayer(1)->equip(InventorySlot::SLOT_KIT);
        $game->getPlayer(1)->equip(InventorySlot::SLOT_TASER);
        $this->assertInstanceOf(Knife::class, $game->getPlayer(1)->getEquippedItem()); // @phpstan-ignore-line
    }

    public function testPlayerInventorySlots(): void
    {
        $p = new Player(1, Color::GREEN, false);
        $pistol = $p->getEquippedItem();
        $p->equipKnife();
        $knife = $p->getEquippedItem();
        $this->assertInstanceOf(Knife::class, $knife);
        $this->assertInstanceOf(PistolUsp::class, $pistol);
        $expectedSlots = [
            InventorySlot::SLOT_KNIFE->value => $knife->toArray(),
            InventorySlot::SLOT_SECONDARY->value => $pistol->toArray(),
        ];
        $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value));
        $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_PRIMARY->value));
        $this->assertSame($expectedSlots, $p->getInventory()->getFilledSlots());
    }

    public function testPlayerCantBuyItemWithoutEnoughMoney(): void
    {
        $playerCommands = [
            fn(Player $p) => $p->buyItem(BuyMenuItem::RIFLE_AK),
            fn(Player $p) => $p->equipPrimaryWeapon(),
        ];

        $game = $this->createOneRoundGame(count($playerCommands));
        $this->assertSame(0, $game->getPlayer(1)->getMoney());
        $this->playPlayer($game, $playerCommands);
        $this->assertSame(0, $game->getPlayer(1)->getMoney());
        $this->assertTrue($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_BOMB->value));

        $knife = $game->getPlayer(1)->getEquippedItem();
        $this->assertInstanceOf(Knife::class, $knife);
        $this->assertFalse($knife->isUserDroppable());
        $knife->setSkinId(123);
        $this->assertSame(123, $knife->getSkinId());
    }

    public function testPlayerBuyAndDropPrimaryWithEnoughMoney(): void
    {
        $startMoney = 2800;
        $akPrice = 2700;

        $playerCommands = [
            fn(Player $p) => $p->buyItem(BuyMenuItem::RIFLE_AK),
            fn(Player $p) => $p->equipPrimaryWeapon(),
        ];

        $game = $this->createOneRoundGame(count($playerCommands), [
            GameProperty::START_MONEY => $startMoney,
        ]);
        $this->assertSame($startMoney, $game->getPlayer(1)->getMoney());
        $this->playPlayer($game, $playerCommands);
        $this->assertSame($startMoney - $akPrice, $game->getPlayer(1)->getMoney());

        $item = $game->getPlayer(1)->getEquippedItem();
        $this->assertInstanceOf(RifleAk::class, $item);
        $this->assertSame($akPrice, $item->getPrice());
        $this->assertTrue($item->isUserDroppable());
        $game->getPlayer(1)->dropEquippedItem();

        $knife = $game->getPlayer(1)->getEquippedItem();
        $this->assertInstanceOf(Knife::class, $knife);
        $this->assertFalse($knife->isUserDroppable());
        $game->getPlayer(1)->dropEquippedItem();
    }

    public function testDropResetEquipped(): void
    {
        $game = $this->createNoPauseGame();
        $glock = null;
        $this->playPlayer($game, [
            fn(Player $p) => $p->equipSecondaryWeapon(),
            fn(Player $p) => $this->assertFalse($p->getEquippedItem()->isEquipped()),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs),
            fn(Player $p) => $this->assertTrue($p->getEquippedItem()->isEquipped()),
            function (Player $p) use (&$glock) {
                $glock = $p->getEquippedItem();
            },
            fn(Player $p) => $this->assertNotNull($p->dropEquippedItem()),
            fn(Player $p) => $this->assertFalse($p->getEquippedItem()->isEquipped()),
            $this->endGame(),
        ]);

        $this->assertInstanceOf(PistolGlock::class, $glock);
        $this->assertFalse($glock->isEquipped());
        $this->assertFalse($glock->isReloading());
        $this->assertTrue($glock->isUserDroppable());
    }

    public function testPlayerGetPistolOnRoundStartIfHasNone(): void
    {
        $game = $this->createNoPauseGame(10);
        $p2 = new Player(2, Color::GREEN, false);
        $game->addPlayer($p2);
        $p2->setPosition(new Point(6000, 0, 6000));
        $p2->dropItemFromSlot(InventorySlot::SLOT_SECONDARY->value);
        $p2->buyItem(BuyMenuItem::DEFUSE_KIT);
        $p2->buyItem(BuyMenuItem::GRENADE_SMOKE);

        $this->playPlayer($game, [
            fn(Player $p) => $this->assertFalse($game->getScore()->attackersIsWinning()),
            fn(Player $p) => $this->assertFalse($game->getScore()->defendersIsWinning()),
            fn(Player $p) => $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            fn(Player $p) => $this->assertTrue($p->equip(InventorySlot::SLOT_SECONDARY)),
            fn(Player $p) => $this->assertInstanceOf(PistolGlock::class, $p->getEquippedItem()),
            fn(Player $p) => $p->setPosition(new Point(9999, 0, 9999)),
            fn(Player $p) => $this->assertNotNull($p->dropEquippedItem()),
            fn(Player $p) => $p->setPosition(new Point(500, 0, 500)),
            fn(Player $p) => $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            fn(Player $p) => $this->assertSame(1, $game->getRoundNumber()),
            fn(Player $p) => $game->getPlayer(2)->suicide(),
            fn(Player $p) => $this->assertSame(2, $game->getRoundNumber()),
            fn(Player $p) => $this->assertTrue($game->getScore()->attackersIsWinning()),
            fn(Player $p) => $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            fn(Player $p) => $this->assertTrue($p->equip(InventorySlot::SLOT_SECONDARY)),
            fn(Player $p) => $this->assertInstanceOf(PistolGlock::class, $p->getEquippedItem()),
            $this->endGame(),
        ]);

        $this->assertSame(0, $game->getScore()->getPlayerStat(1)->getDeaths());
        $this->assertSame(0, $game->getScore()->getPlayerStat(1)->getKills());
        $this->assertSame(1, $game->getScore()->getPlayerStat(2)->getDeaths());
        $this->assertSame(-1, $game->getScore()->getPlayerStat(2)->getKills());
        $this->assertTrue($p2->getInventory()->has(InventorySlot::SLOT_SECONDARY->value));
    }

    public function testPlayerBuyAndDropAndUseForPickup(): void
    {
        $game = $this->createTestGame();
        $p = $game->getPlayer(1);
        $p->getInventory()->earnMoney(15000);

        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->lookHorizontal(0),
            fn(Player $p) => $this->assertEmpty($game->getWorld()->getDropItems()),
            fn(Player $p) => $p->getSight()->lookVertical(-60),
            fn(Player $p) => $this->assertInstanceOf(Knife::class, $p->getEquippedItem()),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            fn(Player $p) => $this->assertNotNull($p->dropEquippedItem()),
            $this->waitNTicks(200),
            fn(Player $p) => $this->assertNotEmpty($game->getWorld()->getDropItems()),
            fn(Player $p) => $p->use(),
            fn(Player $p) => $this->assertEmpty($game->getWorld()->getDropItems()),
            fn(Player $p) => $this->assertInstanceOf(Knife::class, $p->getEquippedItem()),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::RIFLE_AK)),
            fn(Player $p) => $this->assertInstanceOf(RifleAk::class, $p->getEquippedItem()),
            fn(Player $p) => $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_PRIMARY->value)),
            fn(Player $p) => $this->assertNotNull($p->dropEquippedItem()),
            fn(Player $p) => $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_PRIMARY->value)),
            $this->waitNTicks(200),
            fn(Player $p) => $this->assertNotEmpty($game->getWorld()->getDropItems()),
            fn(Player $p) => $p->getSight()->lookVertical(40),
            fn(Player $p) => $p->use(),
            fn(Player $p) => $this->assertNotEmpty($game->getWorld()->getDropItems()),
            fn(Player $p) => $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_PRIMARY->value)),
            fn(Player $p) => $p->getSight()->lookVertical(-70),
            fn(Player $p) => $p->use(),
            fn(Player $p) => $this->assertEmpty($game->getWorld()->getDropItems()),
            fn(Player $p) => $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_PRIMARY->value)),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::RIFLE_AWP)),
            $this->waitNTicks(200),
            fn(Player $p) => $this->assertCount(1, $game->getWorld()->getDropItems()),
            fn(Player $p) => $this->assertInstanceOf(RifleAWP::class, $p->getEquippedItem()),
            fn(Player $p) => $p->use(),
            $this->waitNTicks(200),
            fn(Player $p) => $this->assertInstanceOf(RifleAk::class, $p->getEquippedItem()),
            fn(Player $p) => $p->dropEquippedItem(),
            fn(Player $p) => $p->getSight()->lookHorizontal(45),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            fn(Player $p) => $p->dropEquippedItem(),
            $this->waitNTicks(200),
            fn(Player $p) => $this->assertCount(3, $game->getWorld()->getDropItems()),
            fn(Player $p) => $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            fn(Player $p) => $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_PRIMARY->value)),
            fn(Player $p) => $p->getSight()->lookHorizontal(45),
            fn(Player $p) => $p->use(),
            fn(Player $p) => $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            // non primary nor secondary item already had in inventory pickup
            fn(Player $p) => $p->getSight()->lookVertical(-90),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_FLASH)),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_FLASH)),
            fn(Player $p) => $this->assertSame(2, $p->getEquippedItem()->getQuantity()),
            fn(Player $p) => $this->assertNotNull($p->dropEquippedItem()),
            fn(Player $p) => $this->assertSame(1, $p->getEquippedItem()->getQuantity()),
            fn(Player $p) => $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_GRENADE_FLASH->value)),
            $this->waitNTicks(200),
            fn(Player $p) => $this->assertSame(1, $p->getEquippedItem()->getQuantity()),
            fn(Player $p) => $p->use(),
            fn(Player $p) => $this->assertSame(2, $p->getEquippedItem()->getQuantity()),
            $this->endGame(),
        ]);
    }

    public function testDropAndPickupItem(): void
    {
        $game = $this->createNoPauseGame();
        $game->getPlayer(1)->equipSecondaryWeapon();
        $glock = $game->getPlayer(1)->getEquippedItem();
        $this->assertInstanceOf(PistolGlock::class, $glock);

        $this->playPlayer($game, [
            fn() => $this->assertCount(0, $game->getWorld()->getDropItems()),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_DECOY)),
            fn(Player $p) => $p->getSight()->look(90, -10),
            fn(Player $p) => $p->dropEquippedItem(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs),
            fn(Player $p) => $this->assertNotNull($p->attack()),
            fn(Player $p) => $p->getSight()->look(0, -90),
            function (Player $p) use ($glock) {
                $dropItem = $p->dropEquippedItem();
                $this->assertInstanceOf(PistolGlock::class, $dropItem);
                $this->assertSame(PistolGlock::magazineCapacity - 1, $dropItem->getAmmo());
                $this->assertSame($glock, $dropItem);
            },
            fn(Player $p) => $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_GRENADE_DECOY->value)),
            fn(Player $p) => $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            $this->waitNTicks(200),
            fn() => $this->assertCount(2, $game->getWorld()->getDropItems()),
            fn(Player $p) => $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            fn() => $this->assertCount(1, $game->getWorld()->getDropItems()),
            fn(Player $p) => $this->assertInstanceOf(Knife::class, $p->getEquippedItem()),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            function (Player $p) use ($glock) {
                $equippedItem = $p->getEquippedItem();
                $this->assertInstanceOf(PistolGlock::class, $equippedItem);
                $this->assertSame(PistolGlock::magazineCapacity - 1, $equippedItem->getAmmo());
                $this->assertSame($glock, $equippedItem);
            },
            fn(Player $p) => $p->getSight()->look(90, -10),
            fn(Player $p) => $p->dropEquippedItem(),
            fn(Player $p) => $p->moveLeft(),
            $this->waitNTicks(200),
            fn() => $this->assertCount(2, $game->getWorld()->getDropItems()),
            fn(Player $p) => $p->moveLeft(),
            fn() => $this->assertCount(2, $game->getWorld()->getDropItems()),
            $this->endGame(),
        ]);

        $this->assertSame(PistolGlock::magazineCapacity - 1, $glock->getAmmo());
    }

    public function testDropOnlyLastEquippedGrenadeOnDead(): void
    {
        $game = $this->createNoPauseGame();
        $game->addPlayer(new Player(2, Color::GREEN, false));

        $this->playPlayer($game, [
            fn(Player $p) => $p->getInventory()->earnMoney(9000),
            fn(Player $p) => $p->setPosition(new Point(500, 0, 500)),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_MOLOTOV)),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_SMOKE)),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_HE)),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_DECOY)),
            fn(Player $p) => $p->suicide(),
            $this->endGame(),
        ]);

        $dropItems = $game->getWorld()->getDropItems();
        $this->assertCount(3, $dropItems);
        $this->assertInstanceOf(PistolGlock::class, $dropItems[0]->getItem());
        $this->assertInstanceOf(Bomb::class, $dropItems[1]->getItem());
        $this->assertInstanceOf(Decoy::class, $dropItems[2]->getItem());
    }

    public function testDropAndInstantPickupItem(): void
    {
        $game = $this->createNoPauseGame();
        $this->playPlayer($game, [
            fn(Player $p) => $p->setPosition(new Point(500, 0, 500)),
            fn(Player $p) => $p->getSight()->lookVertical(91),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs),
            fn(Player $p) => $this->assertInstanceOf(PistolGlock::class, $p->dropEquippedItem()),
            fn(Player $p) => $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            $this->waitNTicks(600),
            fn(Player $p) => $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            fn(Player $p) => $this->assertInstanceOf(Knife::class, $p->getEquippedItem()),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            fn(Player $p) => $this->assertInstanceOf(PistolGlock::class, $p->getEquippedItem()),
            $this->endGame(),
        ]);

        $reflection = new ReflectionProperty(World::class, 'dropItems');
        $this->assertSame([], $reflection->getValue($game->getWorld()));
    }

    public function testDropToOtherPlayer(): void
    {
        $game = $this->createNoPauseGame();
        $this->assertFalse($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_PRIMARY->value));
        $game->addPlayer(new Player(2, Color::GREEN, false));
        $game->getPlayer(2)->setPosition(new Point(150, 0, 150));

        $pickEvent = null;
        $dropAirCount = 0;
        $game->onEvents(function (array $events) use (&$pickEvent, &$dropAirCount): void {
            foreach ($events as $event) {
                if (false === ($event instanceof SoundEvent)) {
                    return;
                }

                if ($event->type === SoundType::ITEM_DROP_AIR) {
                    $dropAirCount++;
                }
                if ($event->type === SoundType::ITEM_PICKUP) {
                    $this->assertNull($pickEvent);
                    $pickEvent = $event;
                    $this->assertSame(1, $event->getPlayerId());
                    $this->assertInstanceOf(RifleM4A4::class, $event->getItem());
                }
            }
        });

        $this->playPlayer($game, [
            fn(Player $p) => $p->getInventory()->earnMoney(5000),
            fn(Player $p) => $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_PRIMARY->value)),
            fn(Player $p) => $this->assertFalse($p->buyItem(BuyMenuItem::RIFLE_AK)),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::RIFLE_M4A4)),
            fn(Player $p) => $p->getSight()->look(220, -15),
            fn(Player $p) => $this->assertInstanceOf(RifleM4A4::class, $p->dropEquippedItem()),
            fn(Player $p) => $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_PRIMARY->value)),
            fn(Player $p) => $this->assertInstanceOf(PistolUsp::class, $p->getEquippedItem()),
            fn(Player $p) => $p->equipPrimaryWeapon(),
            fn(Player $p) => $this->assertInstanceOf(PistolUsp::class, $p->getEquippedItem()),
            $this->waitNTicks(1200),
            $this->endGame(),
        ], 2);

        $this->assertTrue($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_PRIMARY->value));
        $this->assertNotNull($pickEvent);
        $this->assertSame(10, $dropAirCount);
    }

    public function testDropWallCollision(): void
    {
        $game = $this->createNoPauseGame();
        $game->addPlayer(new Player(2, Color::GREEN, false));
        $game->getPlayer(2)->setPosition(new Point(150, 0, 150));
        $wall = new Wall(new Point(80), false, 500);
        $game->getWorld()->addWall($wall);

        $game->getPlayer(1)->equipSecondaryWeapon();
        $game->getPlayer(1)->dropEquippedItem();
        $this->assertFalse($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_SECONDARY->value));

        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(220, -15),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolUsp::equipReadyTimeMs),
            fn(Player $p) => $this->assertInstanceOf(PistolUsp::class, $p->dropEquippedItem()),
            fn(Player $p) => $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            fn(Player $p) => $this->assertInstanceOf(Knife::class, $p->getEquippedItem()),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            fn(Player $p) => $this->assertInstanceOf(Knife::class, $p->getEquippedItem()),
            $this->waitNTicks(100),
            $this->endGame(),
        ], 2);

        $this->assertFalse($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_SECONDARY->value));
        $dropItems = $game->getWorld()->getDropItems();
        $this->assertCount(2, $dropItems);
        $this->assertSame($wall->getBase() + 1, $dropItems[1]->getPosition()->addX(-$dropItems[1]->getBoundingRadius())->x);
    }

    public function testDropBoxCollisionNotPickUp(): void
    {
        $game = $this->createNoPauseGame();
        $player = $game->getPlayer(1);
        $box = new Box(new Point(500, 0, 500), 800, 600, 200);
        $game->getWorld()->addBox($box);

        $this->playPlayer($game, [
            fn(Player $p) => $p->setPosition($box->getBase()->clone()->addZ(-($player->getBoundingRadius() + 10))->addX(200)),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs),
            fn(Player $p) => $this->assertInstanceOf(PistolGlock::class, $p->dropEquippedItem()),
            $this->waitNTicks(200),
            fn(Player $p) => $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            $this->endGame(),
        ]);
    }

    public function testDropBoxCollisionBuySameItemAgain(): void
    {
        $game = $this->createNoPauseGame();
        $player = $game->getPlayer(1);
        $box = new Box(new Point(-100, 0, $player->getPositionClone()->z + $player->getBoundingRadius() + 10), 200, 200, 200);
        $game->getWorld()->addBox($box);

        $this->assertSame(800, $player->getMoney());
        $this->playPlayer($game, [
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::PISTOL_GLOCK)),
            fn(Player $p) => $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            $this->waitNTicks(300),
            fn(Player $p) => $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            $this->endGame(),
        ]);
        $this->assertCount(1, $game->getWorld()->getDropItems());
        $this->assertSame(800 - 200, $player->getMoney());
    }

    public function testDropItemWhenDiedMidAir(): void
    {
        $property = $this->createNoPauseGameProperty(5);
        $property->round_end_cool_down_sec = 2;
        $game = $this->createTestGame(null, $property);
        $game->getPlayer(1)->setPosition(new Point(100, 200, 100));

        $this->playPlayer($game, [
            fn(Player $p) => $this->assertTrue($p->isAlive()),
            fn(Player $p) => $this->assertEmpty($game->getWorld()->getDropItems()),
            fn(Player $p) => $p->suicide(),
            fn(Player $p) => $this->assertGreaterThan(0, $p->getPositionClone()->y),
            fn(Player $p) => $this->assertFalse($p->isAlive()),
            fn(Player $p) => $this->assertNotEmpty($game->getWorld()->getDropItems()),
            $this->endGame(),
        ]);
        $this->assertCount(2, $game->getWorld()->getDropItems());
        foreach ($game->getWorld()->getDropItems() as $item) {
            $this->assertSame(0, $item->getPosition()->y);
        }
    }

    public function testPlayerCannotBuyAfterPauseAndBuyTime(): void
    {
        $property = new GameProperty();
        $property->freeze_time_sec = 0;
        $property->buy_time_sec = 0;
        $property->start_money = 16000;
        $game = $this->createTestGame(6, $property);
        $this->playPlayer($game, [
            $this->waitXTicks(2),
            fn(Player $p) => $p->buyItem(BuyMenuItem::RIFLE_AK),
            $this->endGame(),
        ]);
        $this->assertFalse($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_PRIMARY->value));
    }

    public function testPlayerBuyWeapons(): void
    {
        $startMoney = 10901;
        $akPrice = 2700;

        $playerCommands = [
            fn(Player $p) => $this->assertFalse($p->buyItem(BuyMenuItem::RIFLE_M4A4)),
            fn(Player $p) => $this->assertFalse($p->buyItem(BuyMenuItem::PISTOL_USP)),
            fn(Player $p) => $p->buyItem(BuyMenuItem::RIFLE_AK),
            fn(Player $p) => $p->buyItem(BuyMenuItem::RIFLE_AK),
            fn(Player $p) => $p->buyItem(BuyMenuItem::RIFLE_AK),
            fn(Player $p) => $p->buyItem(BuyMenuItem::RIFLE_AK),
        ];

        $game = $this->createOneRoundGame(count($playerCommands), [
            GameProperty::START_MONEY => $startMoney,
        ]);
        $this->assertSame($startMoney, $game->getPlayer(1)->getMoney());
        $this->playPlayer($game, $playerCommands);
        $this->assertSame(101, $game->getPlayer(1)->getMoney());

        $ak = $game->getPlayer(1)->getEquippedItem();
        $this->assertInstanceOf(RifleAk::class, $ak);
        $this->assertSame($akPrice, $ak->getPrice());
        $this->assertFalse($game->getPlayer(1)->getInventory()->canBuy($ak));
    }

    public function testDropFromSlot(): void
    {
        $game = $this->createTestGame();
        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->lookVertical(90),
            fn(Player $p) => $p->getInventory()->earnMoney(5000),
            function (Player $p) {
                $this->assertFalse($p->dropItemFromSlot(InventorySlot::SLOT_KNIFE->value));
                $this->assertFalse($p->dropItemFromSlot(InventorySlot::SLOT_PRIMARY->value));
            },
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::RIFLE_AK)),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_DECOY)),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            function (Player $p) {
                $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_KNIFE->value));
                $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_PRIMARY->value));
                $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value));
                $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_GRENADE_DECOY->value));
                $grenadeSlots = $p->getInventory()->getLastEquippedGrenadeSlots();
                $this->assertSame(InventorySlot::SLOT_GRENADE_DECOY->value, array_shift($grenadeSlots));

                $this->assertFalse($p->dropItemFromSlot(InventorySlot::SLOT_KIT->value));
                $this->assertFalse($p->dropItemFromSlot(InventorySlot::SLOT_KNIFE->value));
                $this->assertTrue($p->dropItemFromSlot(InventorySlot::SLOT_PRIMARY->value));
                $this->assertTrue($p->dropItemFromSlot(InventorySlot::SLOT_GRENADE_DECOY->value));

                $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_KNIFE->value));
                $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value));
                $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_PRIMARY->value));
                $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_GRENADE_DECOY->value));
                $grenadeSlots = $p->getInventory()->getLastEquippedGrenadeSlots();
                $this->assertSame(InventorySlot::SLOT_GRENADE_SMOKE->value, array_shift($grenadeSlots));
            },
            $this->waitNTicks(1000),
            fn(Player $p) => $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_PRIMARY->value)),
            $this->endGame(),
        ]);
    }

    public function testIncrementItemQuantity(): void
    {
        $game = $this->createTestGame();
        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->look(0, 90),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_FLASH)),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_FLASH)),
            fn(Player $p) => $this->assertInstanceOf(Flashbang::class, $p->getEquippedItem()),
            fn(Player $p) => $this->assertSame(2, $p->getEquippedItem()->getQuantity()),
            fn(Player $p) => $this->assertNotNull($p->dropEquippedItem()),
            fn(Player $p) => $this->assertInstanceOf(Flashbang::class, $p->getEquippedItem()),
            fn(Player $p) => $this->assertSame(1, $p->getEquippedItem()->getQuantity()),
            $this->waitNTicks(1000),
            fn(Player $p) => $this->assertSame(2, $p->getEquippedItem()->getQuantity()),
            $this->endGame(),
        ]);
    }

    public function testPlayerBuyTwoFlashes(): void
    {
        $startMoney = 600;
        $itemPrice = 200;

        $playerCommands = [
            fn(Player $p) => $p->buyItem(BuyMenuItem::GRENADE_FLASH),
            fn(Player $p) => $p->buyItem(BuyMenuItem::GRENADE_FLASH),
            fn(Player $p) => $p->buyItem(BuyMenuItem::GRENADE_FLASH),
        ];

        $game = $this->createOneRoundGame(count($playerCommands), [
            GameProperty::START_MONEY => $startMoney,
        ]);
        $this->playPlayer($game, $playerCommands);
        $this->assertSame($itemPrice, $game->getPlayer(1)->getMoney());

        $item = $game->getPlayer(1)->getEquippedItem();
        $this->assertInstanceOf(Flashbang::class, $item);
        $this->assertSame($itemPrice, $item->getPrice());
        $this->assertFalse($game->getPlayer(1)->getInventory()->canBuy($item));
        $this->assertSame(2, $item->getQuantity());
        $this->assertSame(2, $game->getPlayer(1)->serialize()['slots'][InventorySlot::SLOT_GRENADE_FLASH->value]['pcs'] ?? false); // @phpstan-ignore-line

        $flashBang1 = $game->getPlayer(1)->dropEquippedItem();
        $this->assertSame(1, $game->getPlayer(1)->serialize()['slots'][InventorySlot::SLOT_GRENADE_FLASH->value]['pcs'] ?? false); // @phpstan-ignore-line
        $this->assertInstanceOf(Flashbang::class, $flashBang1);
        $flashBang2 = $game->getPlayer(1)->dropEquippedItem();
        $this->assertInstanceOf(Flashbang::class, $flashBang2);
        $this->assertFalse($flashBang1 === $flashBang2);
        $this->assertTrue($item === $flashBang2);
        $this->assertSame(1, $flashBang1->getQuantity());
        $this->assertSame(1, $flashBang2->getQuantity());
        $this->assertNull($game->getPlayer(1)->serialize()['slots'][InventorySlot::SLOT_GRENADE_FLASH->value]['pcs'] ?? null); // @phpstan-ignore-line
    }

    public function testPlayerBuyMaxFourGrenades(): void
    {
        $startMoney = 6000;

        $playerCommands = [
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_SMOKE)),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_MOLOTOV)),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_FLASH)),
            fn(Player $p) => $this->assertTrue($p->buyItem(BuyMenuItem::GRENADE_FLASH)),
            #
            fn(Player $p) => $this->assertFalse($p->buyItem(BuyMenuItem::GRENADE_SMOKE)),
            fn(Player $p) => $this->assertFalse($p->buyItem(BuyMenuItem::GRENADE_MOLOTOV)),
            fn(Player $p) => $this->assertFalse($p->buyItem(BuyMenuItem::GRENADE_DECOY)),
            fn(Player $p) => $this->assertFalse($p->buyItem(BuyMenuItem::GRENADE_FLASH)),
            fn(Player $p) => $this->assertFalse($p->buyItem(BuyMenuItem::GRENADE_HE)),
        ];

        $game = $this->createOneRoundGame(count($playerCommands), [
            GameProperty::START_MONEY => $startMoney,
        ]);
        $this->playPlayer($game, $playerCommands);
        $this->assertSame(6000 - 1100, $game->getPlayer(1)->getMoney());
        $this->assertCount(6, $game->getPlayer(1)->getInventory()->getItems());
        foreach ($game->getPlayer(1)->getInventory()->getItems() as $item) {
            $this->assertNotInstanceOf(HighExplosive::class, $item);
            $this->assertNotInstanceOf(Decoy::class, $item);
        }
    }

    public function testCancelReload(): void
    {
        $game = $this->createNoPauseGame();

        $reloadEventCount = 0;
        $game->onEvents(function (array $events) use (&$reloadEventCount): void {
            foreach ($events as $event) {
                if ($event instanceof SoundEvent && $event->type === SoundType::ITEM_RELOAD) {
                    $reloadEventCount++;
                }
            }
        });

        $this->playPlayer($game, [
            fn(Player $p) => $p->getInventory()->earnMoney(6123),
            fn(Player $p) => $p->getSight()->lookVertical(-90),
            fn(Player $p) => $p->buyItem(BuyMenuItem::RIFLE_AK),
            function (Player $p): void {
                $this->assertNull($p->attack());
                $ak = $p->getEquippedItem();
                $this->assertInstanceOf(RifleAk::class, $ak);
                $this->assertFalse($ak->isEquipped());
                $this->assertSame(RifleAk::magazineCapacity, $ak->getAmmo());
            },
            $this->waitNTicks(RifleAk::equipReadyTimeMs),
            function (Player $p): void {
                $ak = $p->getEquippedItem();
                $this->assertInstanceOf(RifleAk::class, $ak);
                $this->assertTrue($ak->isEquipped());
                $this->assertSame(RifleAk::magazineCapacity, $ak->getAmmo());
            },
            fn(Player $p) => $this->assertNotNull($p->attack()),
            fn(Player $p) => $p->reload(),
            fn(Player $p) => $this->assertFalse($game->getWorld()->canAttack($p)),
            fn(Player $p) => $p->equipKnife(),
            fn(Player $p) => $p->equipPrimaryWeapon(),
            $this->waitNTicks(max(RifleAk::reloadTimeMs, RifleAk::equipReadyTimeMs)),
            $this->endGame(),
        ]);

        $ak = $game->getPlayer(1)->getEquippedItem();
        $this->assertInstanceOf(RifleAk::class, $ak);
        $this->assertSame(RifleAk::magazineCapacity - 1, $ak->getAmmo());
        $this->assertSame(1, $reloadEventCount);
    }

    public function testKevlarBuy(): void
    {
        $playerCommands = [
            function (Player $p): void {
                $this->assertSame(2301, $p->getMoney());
                $this->assertSame(ArmorType::NONE, $p->getArmorType());
                $this->assertSame(0, $p->getArmorValue());
            },
            fn(Player $p) => $p->buyItem(BuyMenuItem::KEVLAR_BODY),
            fn(Player $p) => $p->buyItem(BuyMenuItem::KEVLAR_BODY),
            fn(Player $p) => $this->assertNull($p->getInventory()->equip(InventorySlot::SLOT_KEVLAR)),
            function (Player $p): void {
                $this->assertSame(1651, $p->getMoney());
                $this->assertSame(ArmorType::BODY, $p->getArmorType());
                $this->assertSame(100, $p->getArmorValue());
                $kevlar = $p->getInventory()->getItemSlot(InventorySlot::SLOT_KEVLAR);
                $this->assertInstanceOf(Kevlar::class, $kevlar);
                $this->assertFalse($kevlar->isUserDroppable());
                $this->assertFalse($kevlar->canPurchaseMultipleTime($kevlar));
            },
            fn(Player $p) => $p->lowerArmor(10),
            function (Player $p): void {
                $this->assertSame(ArmorType::BODY, $p->getArmorType());
                $this->assertSame(90, $p->getArmorValue());
            },
            fn(Player $p) => $p->buyItem(BuyMenuItem::KEVLAR_BODY),
            function (Player $p): void {
                $this->assertSame(1001, $p->getMoney());
                $this->assertSame(ArmorType::BODY, $p->getArmorType());
                $this->assertSame(100, $p->getArmorValue());
            },
            fn(Player $p) => $p->buyItem(BuyMenuItem::KEVLAR_BODY),
            fn(Player $p) => $p->buyItem(BuyMenuItem::KEVLAR_BODY_AND_HEAD),
            function (Player $p): void {
                $this->assertSame(651, $p->getMoney());
                $this->assertSame(ArmorType::BODY_AND_HEAD, $p->getArmorType());
                $this->assertSame(100, $p->getArmorValue());
            },
            fn(Player $p) => $p->lowerArmor(10),
            fn(Player $p) => $p->buyItem(BuyMenuItem::KEVLAR_BODY),
            fn(Player $p) => $p->buyItem(BuyMenuItem::KEVLAR_BODY),
            function (Player $p): void {
                $this->assertSame(1, $p->getMoney());
                $this->assertSame(ArmorType::BODY_AND_HEAD, $p->getArmorType());
                $this->assertSame(100, $p->getArmorValue());
            },
        ];

        $this->simulateGame($playerCommands, [GameProperty::START_MONEY => 3 * 650 + 350 + 1]);
    }

}
