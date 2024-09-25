<?php

namespace Test\Unit;

use cs\Core\Game;
use cs\Core\GameException;
use cs\Core\GameFactory;
use cs\Core\GameProperty;
use cs\Core\Setting;
use cs\Core\Util;
use cs\Enum\BuyMenuItem;
use cs\Enum\GameOverReason;
use cs\Enum\InventorySlot;
use cs\Equipment\Molotov;
use cs\Event\EventList;
use cs\Event\GameOverEvent;
use cs\Event\GameStartEvent;
use cs\Map\TestMap;
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

    public function testServerInvalidLoginCodeBlocked(): void
    {
        $game = GameFactory::createDebug();
        $setting = new ServerSetting(1, 0, 'a', 'd', false, 0);
        $testNet = new TestConnector(['login some-invalid-code']);
        $server = new Server($game, $setting, $testNet);
        $this->assertSame(0, $server->getBlockedPlayersCount());
        $server->start();
        $gameOver = $game->tick($game->getTickId() + 1);
        $this->assertInstanceOf(GameOverEvent::class, $gameOver);
        $this->assertSame(GameOverReason::REASON_NOT_ALL_PLAYERS_CONNECTED, $gameOver->reason);
        $this->assertSame(1, $server->getBlockedPlayersCount());
        $this->assertCount(0, $testNet->getResponses());
    }

    public function testServerNotAllPlayersConnected(): void
    {
        $game = GameFactory::createDebug();
        $game->loadMap(new TestMap());
        $setting = new ServerSetting(2, 0, 'code', 'd', true, 0);
        $testNet = new TestConnector(['login code']);
        $server = new Server($game, $setting, $testNet);
        $server->start();

        $this->assertSame(0, $server->getBlockedPlayersCount());
        $serverResponses = $testNet->getResponses();
        $this->assertCount(2, $serverResponses);

        $gameStartMsg = $serverResponses[0];
        $gameStartEvent = json_decode($gameStartMsg, true);
        $this->assertIsArray($gameStartEvent);
        $this->assertCount(0, $gameStartEvent['players'] ?? false);
        $this->assertCount(1, $gameStartEvent['events']);
        $this->assertSame(EventList::map[GameStartEvent::class], $gameStartEvent['events'][0]['code'] ?? false);
        $this->assertSame($game->getProperties()->max_rounds, $gameStartEvent['events'][0]['data']['setting']['max_rounds'] ?? false);
        $this->assertSame(2, $gameStartEvent['events'][0]['data']['playersCount'] ?? false);

        $gameOverMsg = $serverResponses[1];
        $gameOverEvent = json_decode($gameOverMsg, true);
        $this->assertIsArray($gameOverEvent);
        $this->assertCount(1, $gameOverEvent['players'] ?? false);
        $this->assertCount(1, $gameOverEvent['events']);
        $this->assertSame(EventList::map[GameOverEvent::class], $gameOverEvent['events'][0]['code'] ?? false);
        $this->assertSame(GameOverReason::REASON_NOT_ALL_PLAYERS_CONNECTED->value, $gameOverEvent['events'][0]['data']['reason'] ?? false);
    }

    public function testServerGameOver(): void
    {
        $tickRate = Util::$TICK_RATE;
        $roundTimeMs = rand($tickRate + 1, 10 * $tickRate);
        $roundTickCount = Util::millisecondsToFrames($roundTimeMs);
        $gameProperty = new GameProperty();
        $gameProperty->max_rounds = 1;
        $gameProperty->freeze_time_sec = 0;
        $gameProperty->half_time_freeze_sec = 0;
        $gameProperty->round_end_cool_down_sec = 0;
        $gameProperty->round_time_ms = $roundTimeMs;
        $this->assertSame(1, $gameProperty->toArray()[GameProperty::MAX_ROUNDS] ?? false);

        $game = new Game($gameProperty);
        $game->loadMap(new TestMap());
        $setting = new ServerSetting(1, 0, 'code');
        $testNet = new TestConnector(array_merge(['login code'], array_fill(0, 3 + $roundTickCount, 'stand')));
        $server = new Server($game, $setting, $testNet);
        $this->assertSame(2 + $roundTickCount, $server->start());
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
            "look 45.2 -20.1|crouch|buy " . BuyMenuItem::GRENADE_MOLOTOV->value,
        ];
        $responses = $this->runTestServer($game, $clientRequests);
        $player = $game->getPlayer(1);
        $this->assertCount(count($clientRequests) + 1, $responses);
        $this->assertSame(0, $player->getPositionClone()->y);
        $this->assertGreaterThan($spawnPos->x, $player->getPositionClone()->x);
        $this->assertGreaterThan($spawnPos->z, $player->getPositionClone()->z);
        $this->assertSame(45.2, $player->getSight()->getRotationHorizontal());
        $this->assertSame(-20.1, $player->getSight()->getRotationVertical());
        $this->assertInstanceOf(Molotov::class, $player->getInventory()->getItems()[InventorySlot::SLOT_GRENADE_MOLOTOV->value]);
        $this->assertLessThan(Setting::playerHeadHeightStand(), $player->getHeadHeight());
    }

    public function testLoginDefender(): void
    {
        $game = GameFactory::createDebug();
        $map = new TestMap();
        $game->loadMap($map);
        $spawnPos = $map->getSpawnPositionDefender()[0];

        $clientRequests = [
            'login dcode',
            'invalid',
        ];
        $responses = $this->runTestServer($game, $clientRequests);
        $player = $game->getPlayer(1);
        $this->assertCount(count($clientRequests) + 1, $responses);
        $this->assertPositionSame($spawnPos, $player->getPositionClone());
    }

}
