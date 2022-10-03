<?php

namespace cs\Core;

final class Action
{

    /** @var array<string,int> */
    public const defaultConstant = [
        'moveOneMs'                     => 3,
        'moveWalkOneMs'                 => 2,
        'moveCrouchOneMs'               => 1,
        'fallAmountOneMs'               => 4,
        'crouchDurationMs'              => 300,
        'jumpDurationMs'                => 500,
        'jumpMovementSpeedMultiplier'   => 100,
        'flyingMovementSpeedMultiplier' => 80,

        'playerHeadRadius'             => 30,
        'playerBoundingRadius'         => 44,
        'playerJumpHeight'             => 150,
        'playerHeadHeightStand'        => 190,
        'playerHeadHeightCrouch'       => 140,
        'playerObstacleOvercomeHeight' => 20,
        'playerFallDamageThreshold'    => 570,
        'playerBoxHeightCrouchCover'   => 142,
        'playerGunHeightStand'         => 160,
    ];

    /** @var array<string,int> */
    private static array $data = self::defaultConstant;

    /** @var array<string,int> */
    private static array $cacheInt = [];
    /** @var array<string,float> */
    private static array $cacheFloat = [];

    /**
     * @param array<string,int> $constants
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

    public static function moveDistancePerTick(): int
    {
        if (!isset(self::$cacheInt['moveDistancePerTick'])) {
            self::$cacheInt['moveDistancePerTick'] = (int)ceil(self::$data['moveOneMs'] * Util::$TICK_RATE);
        }
        return self::$cacheInt['moveDistancePerTick'];
    }

    public static function jumpDistancePerTick(): int
    {
        if (!isset(self::$cacheInt['jumpDistancePerTick'])) {
            self::$cacheInt['jumpDistancePerTick'] = (int)ceil(Action::playerJumpHeight() / self::tickCountJump());
        }
        return self::$cacheInt['jumpDistancePerTick'];
    }

    public static function crouchDistancePerTick(): int
    {
        if (!isset(self::$cacheInt['crouchDistancePerTick'])) {
            self::$cacheInt['crouchDistancePerTick'] = (int)ceil((Action::playerHeadHeightStand() - Action::playerHeadHeightCrouch()) / self::tickCountCrouch());
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
            self::$cacheFloat['jumpMovementSpeedMultiplier'] = self::$data['jumpMovementSpeedMultiplier'] / 100;
        }
        return self::$cacheFloat['jumpMovementSpeedMultiplier'];
    }

    public static function flyingMovementSpeedMultiplier(): float
    {
        if (!isset(self::$cacheFloat['flyingMovementSpeedMultiplier'])) {
            self::$cacheFloat['flyingMovementSpeedMultiplier'] = self::$data['flyingMovementSpeedMultiplier'] / 100;
        }
        return self::$cacheFloat['flyingMovementSpeedMultiplier'];
    }

    public static function getWeaponPrimarySpeedMultiplier(string $itemId): float
    {
        if (!isset(self::$cacheFloat["getWeaponPrimarySpeedMultiplier-{$itemId}"])) {
            self::$cacheFloat["getWeaponPrimarySpeedMultiplier-{$itemId}"] = (self::$data["weaponPrimarySpeedMultiplier-{$itemId}"] ?? 60) / 100;
        }
        return self::$cacheFloat["getWeaponPrimarySpeedMultiplier-{$itemId}"];
    }

    public static function getWeaponSecondarySpeedMultiplier(string $itemId): float
    {
        if (!isset(self::$cacheFloat["getWeaponSecondarySpeedMultiplier-{$itemId}"])) {
            self::$cacheFloat["getWeaponSecondarySpeedMultiplier-{$itemId}"] = (self::$data["weaponSecondarySpeedMultiplier-{$itemId}"] ?? 80) / 100;
        }
        return self::$cacheFloat["getWeaponSecondarySpeedMultiplier-{$itemId}"];
    }

    public static function playerHeadRadius(): int
    {
        return self::$data['playerHeadRadius'];
    }

    public static function playerBoundingRadius(): int
    {
        return self::$data['playerBoundingRadius'];
    }

    public static function playerJumpHeight(): int
    {
        return self::$data['playerJumpHeight'];
    }

    public static function playerHeadHeightStand(): int
    {
        return self::$data['playerHeadHeightStand'];
    }

    public static function playerHeadHeightCrouch(): int
    {
        return self::$data['playerHeadHeightCrouch'];
    }

    public static function playerObstacleOvercomeHeight(): int
    {
        return self::$data['playerObstacleOvercomeHeight'];
    }

    public static function playerFallDamageThreshold(): int
    {
        return self::$data['playerFallDamageThreshold'];
    }

    public static function playerBoxHeightCrouchCover(): int
    {
        return self::$data['playerBoxHeightCrouchCover'];
    }

    public static function playerGunHeightStand(): int
    {
        return self::$data['playerGunHeightStand'];
    }

    /**
     * @return int[]
     */
    public static function getDataArray(): array
    {
        return self::$data;
    }

}
