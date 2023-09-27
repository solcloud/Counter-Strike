<?php

namespace cs\Core;

final class Setting
{

    /** @var array<string,int|float> */
    public const defaultConstant = [
        'moveOneMs'                     => 0.60,
        'moveWalkOneMs'                 => 0.34,
        'moveCrouchOneMs'               => 0.20,
        'fallAmountOneMs'               => 1,
        'crouchDurationMs'              => 250,
        'jumpDurationMs'                => 420,
        'jumpMovementSpeedMultiplier'   => 1.0,
        'flyingMovementSpeedMultiplier' => 0.8,
        'throwSpeed'                    => 40,

        'playerHeadRadius'             => 10,
        'playerBoundingRadius'         => 60,
        'playerJumpHeight'             => 150,
        'playerHeadHeightStand'        => 190,
        'playerHeadHeightCrouch'       => 140,
        'playerObstacleOvercomeHeight' => 20,
        'playerFallDamageThreshold'    => 570,
    ];

    /** @var array<string,int|float> */
    private static array $data = self::defaultConstant;

    /** @var array<string,int> */
    private static array $cacheInt = [];
    /** @var array<string,float> */
    private static array $cacheFloat = [];

    /**
     * @param array<string,int|float> $constants
     */
    public static function loadConstants(array $constants): void
    {
        self::$cacheInt = [];
        self::$cacheFloat = [];

        self::fixBackwardCompatible($constants);
        self::$data = $constants;
    }

    /**
     * @param array<string,int> $constants
     */
    private static function fixBackwardCompatible(array &$constants): void
    {
        // BC code
        $constants['playerVelocity'] = ($constants['playerVelocity'] ?? 0);
        foreach (self::defaultConstant as $key => $defaultValue) {
            if (isset($constants[$key])) {
                continue;
            }

            $constants[$key] = $defaultValue;
        }
    }

    public static function tickCountCrouch(): int
    {
        if (!isset(self::$cacheInt['tickCountCrouch'])) {
            self::$cacheInt['tickCountCrouch'] = (int)ceil(self::$data['crouchDurationMs'] / Util::$TICK_RATE);
        }
        return self::$cacheInt['tickCountCrouch'];
    }

    public static function fallAmountPerTick(): int
    {
        if (!isset(self::$cacheInt['fallAmountPerTick'])) {
            self::$cacheInt['fallAmountPerTick'] = (int)ceil(self::$data['fallAmountOneMs'] * Util::$TICK_RATE);
        }
        return self::$cacheInt['fallAmountPerTick'];
    }

    public static function jumpDistancePerTick(): int
    {
        if (!isset(self::$cacheInt['jumpDistancePerTick'])) {
            self::$cacheInt['jumpDistancePerTick'] = (int)ceil(self::playerJumpHeight() / self::tickCountJump());
        }
        return self::$cacheInt['jumpDistancePerTick'];
    }

    public static function crouchDistancePerTick(): int
    {
        if (!isset(self::$cacheInt['crouchDistancePerTick'])) {
            self::$cacheInt['crouchDistancePerTick'] = (int)ceil((self::playerHeadHeightStand() - self::playerHeadHeightCrouch()) / self::tickCountCrouch());
        }
        return self::$cacheInt['crouchDistancePerTick'];
    }

    public static function tickCountJump(): int
    {
        if (!isset(self::$cacheInt['tickCountJump'])) {
            self::$cacheInt['tickCountJump'] = (int)ceil(self::$data['jumpDurationMs'] / Util::$TICK_RATE);
        }
        return self::$cacheInt['tickCountJump'];
    }

    public static function moveDistancePerTick(): int
    {
        if (!isset(self::$cacheInt['moveDistancePerTick'])) {
            self::$cacheInt['moveDistancePerTick'] = (int)ceil(self::$data['moveOneMs'] * Util::$TICK_RATE);
        }
        return self::$cacheInt['moveDistancePerTick'];
    }

    public static function moveDistanceWalkPerTick(): int
    {
        if (!isset(self::$cacheInt['moveDistanceWalkPerTick'])) {
            self::$cacheInt['moveDistanceWalkPerTick'] = (int)ceil(self::$data['moveWalkOneMs'] * Util::$TICK_RATE);
        }
        return self::$cacheInt['moveDistanceWalkPerTick'];
    }

    public static function moveDistanceCrouchPerTick(): int
    {
        if (!isset(self::$cacheInt['moveDistanceCrouchPerTick'])) {
            self::$cacheInt['moveDistanceCrouchPerTick'] = (int)ceil(self::$data['moveCrouchOneMs'] * Util::$TICK_RATE);
        }
        return self::$cacheInt['moveDistanceCrouchPerTick'];
    }

    public static function jumpMovementSpeedMultiplier(): float
    {
        if (!isset(self::$cacheFloat['jumpMovementSpeedMultiplier'])) {
            self::$cacheFloat['jumpMovementSpeedMultiplier'] = self::$data['jumpMovementSpeedMultiplier'];
        }
        return self::$cacheFloat['jumpMovementSpeedMultiplier'];
    }

    public static function flyingMovementSpeedMultiplier(): float
    {
        if (!isset(self::$cacheFloat['flyingMovementSpeedMultiplier'])) {
            self::$cacheFloat['flyingMovementSpeedMultiplier'] = self::$data['flyingMovementSpeedMultiplier'];
        }
        return self::$cacheFloat['flyingMovementSpeedMultiplier'];
    }

    public static function getWeaponPrimarySpeedMultiplier(int $itemId): float
    {
        if (!isset(self::$cacheFloat["getWeaponPrimarySpeedMultiplier-{$itemId}"])) {
            self::$cacheFloat["getWeaponPrimarySpeedMultiplier-{$itemId}"] = (self::$data["weaponPrimarySpeedMultiplier-{$itemId}"] ?? 0.6);
        }
        return self::$cacheFloat["getWeaponPrimarySpeedMultiplier-{$itemId}"];
    }

    public static function getWeaponSecondarySpeedMultiplier(int $itemId): float
    {
        if (!isset(self::$cacheFloat["getWeaponSecondarySpeedMultiplier-{$itemId}"])) {
            self::$cacheFloat["getWeaponSecondarySpeedMultiplier-{$itemId}"] = (self::$data["weaponSecondarySpeedMultiplier-{$itemId}"] ?? 0.8);
        }
        return self::$cacheFloat["getWeaponSecondarySpeedMultiplier-{$itemId}"];
    }

    public static function throwSpeed(): int
    {
        return self::$data['throwSpeed']; // @phpstan-ignore-line
    }

    public static function playerHeadRadius(): int
    {
        return self::$data['playerHeadRadius']; // @phpstan-ignore-line
    }

    public static function playerBoundingRadius(): int
    {
        return self::$data['playerBoundingRadius']; // @phpstan-ignore-line
    }

    public static function playerVelocity(): int
    {
        return self::$data['playerVelocity'] ?? ((int)ceil(Util::$TICK_RATE * 1.7)); // @phpstan-ignore-line
    }

    public static function playerJumpHeight(): int
    {
        return self::$data['playerJumpHeight']; // @phpstan-ignore-line
    }

    public static function playerHeadHeightStand(): int
    {
        return self::$data['playerHeadHeightStand']; // @phpstan-ignore-line
    }

    public static function playerHeadHeightCrouch(): int
    {
        return self::$data['playerHeadHeightCrouch']; // @phpstan-ignore-line
    }

    public static function playerObstacleOvercomeHeight(): int
    {
        return self::$data['playerObstacleOvercomeHeight']; // @phpstan-ignore-line
    }

    public static function playerFallDamageThreshold(): int
    {
        return self::$data['playerFallDamageThreshold']; // @phpstan-ignore-line
    }

    /**
     * @return array<string,int|float>
     * @codeCoverageIgnore
     */
    public static function getDataArray(): array
    {
        return self::$data;
    }

}
