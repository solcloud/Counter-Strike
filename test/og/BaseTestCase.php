<?php

declare(strict_types=1);

namespace Test;

use Closure;
use cs\Core\GameProperty;
use cs\Core\GameState;
use cs\Core\Player;
use cs\Core\Setting;
use cs\Core\Util;
use cs\Enum\Color;
use cs\Map\TestMap;
use InvalidArgumentException;
use ReflectionProperty;

abstract class BaseTestCase extends BaseTest
{
    private int $testTickRateMs = 10;
    /** @var array<string,int|float> */
    private array $defaultTestAction = [
        'moveOneMs'                    => 5,
        'moveWalkOneMs'                => 4,
        'moveCrouchOneMs'              => 3,
        'fallAmountOneMs'              => 6,
        'crouchDurationMs'             => 40,
        'jumpDurationMs'               => 50,
        // NOTE: Better to use even numbers for player const
        'playerHeadRadius'             => 30,
        'playerBoundingRadius'         => 44,
        'playerJumpHeight'             => 150,
        'playerHeadHeightStand'        => 190,
        'playerHeadHeightCrouch'       => 140,
        'playerObstacleOvercomeHeight' => 20,
        'playerFallDamageThreshold'    => 570,
    ];

    protected function setUp(): void
    {
        Util::$TICK_RATE = $this->testTickRateMs;
        Setting::loadConstants($this->defaultTestAction);
    }

    /**
     * @param array<Closure|int|false> $commands
     * @param array<string,int|string> $gameProperties
     */
    protected function simulateGame(array $commands, array $gameProperties = []): TestGame
    {
        $tickMax = count($commands);
        foreach ($commands as $command) {
            if (!is_int($command)) {
                continue;
            }
            $tickMax += $command;
        }
        $game = $this->createTestGame($tickMax, GameProperty::fromArray($gameProperties));
        $this->playPlayer($game, $commands);
        return $game;
    }

    /**
     * @param array<string,int|string> $gameProperties
     * @deprecated use createTestGame() instead, this only set playerBoundingRadius() to 0
     */
    protected function createOneRoundGame(int $tickMax = 1, array $gameProperties = []): TestGame
    {
        $properties = GameProperty::fromArray([
                ...[
                    GameProperty::MAX_ROUNDS               => 1,
                    GameProperty::START_MONEY              => 0,
                    GameProperty::FREEZE_TIME_SEC          => 0,
                    GameProperty::ROUND_END_COOL_DOWN_SEC  => 0,
                    GameProperty::RANDOMIZE_SPAWN_POSITION => false,
                ],
                ...$gameProperties,
            ]
        );

        $game = new TestGame($properties);
        $game->loadMap(new TestMap());
        $game->setTickMax($tickMax);

        $testPlayer = new Player(1, Color::BLUE, true);
        $boundingRadius = new ReflectionProperty($testPlayer, 'playerBoundingRadius');
        $boundingRadius->setAccessible(true);
        $boundingRadius->setValue($testPlayer, 0);
        $testPlayer->equipKnife();
        $game->addPlayer($testPlayer);

        return $game;
    }

    protected function createTestGame(?int $tickMax = null, GameProperty $gameProperty = new GameProperty()): TestGame
    {
        $gameProperty->randomize_spawn_position = false;
        $game = new TestGame($gameProperty);
        $game->setTickMax($tickMax ?? PHP_INT_MAX);
        $game->loadMap(new TestMap());

        $testPlayer = new Player(1, Color::GREEN, true);
        $testPlayer->equipKnife();
        $game->addPlayer($testPlayer);

        return $game;
    }

    protected function createNoPauseGame(int $maxRounds = 1): TestGame
    {
        return $this->createTestGame(null, $this->createNoPauseGameProperty($maxRounds));
    }

    protected function createNoPauseGameProperty(int $maxRounds = 1): GameProperty
    {
        $properties = new GameProperty();
        $properties->max_rounds = $maxRounds;
        $properties->freeze_time_sec = 0;
        $properties->half_time_freeze_sec = 0;
        $properties->round_end_cool_down_sec = 0;
        $properties->randomize_spawn_position = false;

        return $properties;
    }

    /**
     * @param array<string,int|string> $gameProperties
     * @deprecated rather use createTestGame() maybe
     */
    protected function createGame(array $gameProperties = []): TestGame
    {
        return $this->createOneRoundGame(PHP_INT_MAX, $gameProperties);
    }

    /**
     * @param array<Closure|int|false> $commands
     */
    protected function playPlayerDebug(TestGame $game, array $commands, int $playerId = 1): void
    {
        $this->playPlayer($game, $commands, $playerId, true);
    }

    /**
     * @param array<Closure|int|false> $commands
     */
    protected function playPlayer(TestGame $game, array $commands, int $playerId = 1, bool $debug = false): void
    {
        $i = 0;
        $skipNTicks = 0;
        $game->onTick(function (GameState $state) use (&$i, &$skipNTicks, $game, $playerId, $commands): void {
            if ($skipNTicks-- > 0) {
                return;
            }
            if (!isset($commands[$i])) {
                throw new InvalidArgumentException("No command defined for tick '{$i}' or too many tickMax in Game");
            }
            if (false === $commands[$i]) {
                $game->setTickMax(0);
                $i++;
                return;
            }
            if (is_int($commands[$i])) {
                if ($commands[$i] <= 0) {
                    $this->fail("Skip ticks cannot be zero or negative");
                }
                $skipNTicks = $commands[$i];
                $i++;
                return;
            }

            $commands[$i]($state->getPlayer($playerId));
            $i++;
        });
        if ($debug) {
            $game->startDebug();
        } else {
            $game->start();
        }
        if (isset($commands[$i])) {
            throw new InvalidArgumentException("Some command(s) were not processed, starting from tick '{$i}'");
        }
    }

    /**
     * @return false
     */
    protected function endGame(): bool
    {
        return false;
    }

    protected function waitXTicks(int $numberOfTicks): int
    {
        return $numberOfTicks;
    }

    protected function waitNTicks(int $waitTimeMs): int
    {
        $waitTick = Util::millisecondsToFrames($waitTimeMs) - 1;
        if ($waitTick < 1) {
            throw new InvalidArgumentException("Value too low");
        }

        return $waitTick;
    }

}
