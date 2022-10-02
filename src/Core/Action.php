<?php

namespace cs\Core;

final class Action
{

    /**
     * @var array<string,int>
     */
    public const defaultConstant = [
        'moveOneMs'                     => 3,
        'moveWalkOneMs'                 => 2,
        'moveCrouchOneMs'               => 1,
        'fallAmountOneMs'               => 4,
        'crouchDurationMs'              => 300,
        'jumpDurationMs'                => 500,
        'jumpMovementSpeedMultiplier'   => 100,
        'flyingMovementSpeedMultiplier' => 80,

        // NOTE: Better to use even numbers for player const
        'playerHeadRadius'              => 30,
        'playerBoundingRadius'          => 44,
        'playerJumpHeight'              => 150,
        'playerHeadHeightStand'         => 190,
        'playerHeadHeightCrouch'        => 140,
        'playerObstacleOvercomeHeight'  => 20,
        'playerFallDamageThreshold'     => 570,
        'playerBoxHeightCrouchCover'    => 142,
        'playerGunHeightStand'          => 160,
    ];

    /**
     * @var array<string,int>
     */
    public const testConstant = [
        'moveOneMs'                     => 5,
        'moveWalkOneMs'                 => 4,
        'moveCrouchOneMs'               => 3,
        'fallAmountOneMs'               => 4,
        'crouchDurationMs'              => 300,
        'jumpDurationMs'                => 500,
        'jumpMovementSpeedMultiplier'   => 100,
        'flyingMovementSpeedMultiplier' => 80,

        // NOTE: Better to use even numbers for player const
        'playerHeadRadius'              => 30,
        'playerBoundingRadius'          => 44,
        'playerJumpHeight'              => 150,
        'playerHeadHeightStand'         => 190,
        'playerHeadHeightCrouch'        => 140,
        'playerObstacleOvercomeHeight'  => 20,
        'playerFallDamageThreshold'     => 570,
        'playerBoxHeightCrouchCover'    => 142,
        'playerGunHeightStand'          => 160,
    ];

    /**
     * @var array<string,int>
     */
    private static array $data = self::defaultConstant;

    /**
     * @param array<string,int> $constants
     */
    public static function loadConstants(array $constants): void
    {
        self::$data = $constants;
    }

    public static function tickCountCrouch(): int
    {
        return (int)ceil(self::$data['crouchDurationMs'] / Util::$TICK_RATE);
    }

    public static function fallAmountPerTick(): int
    {
        return (int)ceil(self::$data['fallAmountOneMs'] * Util::$TICK_RATE);
    }

    public static function moveDistancePerTick(): int
    {
        return (int)ceil(self::$data['moveOneMs'] * Util::$TICK_RATE);
    }

    public static function jumpDistancePerTick(): int
    {
        return (int)ceil(Action::playerJumpHeight() / self::tickCountJump());
    }

    public static function crouchDistancePerTick(): int
    {
        return (int)ceil((Action::playerHeadHeightStand() - Action::playerHeadHeightCrouch()) / self::tickCountCrouch());
    }

    public static function tickCountJump(): int
    {
        return max(1, (int)round(self::$data['jumpDurationMs'] / Util::$TICK_RATE));
    }

    public static function moveDistanceWalkPerTick(): int
    {
        return (int)ceil(self::$data['moveWalkOneMs'] * Util::$TICK_RATE);
    }

    public static function moveDistanceCrouchPerTick(): int
    {
        return (int)ceil(self::$data['moveCrouchOneMs'] * Util::$TICK_RATE);
    }

    public static function jumpMovementSpeedMultiplier(): float
    {
        return self::$data['jumpMovementSpeedMultiplier'] / 100;
    }

    public static function flyingMovementSpeedMultiplier(): float
    {
        return self::$data['flyingMovementSpeedMultiplier'] / 100;
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
