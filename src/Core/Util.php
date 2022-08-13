<?php

namespace cs\Core;

class Util
{
    public static int $TICK_RATE = 20;
    /** @var float[] */
    private static array $sines = [
        0   => 0.0000,
        1   => 0.0175,
        2   => 0.0349,
        3   => 0.0523,
        4   => 0.0698,
        5   => 0.0872,
        6   => 0.1045,
        7   => 0.1219,
        8   => 0.1392,
        9   => 0.1564,
        10  => 0.1736,
        11  => 0.1908,
        12  => 0.2079,
        13  => 0.2250,
        14  => 0.2419,
        15  => 0.2588,
        16  => 0.2756,
        17  => 0.2924,
        18  => 0.3090,
        19  => 0.3256,
        20  => 0.3420,
        21  => 0.3584,
        22  => 0.3746,
        23  => 0.3907,
        24  => 0.4067,
        25  => 0.4226,
        26  => 0.4384,
        27  => 0.4540,
        28  => 0.4695,
        29  => 0.4848,
        30  => 0.5000,
        31  => 0.5150,
        32  => 0.5299,
        33  => 0.5446,
        34  => 0.5592,
        35  => 0.5736,
        36  => 0.5878,
        37  => 0.6018,
        38  => 0.6157,
        39  => 0.6293,
        40  => 0.6428,
        41  => 0.6561,
        42  => 0.6691,
        43  => 0.6820,
        44  => 0.6947,
        45  => 0.7071,
        46  => 0.7193,
        47  => 0.7314,
        48  => 0.7431,
        49  => 0.7547,
        50  => 0.7660,
        51  => 0.7771,
        52  => 0.7880,
        53  => 0.7986,
        54  => 0.8090,
        55  => 0.8192,
        56  => 0.8290,
        57  => 0.8387,
        58  => 0.8480,
        59  => 0.8572,
        60  => 0.8660,
        61  => 0.8746,
        62  => 0.8829,
        63  => 0.8910,
        64  => 0.8988,
        65  => 0.9063,
        66  => 0.9135,
        67  => 0.9205,
        68  => 0.9272,
        69  => 0.9336,
        70  => 0.9397,
        71  => 0.9455,
        72  => 0.9511,
        73  => 0.9563,
        74  => 0.9613,
        75  => 0.9659,
        76  => 0.9703,
        77  => 0.9744,
        78  => 0.9781,
        79  => 0.9816,
        80  => 0.9848,
        81  => 0.9877,
        82  => 0.9903,
        83  => 0.9925,
        84  => 0.9945,
        85  => 0.9962,
        86  => 0.9976,
        87  => 0.9986,
        88  => 0.9994,
        89  => 0.9998,
        90  => 1.0000,
        91  => 0.9998,
        92  => 0.9994,
        93  => 0.9986,
        94  => 0.9976,
        95  => 0.9962,
        96  => 0.9945,
        97  => 0.9925,
        98  => 0.9903,
        99  => 0.9877,
        100 => 0.9848,
        101 => 0.9816,
        102 => 0.9781,
        103 => 0.9744,
        104 => 0.9703,
        105 => 0.9659,
        106 => 0.9613,
        107 => 0.9563,
        108 => 0.9511,
        109 => 0.9455,
        110 => 0.9397,
        111 => 0.9336,
        112 => 0.9272,
        113 => 0.9205,
        114 => 0.9135,
        115 => 0.9063,
        116 => 0.8988,
        117 => 0.8910,
        118 => 0.8829,
        119 => 0.8746,
        120 => 0.8660,
        121 => 0.8572,
        122 => 0.8480,
        123 => 0.8387,
        124 => 0.8290,
        125 => 0.8192,
        126 => 0.8090,
        127 => 0.7986,
        128 => 0.7880,
        129 => 0.7771,
        130 => 0.7660,
        131 => 0.7547,
        132 => 0.7431,
        133 => 0.7314,
        134 => 0.7193,
        135 => 0.7071,
        136 => 0.6947,
        137 => 0.6820,
        138 => 0.6691,
        139 => 0.6561,
        140 => 0.6428,
        141 => 0.6293,
        142 => 0.6157,
        143 => 0.6018,
        144 => 0.5878,
        145 => 0.5736,
        146 => 0.5592,
        147 => 0.5446,
        148 => 0.5299,
        149 => 0.5150,
        150 => 0.5000,
        151 => 0.4848,
        152 => 0.4695,
        153 => 0.4540,
        154 => 0.4384,
        155 => 0.4226,
        156 => 0.4067,
        157 => 0.3907,
        158 => 0.3746,
        159 => 0.3584,
        160 => 0.3420,
        161 => 0.3256,
        162 => 0.3090,
        163 => 0.2924,
        164 => 0.2756,
        165 => 0.2588,
        166 => 0.2419,
        167 => 0.2250,
        168 => 0.2079,
        169 => 0.1908,
        170 => 0.1736,
        171 => 0.1564,
        172 => 0.1392,
        173 => 0.1219,
        174 => 0.1045,
        175 => 0.0872,
        176 => 0.0698,
        177 => 0.0523,
        178 => 0.0349,
        179 => 0.0175,
        180 => 0.0000,
    ];  // NOTE: client needs same values

    public static function millisecondsToFrames(int $timeMs): int
    {
        return (int)ceil($timeMs / self::$TICK_RATE);
    }

    /**
     * @return int 0..359
     */
    public static function normalizeAngle(int $angleDegree): int
    {
        $angleDegree = $angleDegree % 360;
        if ($angleDegree < 0) {
            $angleDegree = 360 + $angleDegree;
        }
        return $angleDegree;
    }

    public static function sine(int $angleDegree): float
    {
        $angleDegree = $angleDegree % 360;
        $multiplier = +1;
        if ($angleDegree < 0) {
            $multiplier = -1;
            $angleDegree = abs($angleDegree);
        }

        if ($angleDegree <= 180) {
            return $multiplier * self::$sines[$angleDegree];
        }

        return $multiplier * self::$sines[$angleDegree - 180];
    }

    /**
     * @return int[] [x, z]
     */
    public static function horizontalMovementXZ(int $angle, int $distance): array
    {
        $angle = self::normalizeAngle($angle);

        if ($angle === 0) {
            return [0, $distance];
        } elseif ($angle === 90) {
            return [$distance, 0];
        } elseif ($angle === 180) {
            return [0, -$distance];
        } elseif ($angle === 270) {
            return [-$distance, 0];
        } else {
            $x = $distance * static::sine($angle);
            $z = (int)round(sqrt($distance * $distance - $x * $x));
            $x = (int)round($x);

            if ($angle < 90) {
                return [$x, $z];
            } elseif ($angle < 180) {
                return [$x, -$z];
            } elseif ($angle < 270) {
                return [-$x, -$z];
            } else {
                return [-$x, $z];
            }
        }
    }

    /**
     * @return int[] [x, y, z]
     */
    public static function movementXYZ(int $angleHorizontal, int $angleVertical, int $distance): array
    {
        if ($angleVertical < -90 || $angleVertical > 90) {
            throw new GameException("Invalid angleVertical '{$angleVertical}' given");
        }

        [$x, $z] = self::horizontalMovementXZ($angleHorizontal, $distance);
        if ($angleVertical === 0) {
            return [$x, 0, $z];
        } elseif ($angleVertical === 90) {
            return [$x, $distance, $z];
        } elseif ($angleVertical === -90) {
            return [$x, -$distance, $z];
        } else {
            $y = (int)round($distance * Util::sine($angleVertical));
            return [$x, $y, $z];
        }
    }

}
