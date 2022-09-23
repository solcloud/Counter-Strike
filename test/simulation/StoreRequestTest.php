<?php

namespace Test\Simulation;

use cs\Core\Floor;
use cs\Core\GameProperty;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Wall;
use cs\Map\TestMap;
use cs\Net\PlayerControl;
use cs\Net\Protocol\TextProtocol;
use Test\BaseTest;
use Test\TestGame;

class StoreRequestTest extends BaseTest
{

    public function testAllStoreRequests(): void
    {
        $dataDirectory = __DIR__ . '/data/requests';

        foreach (glob($dataDirectory . '/*.bin') as $testData) { // @phpstan-ignore-line
            $data = trim(file_get_contents($testData)); // @phpstan-ignore-line
            $meta = json_decode(file_get_contents(substr($testData, 0, -4) . ".json"), true); // @phpstan-ignore-line

            $testerClass = require substr($testData, 0, -4) . ".php";
            $this->_testRequest(new $testerClass(), $meta, $data); // @phpstan-ignore-line
        }
    }

    /**
     * @param array{protocol: string, players: array<mixed>, properties: array<string,string|int|bool>, walls: array<mixed>, floors: array<mixed>} $meta
     */
    private function _testRequest(SimulationTester $tester, array $meta, string &$data): void
    {
        /// TODO load constant from meta - player speed, radius, .... use Reflection or static factory
        $playerRequests = [];
        $playerControls = [];
        $this->assertSame(TextProtocol::class, $meta['protocol']);
        $protocol = new TextProtocol();
        foreach (explode("\n", $data) as $line) {
            [$tickId, $playerId, $request] = explode('~', $line);
            $playerRequests[(int)$tickId][(int)$playerId] = $protocol->parsePlayerControlCommands($request);
        }

        $game = $this->createGame(count($playerRequests), GameProperty::fromArray($meta['properties']));
        $game->loadMap(new TestMap());
        foreach ($meta['walls'] as $wallData) {
            $game->getWorld()->addWall(Wall::fromArray($wallData)); // @phpstan-ignore-line
        }
        foreach ($meta['floors'] as $floorData) {
            $game->getWorld()->addFloor(Floor::fromArray($floorData)); // @phpstan-ignore-line
        }
        foreach ($meta['players'] as $playerData) {
            $player = Player::fromArray($playerData); // @phpstan-ignore-line
            $game->addPlayer($player);
            $player->setPosition(Point::fromArray($playerData['position'])); // @phpstan-ignore-line
        }

        $game->onEvents(function (array $events) use ($tester): void {
            $tester->onEvents($events);
        });
        $game->onTick(function (GameState $state) use ($tester, &$playerRequests, &$playerControls): void {
            $tester->onTickStart($state, $state->getTickId());
            if (!isset($playerRequests[$state->getTickId()])) {
                return;
            }

            foreach ($playerRequests[$state->getTickId()] as $playerId => $commands) {
                if (!isset($playerControls[$playerId])) {
                    $playerControls[$playerId] = new PlayerControl($state->getPlayer($playerId), $state);
                }

                foreach ($commands as $command) {
                    $method = array_shift($command);
                    $playerControls[$playerId]->{$method}(...$command);
                }
            }
        });
        $game->onAfterTick(function (GameState $state) use ($tester): void {
            $tester->onTickEnd($state, $state->getTickId());
        });

        $tester->onGameStart($game);
        $game->start();
        $tester->onGameEnd($game);
    }

    protected function createGame(int $tickMax, GameProperty $properties): TestGame
    {
        $game = new TestGame($properties);
        $game->setTickMax($tickMax);
        return $game;
    }

}
