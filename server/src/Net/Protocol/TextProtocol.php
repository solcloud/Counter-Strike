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

    public const string separator = '|';

    /** @inheritDoc */
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
     * @param Player[] $players
     * @param Event[] $events
     */
    public function serialize(array $players, array $events): string
    {
        return json_encode([
            "players" => array_map(fn(Player $p): array => $p->serialize(), $players, []),
            "events"  => array_map(fn(Event $e): array => ['code' => $e->getCode(), 'data' => $e->serialize()], $events),
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
                if ((self::methodParamFloat[$method][1] ?? false)) {
                    $command[] = (float)$parts[1];
                } else {
                    $command[] = (int)$parts[1];
                }
            }
            if (isset($parts[2])) {
                if (!is_numeric($parts[2])) {
                    return [];
                }
                if ((self::methodParamFloat[$method][2] ?? false)) {
                    $command[] = (float)$parts[2];
                } else {
                    $command[] = (int)$parts[2]; // @codeCoverageIgnore
                }
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
