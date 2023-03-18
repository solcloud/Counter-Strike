<?php

namespace Test;

use Closure;
use cs\Core\Game;
use cs\Core\Setting;
use cs\Net\Protocol\TextProtocol;

/**
 * @internal test only
 */
class TestGame extends Game
{
    private int $tickMax = 1;
    private ?Closure $onTickCallback = null;
    private ?Closure $afterTickCallback = null;
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
     * @param Closure $callback function(GameState $state):void {}
     */
    public function onTick(Closure $callback): void
    {
        $this->onTickCallback = $callback;
    }

    /**
     * @param Closure $callback function(GameState $state):void {}
     */
    public function onAfterTick(Closure $callback): void
    {
        $this->afterTickCallback = $callback;
    }

    /**
     * @param Closure $callback function(array $events):void {}
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

}
