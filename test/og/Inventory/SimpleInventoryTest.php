<?php

namespace Test\Inventory;

use cs\Core\GameProperty;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Enum\BuyMenuItem;
use cs\Enum\InventorySlot;
use cs\Equipment\Decoy;
use cs\Equipment\Flashbang;
use cs\Equipment\HighExplosive;
use cs\Weapon\Knife;
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

}
