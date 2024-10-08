<?php

namespace Test;

use Closure;
use cs\Core\Game;
use cs\Core\GameException;
use cs\Core\GameState;
use cs\Core\Setting;
use cs\Event\Event;
use cs\Map\TestMap;
use cs\Net\Protocol\TextProtocol;

/**
 * @internal test only
 */
class TestGame extends Game
{
    private int $tickMax = 1;
    /** @var ?Closure(GameState):void */
    private ?Closure $onTickCallback = null;
    /** @var ?Closure(GameState):void */
    private ?Closure $afterTickCallback = null;
    /** @var ?Closure(non-empty-list<Event>):void */
    private ?Closure $onEventsCallback = null;
    /** @var array<int,mixed> */
    private array $gameStates = [];

    public function setTickMax(int $tickMax): void
    {
        $this->tickMax = $tickMax;
    }

    public function start(bool $debug = false): void
    {
        if ($debug) {
            if (getenv('PROJECT_CHECK') === 'true') {
                throw new GameException('Debug flag detected, see oldest item of stacktrace');
            }
            $protocol = new TextProtocol();
            $this->gameStates[0] = json_decode($protocol->serializeGameState($this));
        }
        for ($tickId = 0; $tickId < $this->tickMax; $tickId++) {
            $this->tick = $tickId;
            if ($this->onTickCallback) {
                call_user_func($this->onTickCallback, $this->getState());
            }
            $gameOverEventOrNull = $this->tick($tickId);
            $events = $this->consumeTickEvents();
            if ($this->onEventsCallback && $events !== []) {
                call_user_func($this->onEventsCallback, $events);
            }
            if ($this->afterTickCallback) {
                call_user_func($this->afterTickCallback, $this->getState());
            }
            if ($debug) {
                $this->gameStates[$tickId + 1] = json_decode($protocol->serialize($this->getPlayers(), $events));
            }
            if ($gameOverEventOrNull) {
                break;
            }
        }
    }

    /**
     * @param Closure(GameState):void $callback function(GameState $state):void {}
     */
    public function onTick(Closure $callback): void
    {
        $this->onTickCallback = $callback;
    }

    /**
     * @param Closure(GameState):void $callback function(GameState $state):void {}
     */
    public function onAfterTick(Closure $callback): void
    {
        $this->afterTickCallback = $callback;
    }

    /**
     * @param Closure(non-empty-list<Event>):void $callback function(array $events):void {foreach($events as $event){}}
     */
    public function onEvents(Closure $callback): void
    {
        $this->onEventsCallback = $callback;
    }

    public function startDebug(string $path = '/tmp/cs.demo.json'): void
    {
        $this->start(true);
        file_put_contents(
            $path,
            json_encode([
                'player' => [
                    'head'   => Setting::playerHeadRadius(),
                    'body'   => Setting::playerBoundingRadius(),
                    'height' => Setting::playerHeadHeightStand(),
                ],
                'states' => $this->gameStates,
                'floors' => $this->getWorld()->getFloors(),
                'walls'  => $this->getWorld()->getWalls(),
            ])
        );
        $this->gameStates = [];
    }

    public function getTestMap(): TestMap
    {
        $map = $this->getWorld()->getMap();
        if ($map instanceof TestMap) {
            return $map;
        }

        throw new GameException("No test map is loaded");
    }

}
