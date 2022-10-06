<?php

namespace cs\Net\Protocol;

use cs\Core\Game;
use cs\Core\Player;
use cs\Event\Event;
use cs\Event\GameStartEvent;
use cs\Net\Protocol;
use cs\Net\ServerSetting;

class TextProtocol extends Protocol
{

    public const separator = '|';

    public function getRequestMaxSizeBytes(): int
    {
        return 960;
    }

    public function serializeGameSetting(Player $player, ServerSetting $setting, Game $game): string
    {
        $gameStartEvent = new GameStartEvent($player, $setting, $game->getProperties());
        return $this->serialize([], [$gameStartEvent]);
    }

    /**
     * @param Player[] $playersArray
     * @param Event[] $eventsArray
     */
    private function serialize(array $playersArray, array $eventsArray): string
    {
        $players = [];
        foreach ($playersArray as $player) {
            $players[] = $player->serialize();
        }

        $events = [];
        foreach ($eventsArray as $event) {
            $events[] = [
                'code' => $event->getCode(),
                'data' => $event->serialize(),
            ];
        }

        return json_encode([
            "players" => $players,
            "events"  => $events,
        ], JSON_THROW_ON_ERROR);
    }

    public function serializeGameState(Game $game): string
    {
        return $this->serialize($game->getPlayers(), $game->consumeTickEvents());
    }

    public function parsePlayerControlCommands(string $msg): array
    {
        $commands = [];
        $poll = self::playerControlMethods;

        foreach (explode(self::separator, $msg) as $line) {
            $parts = explode(' ', $line);
            $method = $parts[0];
            if (!isset($poll[$method])) {
                return [];
            }

            $command = [$method];
            if (isset($parts[1])) {
                if (!is_numeric($parts[1])) {
                    return [];
                }
                $command[] = (int)$parts[1];
            }
            if (isset($parts[2])) {
                if (!is_numeric($parts[2])) {
                    return [];
                }
                $command[] = (int)$parts[2];
            }

            if (count($command) !== self::playerControlMethodParamCount[$method] + 1) {
                return [];
            }

            $commands[] = $command;
            $poll[$method]--;
            if ($poll[$method] === 0) {
                unset($poll[$method]);
            }
        }

        return $commands;
    }

}
