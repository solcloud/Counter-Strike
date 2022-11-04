<?php

namespace cs\Core;

final class Util
{
    public static int $TICK_RATE = 20;
    /** @var float[] (foreach (range(1, 89) as $angle) {printf("%d => %s,\n", $angle, sin(deg2rad($angle)));}) */
    private static array $sines = [
        0   => 0.0,
        1   => 0.017452406437284,
        2   => 0.034899496702501,
        3   => 0.052335956242944,
        4   => 0.069756473744125,
        5   => 0.087155742747658,
        6   => 0.10452846326765,
        7   => 0.12186934340515,
        8   => 0.13917310096007,
        9   => 0.15643446504023,
        10  => 0.17364817766693,
        11  => 0.19080899537654,
        12  => 0.20791169081776,
        13  => 0.22495105434386,
        14  => 0.24192189559967,
        15  => 0.25881904510252,
        16  => 0.275637355817,
        17  => 0.29237170472274,
        18  => 0.30901699437495,
        19  => 0.32556815445716,
        20  => 0.34202014332567,
        21  => 0.3583679495453,
        22  => 0.37460659341591,
        23  => 0.39073112848927,
        24  => 0.4067366430758,
        25  => 0.4226182617407,
        26  => 0.43837114678908,
        27  => 0.45399049973955,
        28  => 0.46947156278589,
        29  => 0.48480962024634,
        30  => 0.5,
        31  => 0.51503807491005,
        32  => 0.5299192642332,
        33  => 0.54463903501503,
        34  => 0.55919290347075,
        35  => 0.57357643635105,
        36  => 0.58778525229247,
        37  => 0.60181502315205,
        38  => 0.61566147532566,
        39  => 0.62932039104984,
        40  => 0.64278760968654,
        41  => 0.65605902899051,
        42  => 0.66913060635886,
        43  => 0.6819983600625,
        44  => 0.694658370459,
        45  => 0.70710678118655,
        46  => 0.71933980033865,
        47  => 0.73135370161917,
        48  => 0.74314482547739,
        49  => 0.75470958022277,
        50  => 0.76604444311898,
        51  => 0.77714596145697,
        52  => 0.78801075360672,
        53  => 0.79863551004729,
        54  => 0.80901699437495,
        55  => 0.81915204428899,
        56  => 0.82903757255504,
        57  => 0.83867056794542,
        58  => 0.84804809615643,
        59  => 0.85716730070211,
        60  => 0.86602540378444,
        61  => 0.8746197071394,
        62  => 0.88294759285893,
        63  => 0.89100652418837,
        64  => 0.89879404629917,
        65  => 0.90630778703665,
        66  => 0.9135454576426,
        67  => 0.92050485345244,
        68  => 0.92718385456679,
        69  => 0.9335804264972,
        70  => 0.93969262078591,
        71  => 0.94551857559932,
        72  => 0.95105651629515,
        73  => 0.95630475596304,
        74  => 0.96126169593832,
        75  => 0.96592582628907,
        76  => 0.970295726276,
        77  => 0.97437006478524,
        78  => 0.97814760073381,
        79  => 0.98162718344766,
        80  => 0.98480775301221,
        81  => 0.98768834059514,
        82  => 0.99026806874157,
        83  => 0.99254615164132,
        84  => 0.99452189536827,
        85  => 0.99619469809175,
        86  => 0.99756405025982,
        87  => 0.99862953475457,
        88  => 0.9993908270191,
        89  => 0.99984769515639,
        90  => 1.0,
        91  => 0.99984769515639,
        92  => 0.9993908270191,
        93  => 0.99862953475457,
        94  => 0.99756405025982,
        95  => 0.99619469809175,
        96  => 0.99452189536827,
        97  => 0.99254615164132,
        98  => 0.99026806874157,
        99  => 0.98768834059514,
        100 => 0.98480775301221,
        101 => 0.98162718344766,
        102 => 0.97814760073381,
        103 => 0.97437006478524,
        104 => 0.970295726276,
        105 => 0.96592582628907,
        106 => 0.96126169593832,
        107 => 0.95630475596304,
        108 => 0.95105651629515,
        109 => 0.94551857559932,
        110 => 0.93969262078591,
        111 => 0.9335804264972,
        112 => 0.92718385456679,
        113 => 0.92050485345244,
        114 => 0.9135454576426,
        115 => 0.90630778703665,
        116 => 0.89879404629917,
        117 => 0.89100652418837,
        118 => 0.88294759285893,
        119 => 0.8746197071394,
        120 => 0.86602540378444,
        121 => 0.85716730070211,
        122 => 0.84804809615643,
        123 => 0.83867056794542,
        124 => 0.82903757255504,
        125 => 0.81915204428899,
        126 => 0.80901699437495,
        127 => 0.79863551004729,
        128 => 0.78801075360672,
        129 => 0.77714596145697,
        130 => 0.76604444311898,
        131 => 0.75470958022277,
        132 => 0.74314482547739,
        133 => 0.73135370161917,
        134 => 0.71933980033865,
        135 => 0.70710678118655,
        136 => 0.694658370459,
        137 => 0.6819983600625,
        138 => 0.66913060635886,
        139 => 0.65605902899051,
        140 => 0.64278760968654,
        141 => 0.62932039104984,
        142 => 0.61566147532566,
        143 => 0.60181502315205,
        144 => 0.58778525229247,
        145 => 0.57357643635105,
        146 => 0.55919290347075,
        147 => 0.54463903501503,
        148 => 0.5299192642332,
        149 => 0.51503807491005,
        150 => 0.5,
        151 => 0.48480962024634,
        152 => 0.46947156278589,
        153 => 0.45399049973955,
        154 => 0.43837114678908,
        155 => 0.4226182617407,
        156 => 0.4067366430758,
        157 => 0.39073112848927,
        158 => 0.37460659341591,
        159 => 0.3583679495453,
        160 => 0.34202014332567,
        161 => 0.32556815445716,
        162 => 0.30901699437495,
        163 => 0.29237170472274,
        164 => 0.275637355817,
        165 => 0.25881904510252,
        166 => 0.24192189559967,
        167 => 0.22495105434387,
        168 => 0.20791169081776,
        169 => 0.19080899537654,
        170 => 0.17364817766693,
        171 => 0.15643446504023,
        172 => 0.13917310096007,
        173 => 0.12186934340515,
        174 => 0.10452846326765,
        175 => 0.087155742747658,
        176 => 0.069756473744126,
        177 => 0.052335956242944,
        178 => 0.034899496702501,
        179 => 0.017452406437283,
        180 => 0.0,
    ];  // NOTE: client needs same values if they implement own movement

    public static function millisecondsToFrames(int $timeMs): int
    {
        if ($timeMs < 0) {
            throw new GameException("Negative time given");
        }
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

    private static function sin(int $angleDegree): float
    {
        if ($angleDegree < 0) {
            $angleDegree += 360;
        }

        if ($angleDegree < 90) {
            return self::$sines[$angleDegree];
        }
        if ($angleDegree <= 180) {
            return self::$sines[$angleDegree];
        }
        return -1 * self::$sines[$angleDegree - 180];
    }

    private static function cos(int $angleDegree): float
    {
        $base = abs(self::sin($angleDegree - 90));
        if ($angleDegree < 90) {
            return $base;
        }
        if ($angleDegree < 270) {
            return -1 * $base;
        }
        return $base;
    }

    private static function sine(int $angleDegree): float
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
            $x = $distance * self::sine($angle);
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

        if ($angleVertical === 0) {
            [$x, $z] = self::horizontalMovementXZ($angleHorizontal, $distance);
            return [$x, 0, $z];
        } elseif ($angleVertical === 90) {
            return [0, $distance, 0];
        } elseif ($angleVertical === -90) {
            return [0, -$distance, 0];
        } else {
            [$x, $z] = self::horizontalMovementXZ($angleHorizontal, $distance);
            $y = (int)round($distance * Util::sine($angleVertical));
            return [$x, $y, $z];
        }
    }

    public static function distanceFromOrigin(Point2D $point): int
    {
        return (int)round(hypot($point->x, $point->y));
    }

    public static function distanceSquared(Point $a, Point $b): int
    {
        return pow($a->x - $b->x, 2) + pow($a->y - $b->y, 2) + pow($a->z - $b->z, 2);
    }

    /**
     * @return int[] new [$x, $z]
     */
    public static function rotatePointY(int $angle, int $x, int $z, int $centerX = 0, int $centerZ = 0, bool $clockWise = true): array
    {
        $newX = $centerX + (int)round(self::cos($angle) * ($x - $centerX) + self::sin($angle) * ($z - $centerZ));
        $newZ = $centerZ + (int)round(($clockWise ? -1 : 1) * self::sin($angle) * ($x - $centerX) + self::cos($angle) * ($z - $centerZ));

        return [$newX, $newZ];
    }

}
