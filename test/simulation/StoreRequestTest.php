<?php

namespace Test\Simulation;

use cs\Core\Floor;
use cs\Core\Game;
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

            $game = $this->_testRequest($meta, $data); // @phpstan-ignore-line
            $callback = require substr($testData, 0, -4) . ".php";
            call_user_func($callback, $this, $game);
        }
    }

    /**
     * @param array{protocol: string, players: array<mixed>, properties: string, walls: array<mixed>, floors: array<mixed>} $meta
     */
    private function _testRequest(array $meta, string &$data): Game
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

        $properties = unserialize($meta['properties']);
        $this->assertInstanceOf(GameProperty::class, $properties);
        $game = $this->createGame(count($playerRequests), $properties);
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

        $game->onTick(function (GameState $state) use (&$playerRequests, &$playerControls): void {
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

        $game->start();
        return $game;
    }

    protected function createGame(int $tickMax, GameProperty $properties): TestGame
    {
        $game = new TestGame($properties);
        $game->setTickMax($tickMax);
        return $game;
    }

}
