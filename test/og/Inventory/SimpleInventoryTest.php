<?php

namespace Test\Inventory;

use cs\Core\Box;
use cs\Core\GameProperty;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Wall;
use cs\Enum\ArmorType;
use cs\Enum\BuyMenuItem;
use cs\Enum\Color;
use cs\Enum\InventorySlot;
use cs\Equipment\Decoy;
use cs\Equipment\Flashbang;
use cs\Equipment\HighExplosive;
use cs\Weapon\Knife;
use cs\Weapon\PistolGlock;
use cs\Weapon\PistolUsp;
use cs\Weapon\RifleAk;
use Test\BaseTestCase;

class SimpleInventoryTest extends BaseTestCase
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

    public function testPlayerInventorySlots(): void
    {
        $p = new Player(1, Color::GREEN, false);
        $pistol = $p->getEquippedItem();
        $p->equipKnife();
        $knife = $p->getEquippedItem();
        $this->assertInstanceOf(Knife::class, $knife);
        $this->assertInstanceOf(PistolUsp::class, $pistol);
        $expectedSlots = [
            InventorySlot::SLOT_KNIFE->value     => $knife->toArray(),
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

    public function testPlayerBuyAndDropAndUseForPickup(): void
    {
        $game = $this->createTestGame();
        $p = $game->getPlayer(1);
        $p->getInventory()->earnMoney(5000);

        $this->assertTrue($p->buyItem(BuyMenuItem::RIFLE_AK));
        $item = $p->getEquippedItem();
        $this->assertInstanceOf(RifleAk::class, $item);
        $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_PRIMARY->value));
        $this->assertNotNull($p->dropEquippedItem());
        $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_PRIMARY->value));
        $this->assertNotEmpty($game->getWorld()->getDropItems());

        $p->getSight()->lookVertical(40);
        $p->use();
        $this->assertNotEmpty($game->getWorld()->getDropItems());
        $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_PRIMARY->value));

        $p->getSight()->lookVertical(-40);
        $p->use();
        $this->assertSame([], $game->getWorld()->getDropItems());
        $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_PRIMARY->value));
    }

    public function testDropAndPickupItem(): void
    {
        $game = $this->createNoPauseGame();
        $this->playPlayer($game, [
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs),
            fn(Player $p) => $this->assertInstanceOf(PistolGlock::class, $p->dropEquippedItem()),
            fn(Player $p) => $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            fn(Player $p) => $this->assertInstanceOf(Knife::class, $p->getEquippedItem()),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            fn(Player $p) => $this->assertInstanceOf(PistolGlock::class, $p->getEquippedItem()),
            $this->endGame(),
        ]);
    }

    public function testDropAndInstantPickupItem(): void
    {
        $game = $this->createNoPauseGame();
        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->lookVertical(90),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs),
            fn(Player $p) => $this->assertInstanceOf(PistolGlock::class, $p->dropEquippedItem()),
            fn(Player $p) => $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            fn(Player $p) => $this->assertInstanceOf(Knife::class, $p->getEquippedItem()),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            fn(Player $p) => $this->assertInstanceOf(PistolGlock::class, $p->getEquippedItem()),
            $this->endGame(),
        ]);
    }

    public function testDropToOtherPlayer(): void
    {
        $game = $this->createNoPauseGame();
        $game->addPlayer(new Player(2, Color::GREEN, false));
        $game->getPlayer(2)->setPosition(new Point(150, 0, 150));

        $game->getPlayer(1)->equipSecondaryWeapon();
        $game->getPlayer(1)->dropEquippedItem();
        $this->assertFalse($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_SECONDARY->value));

        $this->playPlayer($game, [
            fn(Player $p) => $p->getSight()->lookAt(220, -15),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolUsp::equipReadyTimeMs),
            fn(Player $p) => $this->assertInstanceOf(PistolUsp::class, $p->dropEquippedItem()),
            fn(Player $p) => $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            fn(Player $p) => $this->assertInstanceOf(Knife::class, $p->getEquippedItem()),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            fn(Player $p) => $this->assertInstanceOf(Knife::class, $p->getEquippedItem()),
            $this->endGame(),
        ], 2);

        $this->assertTrue($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_SECONDARY->value));
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
            fn(Player $p) => $p->getSight()->lookAt(220, -15),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolUsp::equipReadyTimeMs),
            fn(Player $p) => $this->assertInstanceOf(PistolUsp::class, $p->dropEquippedItem()),
            fn(Player $p) => $this->assertFalse($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            fn(Player $p) => $this->assertInstanceOf(Knife::class, $p->getEquippedItem()),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            fn(Player $p) => $this->assertInstanceOf(Knife::class, $p->getEquippedItem()),
            $this->endGame(),
        ], 2);

        $this->assertFalse($game->getPlayer(1)->getInventory()->has(InventorySlot::SLOT_SECONDARY->value));
        $dropItems = $game->getWorld()->getDropItems();
        $this->assertCount(2, $dropItems);
        $this->assertSame($wall->getBase() + 1, $dropItems[1]->getPosition()->addX(-$dropItems[1]->getBoundingRadius())->x);
    }

    public function testDropBoxCollision(): void
    {
        $game = $this->createNoPauseGame();
        $player = $game->getPlayer(1);
        $box = new Box(new Point(-100, 0, $player->getPositionClone()->z + $player->getBoundingRadius() + 10), 200, 200, 200);
        $game->getWorld()->addBox($box);

        $this->playPlayer($game, [
            fn(Player $p) => $p->moveForward(),
            fn(Player $p) => $p->equipSecondaryWeapon(),
            $this->waitNTicks(PistolGlock::equipReadyTimeMs),
            fn(Player $p) => $this->assertInstanceOf(PistolGlock::class, $p->dropEquippedItem()),
            fn(Player $p) => $this->assertTrue($p->getInventory()->has(InventorySlot::SLOT_SECONDARY->value)),
            $this->endGame(),
        ]);
    }

    public function testDropBoxCollisionBuy(): void
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
            $this->endGame(),
        ]);
        $this->assertCount(1, $game->getWorld()->getDropItems());
        $this->assertSame(800 - 200, $player->getMoney());
    }

    public function testPlayerBuyWeapons(): void
    {
        $startMoney = 10901;
        $akPrice = 2700;

        $playerCommands = [
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
        $playerCommands = [
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
            2,
            fn(Player $p) => $p->equipKnife(),
            fn(Player $p) => $p->equipPrimaryWeapon(),
            $this->waitNTicks(max(RifleAk::reloadTimeMs, RifleAk::equipReadyTimeMs)),
        ];

        $game = $this->simulateGame($playerCommands, [GameProperty::START_MONEY => 6123]);
        $ak = $game->getPlayer(1)->getEquippedItem();
        $this->assertInstanceOf(RifleAk::class, $ak);
        $this->assertSame(RifleAk::magazineCapacity - 1, $ak->getAmmo());
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
            function (Player $p): void {
                $this->assertSame(1651, $p->getMoney());
                $this->assertSame(ArmorType::BODY, $p->getArmorType());
                $this->assertSame(100, $p->getArmorValue());
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
