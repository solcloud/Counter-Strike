<?php

namespace Test\Unit;

use cs\Core\Game;
use cs\Core\GameException;
use cs\Core\GameFactory;
use cs\Core\Setting;
use cs\Enum\BuyMenuItem;
use cs\Enum\InventorySlot;
use cs\Equipment\Molotov;
use cs\Map\TestMap;
use cs\Net\ProtocolWriter;
use cs\Net\Server;
use cs\Net\ServerSetting;
use cs\Net\TestConnector;
use Test\BaseTest;

class ServerTest extends BaseTest
{
    /**
     * @param string[] $clientRequests
     * @return string[]
     */
    private function runTestServer(Game $game, array $clientRequests): array
    {
        $testNet = new TestConnector($clientRequests);
        $setting = new ServerSetting(1, 0);
        $server = new Server($game, $setting, $testNet);

        $ex = null;
        try {
            $server->start();
        } catch (GameException $ex) {
        }
        $this->assertInstanceOf(GameException::class, $ex);
        $this->assertSame($testNet::msg, $ex->getMessage());

        return $testNet->getResponses();
    }

    public function testServer(): void
    {
        $game = GameFactory::createDebug();
        $map = new TestMap();
        $game->loadMap($map);
        $spawnPos = $map->getSpawnPositionAttacker()[0];

        $clientRequests = [
            'login acode',
            'jump', // game is paused on zero tick so no register
            'forward',
            'right',
            'forward',
            "look 45 -20|crouch|buy " . BuyMenuItem::GRENADE_MOLOTOV->value,
        ];
        $responses = $this->runTestServer($game, $clientRequests);
        $player = $game->getPlayer(1);
        $this->assertCount(count($clientRequests) + 1, $responses);
        $this->assertGreaterThan($spawnPos->x, $player->getPositionClone()->x);
        $this->assertGreaterThan($spawnPos->z, $player->getPositionClone()->z);
        $moveSpeed = ($player->getPositionClone()->x - $spawnPos->x);
        $this->assertGreaterThan(0, $moveSpeed);
        $this->assertSame(0, $player->getPositionClone()->y);
        $this->assertSame($spawnPos->z + 2 * $moveSpeed, $player->getPositionClone()->z);
        $this->assertSame(45.0, $player->getSight()->getRotationHorizontal());
        $this->assertSame(-20.0, $player->getSight()->getRotationVertical());
        $this->assertInstanceOf(Molotov::class, $player->getInventory()->getItems()[InventorySlot::SLOT_GRENADE_MOLOTOV->value]);
        $this->assertLessThan(Setting::playerHeadHeightStand(), $player->getHeadHeight());
    }

}
