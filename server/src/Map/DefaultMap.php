<?php

namespace cs\Map;

use cs\Core\Box;
use cs\Core\BoxGroup;
use cs\Core\Floor;
use cs\Core\Plane;
use cs\Core\PlaneBuilder;
use cs\Core\Point;
use cs\Core\Wall;

final class DefaultMap extends Map
{
    /** @var list<Wall> */
    private array $walls = [];
    /** @var list<Floor> */
    private array $floors = [];
    private BoxGroup $plantArea;
    private BoxGroup $buyAreaAttackers;
    private BoxGroup $buyAreaDefenders;

    public function __construct()
    {
        $this->plantArea = new BoxGroup();
        $this->buyAreaAttackers = new BoxGroup();
        $this->buyAreaDefenders = new BoxGroup();
        $builder = new PlaneBuilder();
        $add = function (Point $a, Point $b, Point $c, ?Point $d, ?float $jaggedness = null, bool $penetrable = true, bool $navmesh = true) use ($builder): void {
            $this->addPlanes($builder->create($a, $b, $c, $d, $jaggedness), $penetrable, $navmesh);
        };

/** START DATA */

        // Map - A Long
        if (true) {

            // a long to a side
            $add(
                new Point(11512, 815, 11049),
                new Point(12539, 815, 11049),
                new Point(12539, 1070, 11978),
                new Point(11512, 1070, 11978),
            );
            $add(
                new Point(11512, 1187, 12535),
                new Point(12539, 1187, 12535),
                new Point(12539, 1187, 13386),
                new Point(11512, 1187, 13386),
            );
            $add(
                new Point(11333, 815, 7887),
                new Point(12609, 815, 7887),
                new Point(12609, 815, 11158),
                new Point(11333, 815, 11158),
            );
            $add(
                new Point(11512, 1070, 11978),
                new Point(12539, 1070, 11978),
                new Point(12539, 1187, 12535),
                new Point(11512, 1187, 12535),
            );
            $add(
                new Point(11333, 815, 7887),
                new Point(11333, 815, 11158),
                new Point(11333, 814, 11158),
                new Point(11333, 814, 7887),
            );

            // a long walls
            $add(
                new Point(11432, 1688, 10188),
                new Point(10296, 1688, 10188),
                new Point(10296, 642, 10188),
                new Point(11432, 642, 10188),
            );
            $add(
                new Point(11432, 1688, 10188),
                new Point(11432, 642, 10188),
                new Point(11432, 642, 9612),
                new Point(11432, 1688, 9612),
            );
            $add(
                new Point(11432, 1688, 9612),
                new Point(11432, 642, 9612),
                new Point(11384, 642, 9612),
                new Point(11384, 1688, 9612),
            );
            $add(
                new Point(11384, 1688, 9612),
                new Point(11384, 642, 9612),
                new Point(11384, 642, 8749),
                new Point(11384, 1688, 8749),
            );
            $add(
                new Point(11384, 1688, 8749),
                new Point(11384, 642, 8749),
                new Point(11456, 642, 8749),
                new Point(11456, 1688, 8749),
            );
            $add(
                new Point(11456, 1688, 8749),
                new Point(11456, 642, 8749),
                new Point(11456, 642, 7789),
                new Point(11456, 1688, 7789),
            );
            $add(
                new Point(11456, 1528, 7789),
                new Point(11456, 642, 7789),
                new Point(9609, 642, 7789),
                new Point(9609, 1528, 7789),
            );
            $add(
                new Point(10376, 1763, 10284),
                new Point(9225, 1763, 10284),
                new Point(9225, 392, 10284),
                new Point(10376, 392, 10284),
            );
            $add(
                new Point(10376, 1763, 10284),
                new Point(10376, 392, 10284),
                new Point(10376, 392, 10186),
                new Point(10376, 1763, 10186),
            );
            $add(
                new Point(9609, 1528, 7789),
                new Point(9609, 642, 7789),
                new Point(9609, 642, 7837),
                new Point(9609, 1528, 7837),
            );

            // long doors
            $add(
                new Point(9913, 823, 6222),
                new Point(9913, 1480, 6222),
                new Point(9705, 1480, 6222),
                new Point(9705, 823, 6222),
            );
            $add(
                new Point(9705, 823, 6222),
                new Point(9705, 1480, 6222),
                new Point(9705, 1480, 5918),
                new Point(9705, 823, 5918),
            );
            $add(
                new Point(9705, 823, 5918),
                new Point(9705, 1480, 5918),
                new Point(10006, 1480, 5918),
                new Point(10006, 823, 5918),
            );
            $add(
                new Point(9910, 1480, 6446),
                new Point(9277, 1480, 6446),
                new Point(9277, 1280, 6446),
                new Point(9910, 1280, 6446),
            );
            $add(
                new Point(9415, 1480, 6446),
                new Point(9222, 1480, 6446),
                new Point(9222, 823, 6446),
                new Point(9415, 823, 6446),
            );
            $add(
                new Point(9225, 799, 5016),
                new Point(9225, 799, 7405),
                new Point(9225, 1481, 7405),
                new Point(9225, 1481, 5016),
            );
            $add(
                new Point(9225, 799, 5016),
                new Point(9225, 1481, 5016),
                new Point(9346, 1481, 5016),
                new Point(9346, 799, 5016),
            );
            $add(
                new Point(9609, 764, 7837),
                new Point(9225, 764, 7405),
                new Point(9225, 1635, 7405),
                new Point(9609, 1635, 7837),
            );

            // long walls
            $add(
                new Point(12466, 1924, 13306),
                new Point(10691, 1924, 13306),
                new Point(10691, 1139, 13306),
                new Point(12466, 1139, 13306),
            );
            $add(
                new Point(13084, 1614, 9518),
                new Point(12523, 1614, 9518),
                new Point(12523, 611, 9518),
                new Point(13084, 611, 9518),
            );
            $add(
                new Point(12487, 826, 10980),
                new Point(12487, 1620, 10980),
                new Point(13063, 1620, 10980),
                new Point(13063, 826, 10980),
            );
            $add(
                new Point(12384, 1121, 12467),
                new Point(12384, 1849, 12467),
                new Point(12545, 1849, 12467),
                new Point(12545, 1121, 12467),
            );
            $add(
                new Point(12901, 871, 9474),
                new Point(12901, 871, 9676),
                new Point(12901, 1637, 9676),
                new Point(12901, 1637, 9474),
            );
            $add(
                new Point(12901, 1637, 9676),
                new Point(12901, 871, 9676),
                new Point(13079, 871, 9676),
                new Point(13079, 1637, 9676),
            );
            $add(
                new Point(13063, 826, 9666),
                new Point(13063, 826, 10980),
                new Point(13063, 1620, 10980),
                new Point(13063, 1620, 9666),
            );
            $add(
                new Point(12487, 826, 10980),
                new Point(12487, 826, 11939),
                new Point(12487, 1620, 11939),
                new Point(12487, 1620, 10980),
            );
            $add(
                new Point(12384, 1121, 12467),
                new Point(12384, 1121, 13367),
                new Point(12384, 1849, 13367),
                new Point(12384, 1849, 12467),
            );
            $add(
                new Point(12523, 611, 9518),
                new Point(12523, 1614, 9518),
                new Point(12523, 1614, 8321),
                new Point(12523, 611, 8321),
            );
            $add(
                new Point(12487, 1620, 11939),
                new Point(12487, 826, 11939),
                new Point(12535, 826, 11939),
                new Point(12535, 1620, 11939),
            );
            $add(
                new Point(12535, 1620, 11939),
                new Point(12535, 826, 11939),
                new Point(12535, 826, 12483),
                new Point(12535, 1620, 12483),
            );
        }

        // Map - A Short
        if (true) {

            // a short main floor
            $add(
                new Point(8842, 1091, 9516),
                new Point(8442, 1091, 9516),
                new Point(8442, 804, 9084),
                new Point(8842, 804, 9084),
            );
            $add(
                new Point(9298, 804, 9592),
                new Point(6966, 804, 9592),
                new Point(6966, 804, 6403),
                new Point(9298, 804, 6403),
            );

            // a short main floor.001
            $add(
                new Point(8889, 1022, 9444),
                new Point(8889, 1022, 9384),
                new Point(8889, 801, 9384),
                new Point(8889, 801, 9444),
            );
            $add(
                new Point(8889, 994, 9384),
                new Point(8889, 994, 9324),
                new Point(8889, 801, 9324),
                new Point(8889, 801, 9384),
            );
            $add(
                new Point(8889, 964, 9324),
                new Point(8889, 964, 9264),
                new Point(8889, 801, 9264),
                new Point(8889, 801, 9324),
            );
            $add(
                new Point(8889, 934, 9264),
                new Point(8889, 934, 9204),
                new Point(8889, 801, 9204),
                new Point(8889, 801, 9264),
            );
            $add(
                new Point(8889, 899, 9204),
                new Point(8889, 899, 9133),
                new Point(8889, 801, 9133),
                new Point(8889, 801, 9204),
            );
            $add(
                new Point(8889, 1054, 9504),
                new Point(8889, 1054, 9444),
                new Point(8889, 801, 9444),
                new Point(8889, 801, 9504),
            );
            $add(
                new Point(8889, 934, 9204),
                new Point(8889, 934, 9264),
                new Point(8841, 934, 9264),
                new Point(8841, 934, 9204),
            );
            $add(
                new Point(8889, 1022, 9384),
                new Point(8889, 1022, 9444),
                new Point(8841, 1022, 9444),
                new Point(8841, 1022, 9384),
            );
            $add(
                new Point(8889, 994, 9324),
                new Point(8889, 994, 9384),
                new Point(8841, 994, 9384),
                new Point(8841, 994, 9324),
            );
            $add(
                new Point(8889, 1054, 9444),
                new Point(8889, 1054, 9504),
                new Point(8841, 1054, 9504),
                new Point(8841, 1054, 9444),
            );
            $add(
                new Point(8889, 899, 9133),
                new Point(8889, 899, 9204),
                new Point(8841, 899, 9204),
                new Point(8841, 899, 9133),
            );
            $add(
                new Point(8889, 964, 9264),
                new Point(8889, 964, 9324),
                new Point(8841, 964, 9324),
                new Point(8841, 964, 9264),
            );
            $add(
                new Point(8841, 1054, 9444),
                new Point(8841, 1054, 9504),
                new Point(8841, 801, 9504),
                new Point(8841, 801, 9444),
            );
            $add(
                new Point(8841, 934, 9204),
                new Point(8841, 934, 9264),
                new Point(8841, 801, 9264),
                new Point(8841, 801, 9204),
            );
            $add(
                new Point(8841, 994, 9324),
                new Point(8841, 994, 9384),
                new Point(8841, 801, 9384),
                new Point(8841, 801, 9324),
            );
            $add(
                new Point(8841, 1022, 9384),
                new Point(8841, 1022, 9444),
                new Point(8841, 801, 9444),
                new Point(8841, 801, 9384),
            );
            $add(
                new Point(8841, 899, 9133),
                new Point(8841, 899, 9204),
                new Point(8841, 801, 9204),
                new Point(8841, 801, 9133),
            );
            $add(
                new Point(8841, 964, 9264),
                new Point(8841, 964, 9324),
                new Point(8841, 801, 9324),
                new Point(8841, 801, 9264),
            );
            $add(
                new Point(8841, 899, 9133),
                new Point(8841, 801, 9133),
                new Point(8889, 801, 9133),
                new Point(8889, 899, 9133),
            );
            $add(
                new Point(8841, 934, 9204),
                new Point(8841, 801, 9204),
                new Point(8889, 801, 9204),
                new Point(8889, 934, 9204),
            );
            $add(
                new Point(8841, 964, 9264),
                new Point(8841, 801, 9264),
                new Point(8889, 801, 9264),
                new Point(8889, 964, 9264),
            );
            $add(
                new Point(8841, 994, 9324),
                new Point(8841, 801, 9324),
                new Point(8889, 801, 9324),
                new Point(8889, 994, 9324),
            );
            $add(
                new Point(8841, 1022, 9384),
                new Point(8841, 801, 9384),
                new Point(8889, 801, 9384),
                new Point(8889, 1022, 9384),
            );
            $add(
                new Point(8841, 1054, 9444),
                new Point(8841, 801, 9444),
                new Point(8889, 801, 9444),
                new Point(8889, 1054, 9444),
            );

            // short walls
            $add(
                new Point(8457, 772, 8941),
                new Point(8457, 772, 10032),
                new Point(8457, 1689, 10032),
                new Point(8457, 1689, 8941),
            );
            $add(
                new Point(8457, 772, 8941),
                new Point(8457, 1689, 8941),
                new Point(8265, 1689, 8749),
                new Point(8265, 772, 8749),
            );
            $add(
                new Point(8265, 772, 8749),
                new Point(8265, 1689, 8749),
                new Point(8073, 1689, 8749),
                new Point(8073, 772, 8749),
            );
            $add(
                new Point(8073, 772, 8749),
                new Point(8073, 1689, 8749),
                new Point(7882, 1689, 8941),
                new Point(7882, 772, 8941),
            );
            $add(
                new Point(7882, 772, 8941),
                new Point(7882, 1689, 8941),
                new Point(7306, 1689, 8941),
                new Point(7306, 772, 8941),
            );
            $add(
                new Point(7306, 772, 8941),
                new Point(7306, 1689, 8941),
                new Point(7114, 1689, 8749),
                new Point(7114, 772, 8749),
            );
            $add(
                new Point(7114, 772, 8749),
                new Point(7114, 1689, 8749),
                new Point(6925, 1689, 8749),
                new Point(6925, 772, 8749),
            );
        }

        // Map - A Side
        if (true) {

            // a boundary.001
            $add(
                new Point(10808, 1187, 11387),
                new Point(10760, 1187, 11387),
                new Point(10760, 1187, 11052),
                new Point(10808, 1187, 11052),
            );
            $add(
                new Point(10808, 1187, 11387),
                new Point(10808, 1187, 11052),
                new Point(10808, 1084, 11052),
                new Point(10808, 1084, 11387),
            );
            $add(
                new Point(10760, 1187, 11052),
                new Point(10760, 1187, 11387),
                new Point(10760, 818, 11387),
                new Point(10760, 818, 11052),
            );

            // a boundary.002
            $add(
                new Point(10792, 1187, 11387),
                new Point(9277, 1187, 11387),
                new Point(9277, 1187, 11339),
                new Point(10792, 1187, 11339),
            );
            $add(
                new Point(9277, 1187, 11387),
                new Point(10792, 1187, 11387),
                new Point(10792, 1066, 11387),
                new Point(9277, 1066, 11387),
            );
            $add(
                new Point(10792, 1187, 11339),
                new Point(9277, 1187, 11339),
                new Point(9277, 522, 11339),
                new Point(10792, 522, 11339),
            );

            // a boundary.003
            $add(
                new Point(11528, 1187, 11100),
                new Point(10760, 1187, 11100),
                new Point(10760, 1187, 11052),
                new Point(11528, 1187, 11052),
            );
            $add(
                new Point(10760, 1187, 11100),
                new Point(11528, 1187, 11100),
                new Point(11528, 1100, 11103),
                new Point(10760, 1100, 11103),
            );
            $add(
                new Point(11528, 1187, 11052),
                new Point(10760, 1187, 11052),
                new Point(10760, 797, 11052),
                new Point(11528, 797, 11052),
            );

            // a car
            $add(
                new Point(12497, 908, 8063),
                new Point(12200, 806, 8063),
                new Point(12487, 791, 7692),
                null,
            );
            $add(
                new Point(12200, 806, 9502),
                new Point(13071, 1105, 9502),
                new Point(13071, 1105, 11263),
                new Point(12200, 806, 11263),
            );
            $add(
                new Point(12636, 954, 8063),
                new Point(12631, 954, 9502),
                new Point(12200, 806, 9502),
                new Point(12200, 806, 8063),
            );

            // a corner barrels
            $add(
                new Point(8872, 1216, 12469),
                new Point(8872, 1216, 12254),
                new Point(8484, 1216, 12254),
                new Point(8484, 1216, 12469),
            );
            $add(
                new Point(8484, 1216, 12254),
                new Point(8872, 1216, 12254),
                new Point(8872, 1100, 12254),
                new Point(8484, 1100, 12254),
            );
            $add(
                new Point(8872, 1216, 12254),
                new Point(8872, 1216, 12469),
                new Point(8872, 1100, 12469),
                new Point(8872, 1100, 12254),
            );

            // a side
            $add(
                new Point(10792, 1091, 12541),
                new Point(10792, 1091, 11072),
                new Point(11499, 1091, 11072),
                new Point(11499, 1091, 12541),
            );
            $add(
                new Point(8421, 1091, 11348),
                new Point(10792, 1091, 11348),
                new Point(10792, 1091, 12643),
                new Point(8421, 1091, 12643),
            );
            $add(
                new Point(9298, 1091, 9516),
                new Point(9298, 1091, 11387),
                new Point(8421, 1091, 11387),
                new Point(8421, 1091, 9516),
            );
            $add(
                new Point(9298, 1091, 9516),
                new Point(8421, 1091, 9516),
                new Point(8421, 785, 9516),
                new Point(9298, 785, 9516),
            );

            // a side wall
            $add(
                new Point(11528, 1187, 12535),
                new Point(11480, 1187, 12535),
                new Point(11480, 1187, 11052),
                new Point(11528, 1187, 11052),
            );
            $add(
                new Point(11528, 1187, 12535),
                new Point(11528, 1187, 11052),
                new Point(11528, 813, 11052),
                new Point(11528, 813, 12535),
            );
            $add(
                new Point(11480, 1187, 11052),
                new Point(11480, 1187, 12535),
                new Point(11480, 817, 12535),
                new Point(11480, 817, 11052),
            );
            $add(
                new Point(11528, 1187, 12535),
                new Point(10733, 1187, 12535),
                new Point(10733, 1187, 13393),
                new Point(11528, 1187, 13393),
            );

            // a site to short walls
            $add(
                new Point(10760, 1048, 12395),
                new Point(10760, 1048, 13388),
                new Point(10760, 1721, 13388),
                new Point(10760, 1721, 12395),
            );
            $add(
                new Point(10760, 1048, 12395),
                new Point(10760, 1721, 12395),
                new Point(9849, 1721, 12395),
                new Point(9849, 1048, 12395),
            );
            $add(
                new Point(9849, 1048, 12395),
                new Point(9849, 1721, 12395),
                new Point(9849, 1721, 12293),
                new Point(9849, 1048, 12293),
            );
            $add(
                new Point(9849, 1048, 12293),
                new Point(9849, 1721, 12293),
                new Point(9453, 1721, 12293),
                new Point(9453, 1048, 12293),
            );
            $add(
                new Point(9453, 1048, 12293),
                new Point(9453, 1721, 12293),
                new Point(9453, 1721, 12539),
                new Point(9453, 1048, 12539),
            );
            $add(
                new Point(9453, 1048, 12539),
                new Point(9453, 1721, 12539),
                new Point(9321, 1721, 12539),
                new Point(9321, 1048, 12539),
            );
            $add(
                new Point(9321, 1048, 12539),
                new Point(9321, 1721, 12539),
                new Point(9321, 1721, 12491),
                new Point(9321, 1048, 12491),
            );
            $add(
                new Point(9321, 1048, 12491),
                new Point(9321, 1721, 12491),
                new Point(8457, 1721, 12491),
                new Point(8457, 1048, 12491),
            );
            $add(
                new Point(8457, 1048, 12491),
                new Point(8457, 1721, 12491),
                new Point(8457, 1721, 12323),
                new Point(8457, 1048, 12323),
            );
            $add(
                new Point(8457, 1048, 12323),
                new Point(8457, 1721, 12323),
                new Point(8553, 1721, 12227),
                new Point(8553, 1048, 12227),
            );
            $add(
                new Point(8553, 1048, 12227),
                new Point(8553, 1721, 12227),
                new Point(8553, 1721, 12035),
                new Point(8553, 1048, 12035),
            );
            $add(
                new Point(8553, 1048, 12035),
                new Point(8553, 1721, 12035),
                new Point(8457, 1721, 11939),
                new Point(8457, 1048, 11939),
            );
            $add(
                new Point(8457, 1048, 11939),
                new Point(8457, 1721, 11939),
                new Point(8457, 1721, 10332),
                new Point(8457, 1048, 10332),
            );
            $add(
                new Point(8457, 1048, 10332),
                new Point(8457, 1721, 10332),
                new Point(8553, 1721, 10236),
                new Point(8553, 1048, 10236),
            );
            $add(
                new Point(8553, 1048, 10236),
                new Point(8553, 1721, 10236),
                new Point(8553, 1721, 10044),
                new Point(8553, 1048, 10044),
            );
            $add(
                new Point(8553, 1048, 10044),
                new Point(8553, 1721, 10044),
                new Point(8450, 1721, 9941),
                new Point(8450, 1048, 9941),
            );

            // Goose
            $add(
                new Point(11534, 1091, 12418),
                new Point(11497, 1187, 12535),
                new Point(10759, 1187, 12535),
                new Point(10722, 1091, 12418),
            );

            // short boost wall
            $add(
                new Point(9321, 872, 10382),
                new Point(9321, 872, 10968),
                new Point(9321, 1187, 10968),
                new Point(9321, 1187, 10382),
            );
            $add(
                new Point(9321, 833, 10968),
                new Point(9321, 833, 11344),
                new Point(9321, 1187, 11344),
                new Point(9321, 1187, 10968),
            );
            $add(
                new Point(9321, 809, 10281),
                new Point(9321, 809, 10382),
                new Point(9321, 1187, 10382),
                new Point(9321, 1187, 10281),
            );
            $add(
                new Point(9685, 732, 11052),
                new Point(9321, 732, 11052),
                new Point(9321, 732, 11399),
                new Point(9685, 732, 11399),
            );
            $add(
                new Point(9321, 436, 11052),
                new Point(9321, 732, 11052),
                new Point(9685, 732, 11052),
                new Point(9685, 436, 11052),
            );
            $add(
                new Point(9321, 1187, 10281),
                new Point(9321, 1187, 11391),
                new Point(9273, 1187, 11391),
                new Point(9273, 1187, 10281),
            );
            $add(
                new Point(9273, 1187, 10281),
                new Point(9273, 1187, 11391),
                new Point(9273, 1091, 11391),
                new Point(9273, 1091, 10281),
            );
        }

        // Map - B Side
        if (true) {

            // b back plat floor
            $add(
                new Point(2460, 899, 13575),
                new Point(1348, 899, 13575),
                new Point(1348, 899, 11459),
                new Point(2460, 899, 11459),
            );
            $add(
                new Point(2460, 899, 13575),
                new Point(2460, 899, 11459),
                new Point(2460, 995, 11437),
                new Point(2460, 995, 13575),
            );
            $add(
                new Point(2460, 995, 13575),
                new Point(2460, 995, 11437),
                new Point(2508, 995, 11437),
                new Point(2508, 995, 13575),
            );
            $add(
                new Point(2508, 995, 13575),
                new Point(2508, 995, 11437),
                new Point(2508, 808, 11437),
                new Point(2508, 808, 13575),
            );
            $add(
                new Point(2460, 899, 11459),
                new Point(1348, 899, 11459),
                new Point(1348, 797, 11459),
                new Point(2458, 797, 11459),
            );

            // b back plat walls
            $add(
                new Point(1741, 909, 13258),
                new Point(2060, 909, 13258),
                new Point(2060, 1455, 13258),
                new Point(1741, 1455, 13258),
            );
            $add(
                new Point(1741, 909, 13258),
                new Point(1741, 1455, 13258),
                new Point(1741, 1455, 13448),
                new Point(1741, 909, 13448),
            );
            $add(
                new Point(1741, 909, 13448),
                new Point(1741, 1455, 13448),
                new Point(1338, 1455, 13448),
                new Point(1338, 909, 13448),
            );

            // b boxes
            $add(
                new Point(4268, 971, 10572),
                new Point(4268, 971, 10267),
                new Point(4574, 971, 10267),
                new Point(4574, 971, 10572),
            );
            $add(
                new Point(4268, 971, 10267),
                new Point(4268, 971, 10572),
                new Point(4268, 695, 10572),
                new Point(4268, 695, 10267),
            );
            $add(
                new Point(4268, 971, 10572),
                new Point(4574, 971, 10572),
                new Point(4574, 695, 10572),
                new Point(4268, 695, 10572),
            );
            $add(
                new Point(4574, 971, 10572),
                new Point(4574, 971, 10267),
                new Point(4574, 695, 10267),
                new Point(4574, 695, 10572),
            );
            $add(
                new Point(4268, 1071, 10572),
                new Point(4268, 1071, 10267),
                new Point(4304, 1071, 10267),
                new Point(4304, 1071, 10572),
            );
            $add(
                new Point(4304, 1071, 10572),
                new Point(4304, 1071, 10267),
                new Point(4575, 971, 10267),
                new Point(4575, 971, 10572),
            );
            $add(
                new Point(4268, 1071, 10267),
                new Point(4268, 1071, 10572),
                new Point(4268, 971, 10572),
                new Point(4268, 971, 10267),
            );

            // b boxes bottom
            $add(
                new Point(4068, 947, 10428),
                new Point(4068, 947, 10278),
                new Point(4264, 947, 10278),
                new Point(4264, 947, 10428),
            );
            $add(
                new Point(4068, 947, 10278),
                new Point(4068, 947, 10428),
                new Point(4068, 803, 10428),
                new Point(4068, 803, 10278),
            );
            $add(
                new Point(4068, 947, 10428),
                new Point(4264, 947, 10428),
                new Point(4264, 803, 10428),
                new Point(4068, 803, 10428),
            );
            $add(
                new Point(4264, 947, 10428),
                new Point(4264, 947, 10278),
                new Point(4264, 803, 10278),
                new Point(4264, 803, 10428),
            );
            $add(
                new Point(4264, 947, 10278),
                new Point(4068, 947, 10278),
                new Point(4068, 803, 10278),
                new Point(4264, 803, 10278),
            );

            // b plat walls
            $add(
                new Point(1307, 802, 11435),
                new Point(1741, 802, 11435),
                new Point(1741, 995, 11435),
                new Point(1307, 995, 11435),
            );
            $add(
                new Point(1741, 995, 11435),
                new Point(1741, 802, 11435),
                new Point(1741, 802, 11483),
                new Point(1741, 995, 11483),
            );
            $add(
                new Point(1741, 995, 11483),
                new Point(1741, 802, 11483),
                new Point(1307, 802, 11483),
                new Point(1307, 995, 11483),
            );
            $add(
                new Point(1741, 995, 11483),
                new Point(1307, 995, 11483),
                new Point(1307, 995, 11435),
                new Point(1741, 995, 11435),
            );
            $add(
                new Point(2508, 805, 11483),
                new Point(2125, 805, 11483),
                new Point(2125, 995, 11483),
                new Point(2508, 995, 11483),
            );
            $add(
                new Point(2125, 995, 11483),
                new Point(2125, 805, 11483),
                new Point(2125, 805, 11435),
                new Point(2125, 995, 11435),
            );
            $add(
                new Point(2125, 995, 11435),
                new Point(2125, 805, 11435),
                new Point(2508, 805, 11435),
                new Point(2508, 995, 11435),
            );
            $add(
                new Point(2125, 995, 11435),
                new Point(2508, 995, 11435),
                new Point(2508, 995, 11483),
                new Point(2125, 995, 11483),
            );

            // b side entry from ct
            $add(
                new Point(3660, 1480, 11052),
                new Point(3660, 1480, 12009),
                new Point(3660, 811, 12009),
                new Point(3660, 811, 11052),
            );
            $add(
                new Point(3660, 811, 12009),
                new Point(3660, 1480, 12009),
                new Point(3852, 1480, 12009),
                new Point(3852, 811, 12009),
            );
            $add(
                new Point(3852, 811, 12009),
                new Point(3852, 1480, 12009),
                new Point(3852, 1480, 11049),
                new Point(3852, 811, 11049),
            );
            $add(
                new Point(3660, 1480, 11052),
                new Point(3660, 811, 11052),
                new Point(3856, 811, 11052),
                new Point(3856, 1480, 11052),
            );
            $add(
                new Point(3660, 1480, 11075),
                new Point(3660, 1340, 11075),
                new Point(3660, 1340, 10468),
                new Point(3660, 1480, 10468),
            );
            $add(
                new Point(3852, 1340, 11074),
                new Point(3852, 1480, 11074),
                new Point(3852, 1480, 10456),
                new Point(3852, 1340, 10456),
            );

            // b side main floor
            $add(
                new Point(1031, 818, 8909),
                new Point(4240, 818, 8909),
                new Point(4240, 818, 12934),
                new Point(1031, 818, 12934),
            );

            // b side walls
            $add(
                new Point(1549, 1477, 9516),
                new Point(1549, 801, 9516),
                new Point(1165, 801, 9516),
                new Point(1165, 1477, 9516),
            );
            $add(
                new Point(1165, 1477, 9516),
                new Point(1165, 801, 9516),
                new Point(1165, 801, 10380),
                new Point(1165, 1477, 10380),
            );
            $add(
                new Point(1165, 1477, 10380),
                new Point(1165, 801, 10380),
                new Point(1261, 801, 10380),
                new Point(1261, 1477, 10380),
            );
            $add(
                new Point(1261, 1477, 10380),
                new Point(1261, 801, 10380),
                new Point(1261, 801, 10476),
                new Point(1261, 1477, 10476),
            );
            $add(
                new Point(1261, 1477, 10476),
                new Point(1261, 801, 10476),
                new Point(1213, 801, 10476),
                new Point(1213, 1477, 10476),
            );
            $add(
                new Point(1213, 1477, 10476),
                new Point(1213, 801, 10476),
                new Point(1213, 801, 11052),
                new Point(1213, 1477, 11052),
            );
            $add(
                new Point(1213, 1477, 11052),
                new Point(1213, 801, 11052),
                new Point(1261, 801, 11052),
                new Point(1261, 1477, 11052),
            );
            $add(
                new Point(1261, 1477, 11052),
                new Point(1261, 801, 11052),
                new Point(1261, 801, 11148),
                new Point(1261, 1477, 11148),
            );
            $add(
                new Point(1261, 1477, 11148),
                new Point(1261, 801, 11148),
                new Point(1165, 801, 11148),
                new Point(1165, 1477, 11148),
            );
            $add(
                new Point(1165, 1477, 11148),
                new Point(1165, 801, 11148),
                new Point(1165, 801, 11435),
                new Point(1165, 1477, 11435),
            );
            $add(
                new Point(1165, 1477, 11435),
                new Point(1165, 801, 11435),
                new Point(1357, 801, 11435),
                new Point(1357, 1477, 11435),
            );
            $add(
                new Point(1357, 1477, 11435),
                new Point(1357, 801, 11435),
                new Point(1357, 801, 13967),
                new Point(1357, 1477, 13967),
            );
            $add(
                new Point(1357, 1477, 13967),
                new Point(1357, 801, 13967),
                new Point(2029, 801, 13967),
                new Point(2029, 1477, 13967),
            );
            $add(
                new Point(2029, 1477, 13967),
                new Point(2029, 801, 13967),
                new Point(2029, 801, 13019),
                new Point(2029, 1477, 13019),
            );
            $add(
                new Point(2029, 1477, 13019),
                new Point(2029, 801, 13019),
                new Point(2269, 801, 12779),
                new Point(2269, 1477, 12779),
            );
            $add(
                new Point(2269, 1477, 12779),
                new Point(2269, 801, 12779),
                new Point(3180, 801, 12779),
                new Point(3180, 1477, 12779),
            );
            $add(
                new Point(3180, 1477, 12779),
                new Point(3180, 801, 12779),
                new Point(3660, 801, 12491),
                new Point(3660, 1477, 12491),
            );
            $add(
                new Point(3660, 1477, 12491),
                new Point(3660, 801, 12491),
                new Point(3660, 801, 12217),
                new Point(3660, 1477, 12217),
            );
            $add(
                new Point(3901, 1477, 12217),
                new Point(3901, 801, 12217),
                new Point(4159, 801, 12133),
                new Point(4159, 1477, 12133),
            );
            $add(
                new Point(4159, 1477, 12133),
                new Point(4159, 801, 12133),
                new Point(4284, 801, 12022),
                new Point(4284, 1477, 12022),
            );
            $add(
                new Point(4284, 1477, 12022),
                new Point(4284, 801, 12022),
                new Point(4410, 801, 12022),
                new Point(4410, 1477, 12022),
            );
            $add(
                new Point(3660, 1477, 12217),
                new Point(3660, 801, 12217),
                new Point(3901, 801, 12217),
                new Point(3901, 1477, 12217),
            );

            // b walls
            $add(
                new Point(2364, 1477, 9516),
                new Point(2364, 801, 9516),
                new Point(1933, 801, 9516),
                new Point(1933, 1477, 9516),
            );
            $add(
                new Point(2364, 801, 9516),
                new Point(2364, 1477, 9516),
                new Point(2508, 1477, 9372),
                new Point(2508, 801, 9372),
            );
            $add(
                new Point(2508, 801, 9372),
                new Point(2508, 1477, 9372),
                new Point(2508, 1477, 8989),
                new Point(2508, 801, 8989),
            );
            $add(
                new Point(2508, 801, 8989),
                new Point(2508, 1477, 8989),
                new Point(2892, 1477, 8989),
                new Point(2892, 801, 8989),
            );
            $add(
                new Point(2892, 801, 8989),
                new Point(2892, 1477, 8989),
                new Point(2892, 1477, 9133),
                new Point(2892, 801, 9133),
            );
            $add(
                new Point(2892, 801, 9133),
                new Point(2892, 1477, 9133),
                new Point(3468, 1477, 9708),
                new Point(3468, 801, 9708),
            );
            $add(
                new Point(3468, 801, 9708),
                new Point(3468, 1477, 9708),
                new Point(3564, 1477, 9708),
                new Point(3564, 801, 9708),
            );
            $add(
                new Point(3564, 801, 9708),
                new Point(3564, 1477, 9708),
                new Point(3564, 1477, 10380),
                new Point(3564, 801, 10380),
            );
            $add(
                new Point(3564, 801, 10380),
                new Point(3564, 1477, 10380),
                new Point(3660, 1477, 10380),
                new Point(3660, 801, 10380),
            );
            $add(
                new Point(3660, 801, 10380),
                new Point(3660, 1477, 10380),
                new Point(3660, 1477, 10476),
                new Point(3660, 801, 10476),
            );
            $add(
                new Point(3660, 801, 10476),
                new Point(3660, 1477, 10476),
                new Point(3852, 1477, 10476),
                new Point(3852, 801, 10476),
            );
            $add(
                new Point(3852, 801, 10476),
                new Point(3852, 1477, 10476),
                new Point(3852, 1477, 10380),
                new Point(3852, 801, 10380),
            );
            $add(
                new Point(3852, 801, 10380),
                new Point(3852, 1477, 10380),
                new Point(3996, 1477, 10380),
                new Point(3996, 801, 10380),
            );
            $add(
                new Point(3996, 801, 10380),
                new Point(3996, 1477, 10380),
                new Point(3996, 1477, 10284),
                new Point(3996, 801, 10284),
            );
            $add(
                new Point(3996, 801, 10284),
                new Point(3996, 1477, 10284),
                new Point(4571, 1477, 10284),
                new Point(4571, 801, 10284),
            );
            $add(
                new Point(4571, 801, 10284),
                new Point(4571, 1477, 10284),
                new Point(4571, 1477, 10381),
                new Point(4571, 801, 10381),
            );
            $add(
                new Point(1960, 1477, 9516),
                new Point(1960, 1317, 9516),
                new Point(1513, 1317, 9516),
                new Point(1513, 1477, 9516),
            );

            // b window access
            $add(
                new Point(3854, 1116, 12041),
                new Point(3854, 905, 11853),
                new Point(4364, 905, 11853),
                new Point(4364, 1116, 12041),
            );
            $add(
                new Point(4303, 905, 11867),
                new Point(3832, 905, 11867),
                new Point(3832, 815, 11542),
                new Point(4303, 815, 11542),
            );
            $add(
                new Point(4364, 905, 11853),
                new Point(3854, 905, 11853),
                new Point(3832, 862, 11853),
                new Point(4364, 862, 11853),
            );
            $add(
                new Point(4288, 805, 11532),
                new Point(4447, 807, 11872),
                new Point(4317, 917, 11909),
                null,
            );
            $add(
                new Point(3849, 1111, 12041),
                new Point(4311, 1111, 12041),
                new Point(4311, 1158, 12252),
                new Point(3849, 1158, 12252),
            );
        }

        // Map - Boundary
        if (true) {

            // door.008
            $add(
                new Point(3853, 1340, 12003),
                new Point(3660, 1340, 12003),
                new Point(3660, 1340, 10456),
                new Point(3853, 1340, 10456),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(3853, 1477, 12003),
                new Point(3853, 1477, 10456),
                new Point(3660, 1477, 10456),
                new Point(3660, 1477, 12003),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(3853, 1340, 10456),
                new Point(3660, 1340, 10456),
                new Point(3660, 1477, 10456),
                new Point(3853, 1477, 10456),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(3660, 1340, 10456),
                new Point(3660, 1340, 12003),
                new Point(3660, 1477, 12003),
                new Point(3660, 1477, 10456),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(3660, 1340, 12003),
                new Point(3853, 1340, 12003),
                new Point(3853, 1477, 12003),
                new Point(3660, 1477, 12003),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(3853, 1340, 12003),
                new Point(3853, 1340, 10456),
                new Point(3853, 1477, 10456),
                new Point(3853, 1477, 12003),
                penetrable: false,
                navmesh: false,
            );

            // pit ceiling
            $add(
                new Point(12562, 825, 4585),
                new Point(12562, 825, 5158),
                new Point(11499, 825, 5158),
                new Point(11499, 825, 4585),
                penetrable: false,
                navmesh: false,
            );

            // plane.001
            $add(
                new Point(-339, 109, -82),
                new Point(14319, 109, -82),
                new Point(14319, 109, 14575),
                new Point(-339, 109, 14575),
                penetrable: false,
                navmesh: false,
            );

            // plane.002
            $add(
                new Point(-339, -256, 14455),
                new Point(14319, -256, 14455),
                new Point(14319, 14401, 14455),
                new Point(-339, 14401, 14455),
                penetrable: false,
                navmesh: false,
            );

            // plane.003
            $add(
                new Point(-339, -256, 154),
                new Point(14319, -256, 154),
                new Point(14319, 14401, 154),
                new Point(-339, 14401, 154),
                penetrable: false,
                navmesh: false,
            );

            // plane.004
            $add(
                new Point(-339, 10730, -82),
                new Point(14319, 10730, -82),
                new Point(14319, 10730, 14575),
                new Point(-339, 10730, 14575),
                penetrable: false,
                navmesh: false,
            );

            // plane.005
            $add(
                new Point(20, -256, -107),
                new Point(20, -256, 14550),
                new Point(20, 14401, 14550),
                new Point(20, 14401, -107),
                penetrable: false,
                navmesh: false,
            );

            // plane.006
            $add(
                new Point(14196, -256, -107),
                new Point(14196, -256, 14550),
                new Point(14196, 14401, 14550),
                new Point(14196, 14401, -107),
                penetrable: false,
                navmesh: false,
            );

            // plane.007
            $add(
                new Point(5006, 1959, 2460),
                new Point(5006, 1959, 1104),
                new Point(4802, 1959, 1104),
                new Point(4802, 1959, 2460),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5006, 1959, 2460),
                new Point(4802, 1959, 2460),
                new Point(4802, 1813, 2460),
                new Point(5006, 1813, 2460),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5006, 1813, 2460),
                new Point(4802, 1813, 2460),
                new Point(4802, 1813, 3662),
                new Point(5006, 1813, 3662),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5006, 1813, 2460),
                new Point(5006, 1813, 3662),
                new Point(5006, 1568, 3662),
                new Point(5006, 1568, 2460),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5006, 1568, 2417),
                new Point(6152, 1568, 2417),
                new Point(6152, 1790, 2417),
                new Point(5006, 1790, 2417),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5006, 1568, 2417),
                new Point(5006, 1568, 4164),
                new Point(6152, 1568, 4164),
                new Point(6152, 1568, 2417),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5006, 1790, 2417),
                new Point(6152, 1790, 2417),
                new Point(6152, 1790, 2229),
                new Point(5006, 1790, 2229),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(6152, 1568, 4164),
                new Point(5006, 1568, 4164),
                new Point(5006, 1777, 4164),
                new Point(6152, 1777, 4164),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(6152, 1777, 4164),
                new Point(5772, 1777, 4164),
                new Point(5772, 1777, 4726),
                new Point(6152, 1777, 4726),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5772, 1913, 4164),
                new Point(5006, 1913, 4164),
                new Point(5006, 1913, 4726),
                new Point(5772, 1913, 4726),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5772, 1777, 4726),
                new Point(5772, 1913, 4726),
                new Point(5643, 1913, 4726),
                new Point(5643, 1777, 4726),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5772, 1777, 4726),
                new Point(5772, 1777, 4164),
                new Point(5772, 1913, 4164),
                new Point(5772, 1913, 4726),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5772, 1777, 4164),
                new Point(5006, 1777, 4164),
                new Point(5006, 1913, 4164),
                new Point(5772, 1913, 4164),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(4952, 1913, 4892),
                new Point(5643, 1913, 4892),
                new Point(5643, 1913, 4363),
                new Point(4952, 1913, 4363),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5643, 1777, 4892),
                new Point(5643, 1913, 4892),
                new Point(5510, 1913, 4892),
                new Point(5510, 1777, 4892),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5643, 1777, 4726),
                new Point(5643, 1913, 4726),
                new Point(5643, 1913, 4892),
                new Point(5643, 1777, 4892),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5510, 1777, 4892),
                new Point(5510, 1913, 4892),
                new Point(4952, 1913, 4892),
                new Point(4952, 1777, 4892),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5510, 1777, 4892),
                new Point(4952, 1777, 4892),
                new Point(4952, 1777, 5685),
                new Point(5510, 1777, 5685),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5572, 1777, 5685),
                new Point(4952, 1777, 5685),
                new Point(4952, 1777, 6322),
                new Point(5572, 1777, 6322),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(6154, 1777, 6322),
                new Point(6154, 1777, 6781),
                new Point(5673, 1777, 6781),
                new Point(5673, 1777, 6322),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(6154, 1777, 6781),
                new Point(6154, 1777, 6322),
                new Point(6154, 2051, 6322),
                new Point(6154, 2051, 6781),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(6154, 1777, 6322),
                new Point(5673, 1777, 6322),
                new Point(5673, 2051, 6322),
                new Point(6154, 2051, 6322),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5673, 1777, 6322),
                new Point(5673, 1777, 6781),
                new Point(5673, 2051, 6781),
                new Point(5673, 2051, 6322),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5673, 1777, 6781),
                new Point(6154, 1777, 6781),
                new Point(6154, 2051, 6781),
                new Point(5673, 2051, 6781),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(6114, 2437, 6738),
                new Point(6114, 2437, 6345),
                new Point(6114, 1980, 6345),
                new Point(6114, 1980, 6738),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(6114, 2437, 6345),
                new Point(5713, 2437, 6345),
                new Point(5713, 1980, 6345),
                new Point(6114, 1980, 6345),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5713, 2437, 6345),
                new Point(5713, 2437, 6738),
                new Point(5713, 1980, 6738),
                new Point(5713, 1980, 6345),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5713, 2437, 6738),
                new Point(6114, 2437, 6738),
                new Point(6114, 1980, 6738),
                new Point(5713, 1980, 6738),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(6007, 2598, 6450),
                new Point(5820, 2598, 6450),
                new Point(5820, 2598, 6633),
                new Point(6007, 2598, 6633),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(6007, 2598, 6450),
                new Point(6007, 2598, 6633),
                new Point(6007, 2327, 6633),
                new Point(6007, 2327, 6450),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5820, 2598, 6450),
                new Point(6007, 2598, 6450),
                new Point(6007, 2327, 6450),
                new Point(5820, 2327, 6450),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5820, 2598, 6633),
                new Point(5820, 2598, 6450),
                new Point(5820, 2327, 6450),
                new Point(5820, 2327, 6633),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(6007, 2598, 6633),
                new Point(5820, 2598, 6633),
                new Point(5820, 2327, 6633),
                new Point(6007, 2327, 6633),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5006, 1813, 3662),
                new Point(4802, 1813, 3662),
                new Point(4802, 1574, 3662),
                new Point(5006, 1574, 3662),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(2168, 1840, 884),
                new Point(2850, 1840, 884),
                new Point(2846, 2295, 884),
                new Point(2168, 2295, 884),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(5006, 1574, 3662),
                new Point(4161, 1574, 3662),
                new Point(4161, 1574, 3994),
                new Point(5006, 1574, 3994),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(4161, 1926, 3994),
                new Point(3495, 1926, 3994),
                new Point(3495, 1926, 3550),
                new Point(4161, 1926, 3550),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(4161, 1926, 3550),
                new Point(3495, 1926, 3550),
                new Point(3495, 1537, 3550),
                new Point(4161, 1537, 3550),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(4161, 1926, 3994),
                new Point(4161, 1926, 3550),
                new Point(4161, 1537, 3550),
                new Point(4161, 1537, 3994),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(3495, 1926, 3994),
                new Point(4161, 1926, 3994),
                new Point(4161, 1537, 3994),
                new Point(3495, 1537, 3994),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(3495, 1926, 3550),
                new Point(3495, 1926, 3994),
                new Point(3495, 1537, 3994),
                new Point(3495, 1537, 3550),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(1432, 1840, 1104),
                new Point(2847, 1840, 1104),
                new Point(2847, 1840, 884),
                new Point(1432, 1840, 884),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(3927, 1840, 1104),
                new Point(5006, 1840, 1104),
                new Point(5006, 1840, 884),
                new Point(3927, 1840, 884),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(2847, 1840, 1104),
                new Point(3927, 1840, 1104),
                new Point(3927, 1840, 884),
                new Point(2847, 1840, 884),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(2847, 1840, 884),
                new Point(3927, 1840, 884),
                new Point(3927, 2130, 884),
                new Point(2847, 2130, 884),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(3927, 1840, 884),
                new Point(4658, 1840, 884),
                new Point(4658, 2286, 884),
                new Point(3927, 2286, 884),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(2168, 1840, 884),
                new Point(2168, 2295, 884),
                new Point(2168, 2295, 1026),
                new Point(2168, 1840, 1026),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(2168, 1840, 1026),
                new Point(2168, 2295, 1026),
                new Point(745, 2295, 1026),
                new Point(745, 1840, 1026),
                penetrable: false,
                navmesh: false,
            );

            // plane.008
            $add(
                new Point(1447, 1679, 3861),
                new Point(1447, 1679, 2081),
                new Point(421, 1679, 2081),
                new Point(421, 1679, 3861),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(421, 1679, 3861),
                new Point(421, 1679, 2081),
                new Point(421, 2161, 2081),
                new Point(421, 2161, 3861),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(1447, 1679, 3861),
                new Point(421, 1679, 3861),
                new Point(421, 1457, 3861),
                new Point(1447, 1457, 3861),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(1447, 1457, 3861),
                new Point(421, 1457, 3861),
                new Point(421, 1457, 6535),
                new Point(1447, 1457, 6535),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(421, 1457, 3861),
                new Point(421, 2161, 3861),
                new Point(421, 2161, 4455),
                new Point(421, 1457, 4455),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(421, 1457, 4455),
                new Point(421, 2161, 4455),
                new Point(620, 2161, 4455),
                new Point(620, 1457, 4455),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(620, 1457, 4455),
                new Point(620, 2161, 4455),
                new Point(620, 2161, 5306),
                new Point(620, 1457, 5306),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(2346, 1457, 6982),
                new Point(1030, 1457, 6982),
                new Point(1030, 1457, 9515),
                new Point(2346, 1457, 9515),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(4773, 1457, 6982),
                new Point(3476, 1457, 6982),
                new Point(3476, 1457, 9696),
                new Point(4773, 1457, 9696),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(3476, 1457, 6982),
                new Point(2346, 1457, 6982),
                new Point(2346, 1457, 8996),
                new Point(3476, 1457, 8996),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(3770, 944, 7988),
                new Point(3770, 944, 8996),
                new Point(6085, 944, 8996),
                new Point(6085, 944, 7988),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(1156, 1457, 11437),
                new Point(1156, 1457, 6982),
                new Point(735, 1457, 6982),
                new Point(735, 1457, 11437),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(1354, 1457, 11437),
                new Point(735, 1457, 11437),
                new Point(735, 1457, 14125),
                new Point(1354, 1457, 14125),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(2315, 1350, 14125),
                new Point(735, 1350, 14125),
                new Point(735, 1350, 15124),
                new Point(2315, 1350, 15124),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(2032, 1460, 12992),
                new Point(2032, 1460, 15917),
                new Point(3522, 1460, 15917),
                new Point(3522, 1460, 12992),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(3522, 1460, 12992),
                new Point(3522, 1460, 15917),
                new Point(3522, 1737, 15917),
                new Point(3522, 1737, 12992),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(3522, 1737, 12328),
                new Point(3522, 1737, 14269),
                new Point(5067, 1737, 14269),
                new Point(5067, 1737, 12328),
                penetrable: false,
                navmesh: false,
            );

            // plane.009
            $add(
                new Point(9314, 974, 11822),
                new Point(9314, 974, 10082),
                new Point(7044, 974, 10082),
                new Point(7044, 974, 11822),
                penetrable: false,
                navmesh: false,
            );

            // plane.010
            $add(
                new Point(11382, 1505, 10188),
                new Point(11382, 1505, 7802),
                new Point(9230, 1505, 7802),
                new Point(9230, 1505, 10188),
                penetrable: false,
                navmesh: false,
            );

            // plane.011
            $add(
                new Point(9787, 1377, 5481),
                new Point(9787, 1377, 5400),
                new Point(9428, 1377, 5400),
                new Point(9428, 1377, 5481),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(9787, 1377, 5910),
                new Point(9787, 1377, 5829),
                new Point(9428, 1377, 5829),
                new Point(9428, 1377, 5910),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(9759, 1377, 6248),
                new Point(10544, 1377, 6248),
                new Point(10544, 1377, 5095),
                new Point(9759, 1377, 5095),
                penetrable: false,
                navmesh: false,
            );
            $add(
                new Point(9208, 1377, 6248),
                new Point(9448, 1377, 6248),
                new Point(9448, 1377, 5095),
                new Point(9208, 1377, 5095),
                penetrable: false,
                navmesh: false,
            );

            // topmidboundary
            $add(
                new Point(6538, 1517, 4725),
                new Point(6538, 1517, 2992),
                new Point(6154, 1517, 2992),
                new Point(6154, 1517, 4725),
                penetrable: false,
                navmesh: false,
            );
        }

        // Map - CT spawn
        if (true) {

            // ct spawn main floor
            $add(
                new Point(5387, 425, 11948),
                new Point(5387, 425, 10239),
                new Point(5954, 440, 10239),
                new Point(5954, 440, 11948),
            );
            $add(
                new Point(6966, 440, 10163),
                new Point(5817, 440, 10163),
                new Point(5817, 440, 8680),
                new Point(6966, 440, 8680),
            );
            $add(
                new Point(9374, 440, 11353),
                new Point(9374, 440, 10163),
                new Point(10210, 698, 10163),
                new Point(10210, 698, 11353),
            );
            $add(
                new Point(10210, 698, 11353),
                new Point(10210, 698, 10163),
                new Point(10649, 820, 10163),
                new Point(10649, 820, 11353),
            );
            $add(
                new Point(10649, 820, 11353),
                new Point(10649, 820, 10163),
                new Point(11355, 820, 10163),
                new Point(11355, 820, 11353),
            );
            $add(
                new Point(5750, 440, 11948),
                new Point(5750, 440, 10013),
                new Point(9374, 440, 10013),
                new Point(9374, 440, 11948),
            );
            $add(
                new Point(4240, 818, 10239),
                new Point(5387, 425, 10239),
                new Point(5387, 425, 11948),
                new Point(4240, 818, 11948),
            );
            $add(
                new Point(11355, 820, 11353),
                new Point(11355, 820, 10163),
                new Point(11355, 805, 10163),
                new Point(11355, 805, 11353),
            );

            // ct to b side walls
            $add(
                new Point(9225, 994, 11723),
                new Point(9225, 994, 11052),
                new Point(9225, 405, 11052),
                new Point(9225, 405, 11723),
            );
            $add(
                new Point(9225, 994, 11723),
                new Point(9225, 405, 11723),
                new Point(7882, 405, 11723),
                new Point(7882, 994, 11723),
            );
            $add(
                new Point(7882, 994, 11723),
                new Point(7882, 405, 11723),
                new Point(7882, 405, 11339),
                new Point(7882, 994, 11339),
            );
            $add(
                new Point(7882, 994, 11339),
                new Point(7882, 405, 11339),
                new Point(7594, 405, 11052),
                new Point(7594, 994, 11052),
            );
            $add(
                new Point(7594, 994, 11052),
                new Point(7594, 405, 11052),
                new Point(6922, 405, 11052),
                new Point(6922, 994, 11052),
            );
            $add(
                new Point(6922, 1803, 11052),
                new Point(6922, 405, 11052),
                new Point(6730, 405, 11243),
                new Point(6730, 1803, 11243),
            );
            $add(
                new Point(6730, 1803, 11243),
                new Point(6730, 405, 11243),
                new Point(6730, 405, 11427),
                new Point(6730, 1803, 11427),
            );
            $add(
                new Point(6730, 1803, 11427),
                new Point(6730, 405, 11427),
                new Point(6048, 405, 11427),
                new Point(6048, 1803, 11427),
            );
            $add(
                new Point(6048, 1803, 11427),
                new Point(6048, 405, 11427),
                new Point(5861, 405, 11511),
                new Point(5861, 1803, 11511),
            );
            $add(
                new Point(5861, 1803, 11511),
                new Point(5861, 405, 11511),
                new Point(5861, 405, 11579),
                new Point(5861, 1803, 11579),
            );
            $add(
                new Point(5861, 1803, 11579),
                new Point(5861, 405, 11579),
                new Point(5657, 405, 11731),
                new Point(5657, 1803, 11731),
            );
            $add(
                new Point(5657, 1803, 11731),
                new Point(5657, 405, 11731),
                new Point(5383, 405, 11731),
                new Point(5383, 1803, 11731),
            );
            $add(
                new Point(5383, 1803, 11731),
                new Point(5383, 405, 11731),
                new Point(5078, 405, 11865),
                new Point(5078, 1803, 11865),
            );
            $add(
                new Point(5078, 1803, 11865),
                new Point(5078, 405, 11865),
                new Point(4372, 405, 11865),
                new Point(4372, 1803, 11865),
            );
            $add(
                new Point(4372, 1803, 11865),
                new Point(4372, 405, 11865),
                new Point(4372, 405, 12301),
                new Point(4372, 1803, 12301),
            );
            $add(
                new Point(4372, 1803, 12301),
                new Point(4372, 405, 12301),
                new Point(3671, 405, 12301),
                new Point(3671, 1803, 12301),
            );
            $add(
                new Point(3671, 1803, 12301),
                new Point(3671, 405, 12301),
                new Point(3671, 405, 13268),
                new Point(3671, 1803, 13268),
            );
            $add(
                new Point(7018, 812, 11175),
                new Point(7018, 1666, 11175),
                new Point(7018, 1666, 10881),
                new Point(7018, 812, 10881),
            );
            $add(
                new Point(7018, 884, 10881),
                new Point(7018, 884, 10097),
                new Point(6922, 884, 10097),
                new Point(6922, 884, 10881),
            );
            $add(
                new Point(6922, 884, 10881),
                new Point(6922, 884, 10097),
                new Point(6922, 1091, 10097),
                new Point(6922, 1091, 10881),
            );
            $add(
                new Point(7018, 884, 10097),
                new Point(7018, 1666, 10097),
                new Point(6912, 1666, 10097),
                new Point(6912, 884, 10097),
            );
            $add(
                new Point(7018, 1666, 10097),
                new Point(7018, 884, 10097),
                new Point(7018, 884, 10881),
                new Point(7018, 1666, 10881),
            );
            $add(
                new Point(6922, 884, 10881),
                new Point(6922, 1091, 10881),
                new Point(6922, 1091, 11107),
                new Point(6922, 884, 11107),
            );
            $add(
                new Point(6922, 1091, 11107),
                new Point(6922, 1091, 10881),
                new Point(7046, 1091, 10881),
                new Point(7046, 1091, 11107),
            );
            $add(
                new Point(6922, 1091, 10881),
                new Point(6922, 1091, 10097),
                new Point(7046, 1091, 10097),
                new Point(7046, 1091, 10881),
            );
            $add(
                new Point(6922, 405, 11052),
                new Point(6922, 1803, 11052),
                new Point(7036, 1803, 11052),
                new Point(7036, 405, 11052),
            );
            $add(
                new Point(9225, 405, 11052),
                new Point(9225, 994, 11052),
                new Point(9321, 994, 11052),
                new Point(9321, 405, 11052),
            );
            $add(
                new Point(9321, 405, 11052),
                new Point(9321, 994, 11052),
                new Point(9321, 994, 11339),
                new Point(9321, 405, 11339),
            );

            // mid to b walls
            $add(
                new Point(4571, 312, 10380),
                new Point(4571, 1477, 10380),
                new Point(4955, 1477, 10380),
                new Point(4955, 312, 10380),
            );
            $add(
                new Point(4955, 312, 10380),
                new Point(4955, 1477, 10380),
                new Point(4955, 1477, 10284),
                new Point(4955, 312, 10284),
            );
            $add(
                new Point(4955, 312, 10284),
                new Point(4955, 1477, 10284),
                new Point(5531, 1477, 10284),
                new Point(5531, 312, 10284),
            );
            $add(
                new Point(5531, 312, 10284),
                new Point(5531, 1477, 10284),
                new Point(5531, 1477, 10380),
                new Point(5531, 312, 10380),
            );
            $add(
                new Point(5531, 312, 10380),
                new Point(5531, 1477, 10380),
                new Point(5915, 1477, 10380),
                new Point(5915, 312, 10380),
            );
            $add(
                new Point(5915, 312, 10380),
                new Point(5915, 1477, 10380),
                new Point(5915, 1477, 9996),
                new Point(5915, 312, 9996),
            );
            $add(
                new Point(5915, 312, 9996),
                new Point(5915, 1477, 9996),
                new Point(5819, 1477, 9996),
                new Point(5819, 312, 9996),
            );
            $add(
                new Point(5819, 312, 9996),
                new Point(5819, 1477, 9996),
                new Point(5819, 1477, 9420),
                new Point(5819, 312, 9420),
            );
            $add(
                new Point(5819, 312, 9420),
                new Point(5819, 1477, 9420),
                new Point(5915, 1477, 9420),
                new Point(5915, 312, 9420),
            );
            $add(
                new Point(5915, 312, 9420),
                new Point(5915, 1477, 9420),
                new Point(5915, 1477, 9037),
                new Point(5915, 312, 9037),
            );
            $add(
                new Point(5915, 312, 9037),
                new Point(5915, 1477, 9037),
                new Point(5772, 1477, 9037),
                new Point(5772, 312, 9037),
            );

            // mountains mud
            $add(
                new Point(7089, 422, 11012),
                new Point(5705, 422, 11012),
                new Point(5750, 666, 11765),
                new Point(7134, 666, 11765),
            );
            $add(
                new Point(5459, 548, 11339),
                new Point(5545, 537, 11801),
                new Point(4247, 769, 11037),
                null,
            );
            $add(
                new Point(5545, 537, 11801),
                new Point(4246, 900, 11900),
                new Point(4247, 769, 11037),
                null,
            );
            $add(
                new Point(4247, 769, 11037),
                new Point(5459, 407, 11037),
                new Point(5459, 548, 11339),
                null,
            );
            $add(
                new Point(5705, 422, 11012),
                new Point(5459, 548, 11339),
                new Point(5459, 407, 11037),
                null,
            );
            $add(
                new Point(5750, 666, 11765),
                new Point(5459, 548, 11339),
                new Point(5705, 422, 11012),
                null,
            );
            $add(
                new Point(5750, 666, 11765),
                new Point(5545, 537, 11801),
                new Point(5459, 548, 11339),
                null,
            );
        }

        // Map - Lower Tunnel
        if (true) {

            // lower tunnel
            $add(
                new Point(4628, 464, 7918),
                new Point(3996, 464, 7918),
                new Point(3996, 654, 7598),
                new Point(4628, 654, 7598),
            );
            $add(
                new Point(4628, 654, 7598),
                new Point(3996, 654, 7598),
                new Point(3996, 654, 7132),
                new Point(4628, 654, 7132),
            );
            $add(
                new Point(4185, 756, 7214),
                new Point(4185, 756, 7614),
                new Point(3858, 894, 7614),
                new Point(3858, 894, 7214),
            );
            $add(
                new Point(4044, 423, 8077),
                new Point(4044, 423, 8749),
                new Point(4044, 947, 8749),
                new Point(4044, 947, 8077),
            );
            $add(
                new Point(4044, 423, 8077),
                new Point(4044, 947, 8077),
                new Point(4238, 947, 8077),
                new Point(4238, 423, 8077),
            );
            $add(
                new Point(4238, 423, 8077),
                new Point(4238, 947, 8077),
                new Point(4238, 947, 7598),
                new Point(4238, 423, 7598),
            );
            $add(
                new Point(4238, 947, 7598),
                new Point(4238, 947, 8077),
                new Point(3879, 947, 8077),
                new Point(3879, 947, 7598),
            );
            $add(
                new Point(3879, 947, 7598),
                new Point(3879, 947, 8077),
                new Point(3879, 857, 8077),
                new Point(3879, 857, 7598),
            );
            $add(
                new Point(4238, 947, 7598),
                new Point(3879, 947, 7598),
                new Point(3879, 648, 7598),
                new Point(4238, 648, 7598),
            );
            $add(
                new Point(4044, 947, 8749),
                new Point(4044, 423, 8749),
                new Point(6059, 423, 8749),
                new Point(6059, 947, 8749),
            );
            $add(
                new Point(6059, 947, 8749),
                new Point(6059, 423, 8749),
                new Point(6059, 423, 8605),
                new Point(6059, 947, 8605),
            );
            $add(
                new Point(6059, 947, 8605),
                new Point(6059, 423, 8605),
                new Point(6154, 423, 8605),
                new Point(6154, 947, 8605),
            );
            $add(
                new Point(6154, 947, 8605),
                new Point(6154, 423, 8605),
                new Point(6154, 423, 9133),
                new Point(6154, 947, 9133),
            );
            $add(
                new Point(6154, 947, 9133),
                new Point(6154, 423, 9133),
                new Point(5906, 423, 9133),
                new Point(5906, 947, 9133),
            );
            $add(
                new Point(6059, 439, 8077),
                new Point(4619, 439, 8077),
                new Point(4619, 1020, 8077),
                new Point(6059, 1020, 8077),
            );
            $add(
                new Point(6059, 439, 8077),
                new Point(6059, 1020, 8077),
                new Point(6059, 1020, 8221),
                new Point(6059, 439, 8221),
            );
            $add(
                new Point(6059, 439, 8221),
                new Point(6059, 1020, 8221),
                new Point(6154, 1020, 8221),
                new Point(6154, 439, 8221),
            );
            $add(
                new Point(4185, 756, 7614),
                new Point(4185, 756, 7214),
                new Point(4238, 649, 7214),
                new Point(4238, 649, 7614),
            );
            $add(
                new Point(4246, 620, 7650),
                new Point(4147, 774, 7077),
                new Point(4681, 656, 7576),
                null,
            );

            // lower tunnel floor
            $add(
                new Point(4628, 472, 7753),
                new Point(4628, 472, 8871),
                new Point(3996, 472, 8871),
                new Point(3996, 472, 7753),
            );
            $add(
                new Point(6058, 472, 8063),
                new Point(6058, 472, 8858),
                new Point(4628, 472, 8858),
                new Point(4628, 472, 8063),
            );
            $add(
                new Point(6058, 472, 8858),
                new Point(6058, 472, 8063),
                new Point(6058, 387, 8066),
                new Point(6058, 387, 8858),
            );

            // lower tunnel stairs wall
            $add(
                new Point(4619, 1697, 8077),
                new Point(4619, 406, 8077),
                new Point(4619, 406, 7645),
                new Point(4619, 1697, 7645),
            );
            $add(
                new Point(4331, 1697, 7262),
                new Point(4331, 406, 7262),
                new Point(4188, 406, 7214),
                new Point(4188, 1697, 7214),
            );
            $add(
                new Point(4475, 1697, 7357),
                new Point(4475, 406, 7357),
                new Point(4331, 406, 7262),
                new Point(4331, 1697, 7262),
            );
            $add(
                new Point(4571, 1697, 7501),
                new Point(4571, 406, 7501),
                new Point(4475, 406, 7357),
                new Point(4475, 1697, 7357),
            );
            $add(
                new Point(4619, 406, 7645),
                new Point(4571, 406, 7501),
                new Point(4571, 1697, 7501),
                new Point(4619, 1697, 7645),
            );
            $add(
                new Point(4188, 1697, 7214),
                new Point(4188, 406, 7214),
                new Point(3827, 406, 7214),
                new Point(3827, 1697, 7214),
            );

            // lower tunnel to mid
            $add(
                new Point(6162, 464, 7501),
                new Point(6162, 1144, 7501),
                new Point(6059, 1144, 7501),
                new Point(6059, 464, 7501),
            );
            $add(
                new Point(6059, 464, 7501),
                new Point(6059, 1353, 7501),
                new Point(6059, 1353, 6767),
                new Point(6059, 464, 6767),
            );
            $add(
                new Point(6154, 464, 8240),
                new Point(6154, 913, 8240),
                new Point(6154, 913, 7501),
                new Point(6154, 464, 7501),
            );
            $add(
                new Point(6154, 913, 7501),
                new Point(6154, 913, 8240),
                new Point(6154, 1144, 8240),
                new Point(6154, 1144, 7501),
            );
            $add(
                new Point(6154, 1144, 8240),
                new Point(6154, 913, 8240),
                new Point(6154, 913, 9136),
                new Point(6154, 1144, 9136),
            );
            $add(
                new Point(6162, 1144, 8941),
                new Point(6749, 1144, 8941),
                new Point(6749, 1144, 9133),
                new Point(6162, 1144, 9133),
            );
            $add(
                new Point(6154, 1144, 8240),
                new Point(6154, 1144, 9136),
                new Point(5824, 1144, 9136),
                new Point(5824, 1144, 8240),
            );
            $add(
                new Point(6154, 1144, 7501),
                new Point(6154, 1144, 8240),
                new Point(5824, 1144, 8240),
                new Point(5824, 1144, 7501),
            );
            $add(
                new Point(5824, 1144, 8240),
                new Point(5824, 1144, 9136),
                new Point(5824, 1574, 9136),
                new Point(5824, 1574, 8240),
            );
            $add(
                new Point(5824, 1144, 7501),
                new Point(5824, 1144, 8240),
                new Point(5824, 1574, 8240),
                new Point(5824, 1574, 7501),
            );
            $add(
                new Point(6162, 1144, 8941),
                new Point(6162, 962, 8941),
                new Point(6749, 962, 8941),
                new Point(6749, 1144, 8941),
            );
            $add(
                new Point(6162, 1144, 9133),
                new Point(6749, 1144, 9133),
                new Point(6749, 946, 9133),
                new Point(6162, 946, 9133),
            );
            $add(
                new Point(6162, 1144, 9133),
                new Point(6162, 946, 9133),
                new Point(5865, 946, 9133),
                new Point(5865, 1144, 9133),
            );
            $add(
                new Point(5824, 1144, 7501),
                new Point(5824, 1574, 7501),
                new Point(5824, 1574, 6718),
                new Point(5824, 1144, 6718),
            );
            $add(
                new Point(6059, 1353, 7501),
                new Point(6059, 464, 7501),
                new Point(5824, 464, 7501),
                new Point(5824, 1353, 7501),
            );

            // lower tunnel to upper
            $add(
                new Point(3887, 947, 7597),
                new Point(3887, 947, 7802),
                new Point(3813, 898, 7802),
                new Point(3813, 898, 7597),
            );
        }

        // Map - Mid
        if (true) {

            // mid main floor
            $add(
                new Point(6983, 444, 9039),
                new Point(6011, 444, 9039),
                new Point(6011, 444, 7796),
                new Point(6983, 444, 7796),
            );
            $add(
                new Point(6950, 813, 6447),
                new Point(6950, 444, 7937),
                new Point(6027, 444, 7937),
                new Point(6027, 813, 6447),
            );
            $add(
                new Point(6011, 444, 9039),
                new Point(6983, 444, 9039),
                new Point(6983, 391, 9039),
                new Point(6011, 391, 9039),
            );

            // mid walls
            $add(
                new Point(9247, 415, 10284),
                new Point(9247, 995, 10284),
                new Point(7978, 995, 10284),
                new Point(7978, 415, 10284),
            );
            $add(
                new Point(7978, 415, 10284),
                new Point(7978, 995, 10284),
                new Point(7690, 995, 10092),
                new Point(7690, 415, 10092),
            );
            $add(
                new Point(7690, 415, 10092),
                new Point(7690, 995, 10092),
                new Point(6917, 995, 10092),
                new Point(6917, 415, 10092),
            );
            $add(
                new Point(6730, 415, 9900),
                new Point(6730, 1866, 9900),
                new Point(6730, 1866, 9708),
                new Point(6730, 415, 9708),
            );
            $add(
                new Point(6917, 415, 10105),
                new Point(6917, 1866, 10105),
                new Point(6730, 1866, 9900),
                new Point(6730, 415, 9900),
            );
            $add(
                new Point(6730, 415, 9708),
                new Point(6730, 1866, 9708),
                new Point(6922, 1866, 9516),
                new Point(6922, 415, 9516),
            );
            $add(
                new Point(6922, 415, 9516),
                new Point(6922, 1866, 9516),
                new Point(6922, 1866, 9318),
                new Point(6922, 415, 9318),
            );
            $add(
                new Point(6922, 415, 9318),
                new Point(6922, 1866, 9318),
                new Point(6730, 1866, 9133),
                new Point(6730, 415, 9133),
            );
            $add(
                new Point(6730, 415, 9133),
                new Point(6730, 1866, 9133),
                new Point(6730, 1866, 8941),
                new Point(6730, 415, 8941),
            );
            $add(
                new Point(6730, 415, 8941),
                new Point(6730, 1866, 8941),
                new Point(6922, 1866, 8749),
                new Point(6922, 415, 8749),
            );
            $add(
                new Point(6922, 440, 8771),
                new Point(6922, 899, 8771),
                new Point(6922, 899, 6445),
                new Point(6922, 440, 6445),
            );
            $add(
                new Point(6922, 899, 6445),
                new Point(6922, 899, 8771),
                new Point(6970, 899, 8771),
                new Point(6970, 899, 6445),
            );
            $add(
                new Point(6922, 440, 6445),
                new Point(6922, 899, 6445),
                new Point(6970, 899, 6445),
                new Point(6970, 440, 6445),
            );
            $add(
                new Point(6970, 899, 6445),
                new Point(6970, 899, 8771),
                new Point(6970, 799, 8771),
                new Point(6970, 799, 6445),
            );
            $add(
                new Point(6922, 415, 8749),
                new Point(6922, 1866, 8749),
                new Point(6933, 1866, 8755),
                new Point(6933, 415, 8755),
            );

            // short to top mid walls
            $add(
                new Point(9225, 769, 8173),
                new Point(9225, 769, 10284),
                new Point(9225, 1775, 10284),
                new Point(9225, 1775, 8173),
            );
            $add(
                new Point(9225, 769, 8173),
                new Point(9225, 1775, 8173),
                new Point(7594, 1775, 8173),
                new Point(7594, 769, 8173),
            );
            $add(
                new Point(7594, 769, 8173),
                new Point(7594, 1775, 8173),
                new Point(7306, 1775, 7885),
                new Point(7306, 769, 7885),
            );
            $add(
                new Point(7306, 769, 7885),
                new Point(7306, 1775, 7885),
                new Point(7306, 1775, 5870),
                new Point(7306, 769, 5870),
            );
            $add(
                new Point(7306, 769, 5870),
                new Point(7306, 1775, 5870),
                new Point(7594, 1775, 5582),
                new Point(7594, 769, 5582),
            );
            $add(
                new Point(7594, 769, 5582),
                new Point(7594, 1775, 5582),
                new Point(8841, 1775, 5582),
                new Point(8841, 769, 5582),
            );
            $add(
                new Point(8841, 769, 5582),
                new Point(8841, 1775, 5582),
                new Point(8841, 1775, 5103),
                new Point(8841, 769, 5103),
            );
            $add(
                new Point(8841, 769, 5103),
                new Point(8841, 1775, 5103),
                new Point(9033, 1775, 4911),
                new Point(9033, 769, 4911),
            );
            $add(
                new Point(9033, 769, 4911),
                new Point(9033, 1775, 4911),
                new Point(9321, 1775, 4911),
                new Point(9321, 769, 4911),
            );
            $add(
                new Point(9321, 769, 4911),
                new Point(9321, 1775, 4911),
                new Point(9321, 1775, 5019),
                new Point(9321, 769, 5019),
            );

            // top mid main floor
            $add(
                new Point(5481, 824, 2262),
                new Point(10025, 824, 2262),
                new Point(10025, 824, 6403),
                new Point(5481, 824, 6403),
            );
            $add(
                new Point(5481, 824, 6403),
                new Point(10025, 824, 6403),
                new Point(10025, 804, 6501),
                new Point(5481, 804, 6501),
            );

            // top mid walls
            $add(
                new Point(6154, 726, 2224),
                new Point(6154, 726, 4719),
                new Point(6154, 1778, 4719),
                new Point(6154, 1778, 2224),
            );
            $add(
                new Point(6154, 1778, 4719),
                new Point(6154, 726, 4719),
                new Point(5771, 726, 4719),
                new Point(5771, 1778, 4719),
            );
            $add(
                new Point(5771, 1778, 4719),
                new Point(5771, 726, 4719),
                new Point(5579, 726, 4911),
                new Point(5579, 1778, 4911),
            );
            $add(
                new Point(5579, 1778, 4911),
                new Point(5579, 726, 4911),
                new Point(5510, 726, 4911),
                new Point(5510, 1778, 4911),
            );
            $add(
                new Point(5510, 1778, 5678),
                new Point(5510, 726, 5678),
                new Point(5579, 726, 5678),
                new Point(5579, 1778, 5678),
            );
            $add(
                new Point(5579, 1778, 5678),
                new Point(5579, 726, 5678),
                new Point(5771, 726, 5870),
                new Point(5771, 1778, 5870),
            );
            $add(
                new Point(5771, 1778, 5870),
                new Point(5771, 726, 5870),
                new Point(5771, 726, 6062),
                new Point(5771, 1778, 6062),
            );
            $add(
                new Point(5771, 1778, 6062),
                new Point(5771, 726, 6062),
                new Point(6059, 726, 6062),
                new Point(6059, 1778, 6062),
            );
            $add(
                new Point(6059, 1778, 6062),
                new Point(6059, 726, 6062),
                new Point(6059, 726, 6302),
                new Point(6059, 1778, 6302),
            );
            $add(
                new Point(6059, 1778, 6302),
                new Point(6059, 726, 6302),
                new Point(6154, 726, 6302),
                new Point(6154, 1778, 6302),
            );
            $add(
                new Point(6154, 1778, 6302),
                new Point(6154, 726, 6302),
                new Point(6154, 726, 6782),
                new Point(6154, 1778, 6782),
            );
            $add(
                new Point(6154, 1778, 6782),
                new Point(6154, 726, 6782),
                new Point(5635, 726, 6782),
                new Point(5635, 1778, 6782),
            );
            $add(
                new Point(5510, 1778, 4911),
                new Point(5510, 726, 4911),
                new Point(5510, 726, 5678),
                new Point(5510, 1778, 5678),
            );

            // top mid walls.001
            $add(
                new Point(8073, 786, 2992),
                new Point(8073, 786, 4719),
                new Point(8073, 1620, 4719),
                new Point(8073, 1620, 2992),
            );
            $add(
                new Point(8073, 1620, 4719),
                new Point(8073, 786, 4719),
                new Point(7786, 786, 5007),
                new Point(7786, 1620, 5007),
            );
            $add(
                new Point(7786, 1620, 5007),
                new Point(7786, 786, 5007),
                new Point(6820, 786, 5007),
                new Point(6820, 1620, 5007),
            );
            $add(
                new Point(6820, 1620, 5007),
                new Point(6820, 786, 5007),
                new Point(6538, 786, 4725),
                new Point(6538, 1620, 4725),
            );
            $add(
                new Point(6538, 1620, 2992),
                new Point(6538, 786, 2992),
                new Point(6826, 786, 2704),
                new Point(6826, 1620, 2704),
            );
            $add(
                new Point(6826, 1620, 2704),
                new Point(6826, 786, 2704),
                new Point(7786, 786, 2704),
                new Point(7786, 1620, 2704),
            );
            $add(
                new Point(7786, 1620, 2704),
                new Point(7786, 786, 2704),
                new Point(8073, 786, 2992),
                new Point(8073, 1620, 2992),
            );
            $add(
                new Point(6538, 1620, 2992),
                new Point(6538, 786, 2992),
                new Point(6538, 786, 4725),
                new Point(6538, 1620, 4725),
            );
            $add(
                new Point(6538, 1620, 4725),
                new Point(6538, 1517, 4725),
                new Point(6154, 1517, 4725),
                new Point(6154, 1620, 4725),
            );
            $add(
                new Point(6538, 1517, 2992),
                new Point(6538, 1620, 2992),
                new Point(6154, 1620, 2992),
                new Point(6154, 1517, 2992),
            );

            // xBox
            $add(
                new Point(6620, 581, 8508),
                new Point(6620, 581, 8554),
                new Point(6693, 581, 8554),
                new Point(6693, 581, 8508),
            );
            $add(
                new Point(6634, 720, 8269),
                new Point(6634, 720, 8557),
                new Point(6927, 720, 8557),
                new Point(6927, 720, 8269),
            );
            $add(
                new Point(6646, 581, 8253),
                new Point(6646, 581, 8300),
                new Point(6930, 581, 8300),
                new Point(6930, 581, 8253),
            );
            $add(
                new Point(6634, 720, 8269),
                new Point(6927, 720, 8269),
                new Point(6927, 425, 8269),
                new Point(6634, 425, 8269),
            );
            $add(
                new Point(6634, 720, 8557),
                new Point(6634, 720, 8269),
                new Point(6634, 425, 8269),
                new Point(6634, 425, 8557),
            );
            $add(
                new Point(6927, 720, 8557),
                new Point(6634, 720, 8557),
                new Point(6634, 425, 8557),
                new Point(6927, 425, 8557),
            );
            $add(
                new Point(6927, 720, 8269),
                new Point(6927, 720, 8557),
                new Point(6927, 425, 8557),
                new Point(6927, 425, 8269),
            );
        }

        // Map - Outside Tunnels
        if (true) {

            // outside tunnels bumps
            $add(
                new Point(2335, 899, 5787),
                new Point(2335, 899, 5678),
                new Point(1464, 899, 5678),
                new Point(1464, 899, 5787),
            );
            $add(
                new Point(3774, 899, 5787),
                new Point(3774, 899, 5678),
                new Point(3062, 899, 5678),
                new Point(3062, 899, 5787),
            );
            $add(
                new Point(3062, 899, 5787),
                new Point(3062, 899, 5678),
                new Point(3062, 799, 5678),
                new Point(3062, 799, 5787),
            );
            $add(
                new Point(1464, 899, 5678),
                new Point(2335, 899, 5678),
                new Point(2335, 799, 5678),
                new Point(1464, 799, 5678),
            );
            $add(
                new Point(2335, 899, 5678),
                new Point(2335, 899, 5787),
                new Point(2335, 799, 5787),
                new Point(2335, 799, 5678),
            );
            $add(
                new Point(3062, 899, 5678),
                new Point(3774, 899, 5678),
                new Point(3774, 799, 5678),
                new Point(3062, 799, 5678),
            );
            $add(
                new Point(3774, 899, 5787),
                new Point(3062, 899, 5787),
                new Point(3062, 799, 5787),
                new Point(3774, 799, 5787),
            );

            // outside tunnels walls
            $add(
                new Point(1477, 794, 5966),
                new Point(1477, 1691, 5966),
                new Point(1477, 1691, 5007),
                new Point(1477, 794, 5007),
            );
            $add(
                new Point(1477, 1691, 5966),
                new Point(1477, 794, 5966),
                new Point(1741, 794, 5966),
                new Point(1741, 1691, 5966),
            );
            $add(
                new Point(1741, 1691, 5966),
                new Point(1741, 794, 5966),
                new Point(1933, 794, 6158),
                new Point(1933, 1691, 6158),
            );
            $add(
                new Point(1933, 1691, 6158),
                new Point(1933, 794, 6158),
                new Point(1933, 794, 6265),
                new Point(1933, 1691, 6265),
            );
            $add(
                new Point(1477, 794, 5007),
                new Point(1477, 1691, 5007),
                new Point(1597, 1691, 5007),
                new Point(1597, 794, 5007),
            );
            $add(
                new Point(1597, 794, 5007),
                new Point(1597, 1691, 5007),
                new Point(1597, 1691, 4503),
                new Point(1597, 794, 4503),
            );
            $add(
                new Point(1597, 794, 4503),
                new Point(1597, 1691, 4503),
                new Point(1549, 1691, 4503),
                new Point(1549, 794, 4503),
            );
            $add(
                new Point(1549, 794, 4503),
                new Point(1549, 1691, 4503),
                new Point(1549, 1691, 3759),
                new Point(1549, 794, 3759),
            );
            $add(
                new Point(1549, 794, 3759),
                new Point(1549, 1691, 3759),
                new Point(1453, 1691, 3759),
                new Point(1453, 794, 3759),
            );
            $add(
                new Point(1453, 794, 3759),
                new Point(1453, 1691, 3759),
                new Point(1453, 1691, 2080),
                new Point(1453, 794, 2080),
            );

            // outside tunnels walls.001
            $add(
                new Point(3948, 800, 4527),
                new Point(3948, 800, 5582),
                new Point(3948, 1763, 5582),
                new Point(3948, 1763, 4527),
            );
            $add(
                new Point(3948, 800, 4527),
                new Point(3948, 1763, 4527),
                new Point(3756, 1763, 4527),
                new Point(3756, 800, 4527),
            );
            $add(
                new Point(3756, 800, 4527),
                new Point(3756, 1763, 4527),
                new Point(3756, 1763, 4143),
                new Point(3756, 800, 4143),
            );
            $add(
                new Point(3756, 800, 4143),
                new Point(3756, 1763, 4143),
                new Point(3660, 1763, 4143),
                new Point(3660, 800, 4143),
            );
            $add(
                new Point(3660, 800, 4143),
                new Point(3660, 1763, 4143),
                new Point(3468, 1763, 3951),
                new Point(3468, 800, 3951),
            );
            $add(
                new Point(3468, 800, 3951),
                new Point(3468, 1763, 3951),
                new Point(3468, 1763, 3717),
                new Point(3468, 800, 3717),
            );
            $add(
                new Point(3948, 1763, 5582),
                new Point(3948, 800, 5582),
                new Point(3756, 800, 5582),
                new Point(3756, 1763, 5582),
            );
            $add(
                new Point(3756, 1763, 5582),
                new Point(3756, 800, 5582),
                new Point(3756, 800, 5966),
                new Point(3756, 1763, 5966),
            );
            $add(
                new Point(3756, 1763, 5966),
                new Point(3756, 800, 5966),
                new Point(3660, 800, 5966),
                new Point(3660, 1763, 5966),
            );
            $add(
                new Point(3660, 1763, 5966),
                new Point(3660, 800, 5966),
                new Point(3468, 800, 6158),
                new Point(3468, 1763, 6158),
            );
            $add(
                new Point(3468, 1763, 6158),
                new Point(3468, 800, 6158),
                new Point(3468, 800, 6275),
                new Point(3468, 1763, 6275),
            );
        }

        // Map - Pit
        if (true) {

            // lower pit walls
            $add(
                new Point(12487, 197, 4583),
                new Point(12487, 197, 6606),
                new Point(12487, 1043, 6606),
                new Point(12487, 1043, 4583),
            );
            $add(
                new Point(12487, 1043, 6606),
                new Point(12487, 197, 6606),
                new Point(12583, 197, 6606),
                new Point(12583, 1043, 6606),
            );
            $add(
                new Point(12487, 1043, 4583),
                new Point(12487, 1043, 6606),
                new Point(12583, 1043, 6606),
                new Point(12583, 1043, 4583),
            );
            $add(
                new Point(12583, 1043, 4583),
                new Point(12583, 1043, 6606),
                new Point(12583, 950, 6606),
                new Point(12583, 950, 4583),
            );
            $add(
                new Point(12633, 925, 4623),
                new Point(11444, 925, 4623),
                new Point(11444, 219, 4623),
                new Point(12633, 219, 4623),
            );
            $add(
                new Point(12558, 1043, 5151),
                new Point(11432, 1043, 5151),
                new Point(11432, 800, 5151),
                new Point(12558, 800, 5151),
            );
            $add(
                new Point(11432, 800, 5151),
                new Point(11432, 1043, 5151),
                new Point(11432, 1043, 5091),
                new Point(11432, 800, 5091),
            );
            $add(
                new Point(12583, 1043, 7061),
                new Point(12487, 1043, 7061),
                new Point(12487, 1043, 8365),
                new Point(12583, 1043, 8365),
            );
            $add(
                new Point(12487, 1043, 8365),
                new Point(12487, 1043, 7061),
                new Point(12487, 791, 7061),
                new Point(12487, 791, 8365),
            );
            $add(
                new Point(12583, 1043, 7061),
                new Point(12583, 1043, 8365),
                new Point(12583, 791, 8365),
                new Point(12583, 791, 7061),
            );
            $add(
                new Point(12487, 1043, 7061),
                new Point(12583, 1043, 7061),
                new Point(12583, 791, 7061),
                new Point(12487, 791, 7061),
            );
            $add(
                new Point(11432, 1043, 5151),
                new Point(12558, 1043, 5151),
                new Point(12558, 1043, 5091),
                new Point(11432, 1043, 5091),
            );
            $add(
                new Point(12583, 791, 8365),
                new Point(12583, 1043, 8365),
                new Point(12487, 1043, 8365),
                new Point(12487, 791, 8365),
            );

            // lower pit walls.001
            $add(
                new Point(11432, 797, 4588),
                new Point(11432, 797, 6445),
                new Point(11432, 899, 6446),
                new Point(11432, 899, 4588),
            );
            $add(
                new Point(11432, 899, 4588),
                new Point(11432, 899, 6446),
                new Point(11528, 899, 6446),
                new Point(11528, 899, 4588),
            );
            $add(
                new Point(11528, 899, 6446),
                new Point(11432, 899, 6446),
                new Point(11432, 792, 6446),
                new Point(11528, 792, 6446),
            );
            $add(
                new Point(11528, 899, 4588),
                new Point(11528, 899, 6446),
                new Point(11528, 203, 6445),
                new Point(11528, 203, 4588),
            );

            // pit main floor
            $add(
                new Point(9207, 815, 4697),
                new Point(11509, 815, 4697),
                new Point(11509, 815, 6430),
                new Point(9207, 815, 6430),
            );
            $add(
                new Point(11509, 815, 7887),
                new Point(11509, 815, 6430),
                new Point(12547, 815, 6430),
                new Point(12547, 815, 7887),
            );
            $add(
                new Point(9207, 815, 6430),
                new Point(11509, 815, 6430),
                new Point(11509, 815, 7887),
                new Point(9207, 815, 7887),
            );
            $add(
                new Point(12583, 995, 7422),
                new Point(12583, 995, 4966),
                new Point(13079, 995, 4966),
                new Point(13079, 995, 7422),
            );
            $add(
                new Point(12547, 815, 6430),
                new Point(11509, 815, 6430),
                new Point(11509, 289, 4818),
                new Point(12547, 289, 4818),
            );
            $add(
                new Point(12547, 289, 4818),
                new Point(11509, 289, 4818),
                new Point(11509, 289, 4567),
                new Point(12547, 289, 4567),
            );

            // pit walls
            $add(
                new Point(11481, 1562, 4719),
                new Point(10548, 1562, 4719),
                new Point(10548, 771, 4719),
                new Point(11481, 771, 4719),
            );
            $add(
                new Point(10568, 799, 4628),
                new Point(10568, 799, 6302),
                new Point(10568, 1481, 6302),
                new Point(10568, 1481, 4628),
            );
            $add(
                new Point(10781, 1487, 4935),
                new Point(10781, 786, 4935),
                new Point(10781, 786, 4699),
                new Point(10781, 1487, 4699),
            );
            $add(
                new Point(10781, 1487, 4935),
                new Point(10548, 1487, 4935),
                new Point(10548, 786, 4935),
                new Point(10781, 786, 4935),
            );
            $add(
                new Point(10568, 1481, 6302),
                new Point(10568, 799, 6302),
                new Point(10424, 799, 6446),
                new Point(10424, 1481, 6446),
            );
            $add(
                new Point(12971, 1638, 5103),
                new Point(12295, 1638, 5103),
                new Point(12295, 932, 5103),
                new Point(12971, 932, 5103),
            );
            $add(
                new Point(12511, 754, 7214),
                new Point(12511, 2143, 7214),
                new Point(13099, 2143, 7214),
                new Point(13099, 754, 7214),
            );
            $add(
                new Point(12967, 1764, 5678),
                new Point(12967, 1002, 5678),
                new Point(13076, 1002, 5678),
                new Point(13076, 1764, 5678),
            );
            $add(
                new Point(12967, 1002, 5103),
                new Point(12967, 1002, 5678),
                new Point(12967, 1764, 5678),
                new Point(12967, 1764, 5103),
            );
            $add(
                new Point(13063, 993, 5671),
                new Point(13063, 993, 7226),
                new Point(13063, 1755, 7226),
                new Point(13063, 1755, 5671),
            );
            $add(
                new Point(13063, 1744, 6495),
                new Point(13063, 1744, 7226),
                new Point(13063, 2093, 7226),
                new Point(13063, 2093, 6495),
            );
            $add(
                new Point(11432, 773, 4430),
                new Point(11432, 773, 5091),
                new Point(11432, 1811, 5091),
                new Point(11432, 1811, 4430),
            );
            $add(
                new Point(12511, 754, 7214),
                new Point(12511, 754, 8365),
                new Point(12511, 2143, 8365),
                new Point(12511, 2143, 7214),
            );
            $add(
                new Point(12511, 2143, 8365),
                new Point(12511, 754, 8365),
                new Point(12604, 754, 8365),
                new Point(12604, 2143, 8365),
            );
            $add(
                new Point(10424, 1481, 6446),
                new Point(10424, 799, 6446),
                new Point(9897, 799, 6446),
                new Point(9897, 1481, 6446),
            );
            $add(
                new Point(9897, 1481, 6446),
                new Point(9897, 799, 6446),
                new Point(9897, 799, 6172),
                new Point(9897, 1481, 6172),
            );
            $add(
                new Point(12295, 932, 5103),
                new Point(12295, 1638, 5103),
                new Point(12295, 1638, 5091),
                new Point(12295, 932, 5091),
            );
            $add(
                new Point(12295, 932, 5091),
                new Point(12295, 1811, 5091),
                new Point(11432, 1811, 5091),
                new Point(11432, 932, 5091),
            );
        }

        // Map - Props
        if (true) {

            // blue box
            $add(
                new Point(9992, 1187, 7405),
                new Point(10376, 1187, 7405),
                new Point(10376, 801, 7405),
                new Point(9992, 801, 7405),
            );
            $add(
                new Point(10376, 1187, 7405),
                new Point(10376, 1187, 7794),
                new Point(10376, 801, 7794),
                new Point(10376, 801, 7405),
            );
            $add(
                new Point(9992, 1187, 7794),
                new Point(9992, 1187, 7405),
                new Point(9992, 801, 7405),
                new Point(9992, 801, 7794),
            );
            $add(
                new Point(10376, 1187, 7794),
                new Point(10376, 1187, 7405),
                new Point(9992, 1187, 7405),
                new Point(9992, 1187, 7794),
            );
            $add(
                new Point(10590, 975, 7799),
                new Point(10590, 975, 7581),
                new Point(10374, 975, 7581),
                new Point(10374, 975, 7799),
            );
            $add(
                new Point(10374, 975, 7581),
                new Point(10590, 975, 7581),
                new Point(10590, 801, 7581),
                new Point(10374, 801, 7581),
            );
            $add(
                new Point(10590, 975, 7581),
                new Point(10590, 975, 7799),
                new Point(10590, 801, 7799),
                new Point(10590, 801, 7581),
            );

            // box
            $add(
                new Point(13068, 1187, 7022),
                new Point(12871, 1187, 7022),
                new Point(12871, 1187, 7215),
                new Point(13068, 1187, 7215),
            );
            $add(
                new Point(12871, 1187, 7215),
                new Point(12871, 1187, 7022),
                new Point(12871, 970, 7022),
                new Point(12871, 970, 7215),
            );
            $add(
                new Point(12871, 1187, 7022),
                new Point(13068, 1187, 7022),
                new Point(13068, 970, 7022),
                new Point(12871, 970, 7022),
            );

            // box.001
            $add(
                new Point(9529, 923, 11352),
                new Point(9529, 923, 11129),
                new Point(9315, 923, 11129),
                new Point(9315, 923, 11352),
            );
            $add(
                new Point(9315, 923, 11129),
                new Point(9529, 923, 11129),
                new Point(9529, 713, 11129),
                new Point(9315, 713, 11129),
            );
            $add(
                new Point(9529, 923, 11129),
                new Point(9529, 923, 11352),
                new Point(9529, 713, 11352),
                new Point(9529, 713, 11129),
            );

            // box.002
            $add(
                new Point(10903, 1284, 11629),
                new Point(10903, 1284, 11435),
                new Point(10711, 1284, 11435),
                new Point(10711, 1284, 11629),
            );
            $add(
                new Point(10711, 1284, 11435),
                new Point(10903, 1284, 11435),
                new Point(10903, 1068, 11435),
                new Point(10711, 1068, 11435),
            );
            $add(
                new Point(10903, 1284, 11435),
                new Point(10903, 1284, 11629),
                new Point(10903, 1068, 11629),
                new Point(10903, 1068, 11435),
            );
            $add(
                new Point(10711, 1284, 11435),
                new Point(10711, 1068, 11435),
                new Point(10712, 1068, 11679),
                new Point(10712, 1284, 11679),
            );
            $add(
                new Point(10712, 1284, 11679),
                new Point(10712, 1068, 11679),
                new Point(10764, 1068, 11867),
                new Point(10764, 1284, 11867),
            );
            $add(
                new Point(10764, 1284, 11867),
                new Point(10764, 1068, 11867),
                new Point(10953, 1068, 11815),
                new Point(10953, 1284, 11815),
            );
            $add(
                new Point(10953, 1284, 11815),
                new Point(10953, 1068, 11815),
                new Point(10903, 1068, 11629),
                new Point(10903, 1284, 11629),
            );
            $add(
                new Point(10913, 1284, 11678),
                new Point(10753, 1284, 11678),
                new Point(10753, 1284, 11823),
                new Point(10913, 1284, 11823),
            );

            // box.003
            $add(
                new Point(9897, 804, 11399),
                new Point(9608, 804, 11399),
                new Point(9608, 804, 11052),
                new Point(9897, 804, 11052),
            );
            $add(
                new Point(9897, 804, 11052),
                new Point(9608, 804, 11052),
                new Point(9608, 497, 11052),
                new Point(9897, 497, 11052),
            );
            $add(
                new Point(9608, 804, 11052),
                new Point(9608, 804, 11399),
                new Point(9608, 497, 11399),
                new Point(9608, 497, 11052),
            );
            $add(
                new Point(9897, 804, 11399),
                new Point(9897, 804, 11052),
                new Point(9897, 497, 11052),
                new Point(9897, 497, 11399),
            );

            // box.004
            $add(
                new Point(11480, 1283, 11771),
                new Point(11288, 1283, 11771),
                new Point(11288, 1283, 11579),
                new Point(11480, 1283, 11579),
            );
            $add(
                new Point(11480, 1283, 11579),
                new Point(11288, 1283, 11579),
                new Point(11288, 1084, 11579),
                new Point(11480, 1084, 11579),
            );
            $add(
                new Point(11480, 1283, 11771),
                new Point(11480, 1283, 11579),
                new Point(11480, 1084, 11579),
                new Point(11480, 1084, 11771),
            );
            $add(
                new Point(11288, 1283, 11771),
                new Point(11480, 1283, 11771),
                new Point(11480, 1084, 11771),
                new Point(11288, 1084, 11771),
            );
            $add(
                new Point(11288, 1283, 11579),
                new Point(11288, 1283, 11771),
                new Point(11288, 1084, 11771),
                new Point(11288, 1084, 11579),
            );

            // box.005
            $add(
                new Point(7258, 805, 10284),
                new Point(7258, 394, 10284),
                new Point(7258, 394, 10072),
                new Point(7258, 805, 10072),
            );
            $add(
                new Point(7450, 805, 10284),
                new Point(7450, 394, 10284),
                new Point(7258, 394, 10284),
                new Point(7258, 805, 10284),
            );
            $add(
                new Point(7450, 805, 10284),
                new Point(7258, 805, 10284),
                new Point(7258, 805, 10072),
                new Point(7450, 805, 10072),
            );
            $add(
                new Point(7450, 394, 10284),
                new Point(7450, 805, 10284),
                new Point(7450, 805, 10072),
                new Point(7450, 394, 10072),
            );

            // box.006
            $add(
                new Point(7018, 899, 10860),
                new Point(7018, 899, 11061),
                new Point(7210, 899, 11061),
                new Point(7210, 899, 10860),
            );
            $add(
                new Point(7210, 899, 10860),
                new Point(7210, 899, 11061),
                new Point(7210, 705, 11061),
                new Point(7210, 705, 10860),
            );
            $add(
                new Point(7018, 899, 10860),
                new Point(7210, 899, 10860),
                new Point(7210, 705, 10860),
                new Point(7018, 705, 10860),
            );
            $add(
                new Point(7018, 899, 11061),
                new Point(7018, 899, 10860),
                new Point(7018, 705, 10860),
                new Point(7018, 705, 11061),
            );

            // box.007
            $add(
                new Point(7018, 708, 10764),
                new Point(7306, 708, 10764),
                new Point(7306, 413, 10764),
                new Point(7018, 413, 10764),
            );
            $add(
                new Point(7306, 708, 10764),
                new Point(7306, 708, 11070),
                new Point(7306, 413, 11070),
                new Point(7306, 413, 10764),
            );
            $add(
                new Point(7018, 708, 11070),
                new Point(7018, 708, 10764),
                new Point(7018, 413, 10764),
                new Point(7018, 413, 11070),
            );
            $add(
                new Point(7018, 708, 10764),
                new Point(7018, 708, 11070),
                new Point(7306, 708, 11070),
                new Point(7306, 708, 10764),
            );

            // box.008
            $add(
                new Point(5293, 852, 8295),
                new Point(5090, 852, 8295),
                new Point(5090, 852, 8077),
                new Point(5293, 852, 8077),
            );
            $add(
                new Point(5293, 852, 8295),
                new Point(5293, 852, 8077),
                new Point(5293, 462, 8077),
                new Point(5293, 462, 8295),
            );
            $add(
                new Point(5090, 852, 8295),
                new Point(5293, 852, 8295),
                new Point(5293, 462, 8295),
                new Point(5090, 462, 8295),
            );
            $add(
                new Point(5090, 852, 8077),
                new Point(5090, 852, 8295),
                new Point(5090, 462, 8295),
                new Point(5090, 462, 8077),
            );

            // box.009
            $add(
                new Point(2125, 1187, 9708),
                new Point(2125, 1187, 9507),
                new Point(2125, 801, 9507),
                new Point(2125, 801, 9708),
            );
            $add(
                new Point(2317, 1187, 9708),
                new Point(2317, 1187, 9507),
                new Point(2125, 1187, 9507),
                new Point(2125, 1187, 9708),
            );
            $add(
                new Point(2317, 1187, 9708),
                new Point(2125, 1187, 9708),
                new Point(2125, 801, 9708),
                new Point(2317, 801, 9708),
            );
            $add(
                new Point(2317, 1187, 9507),
                new Point(2317, 1187, 9708),
                new Point(2317, 801, 9708),
                new Point(2317, 801, 9507),
            );

            // box.010
            $add(
                new Point(1634, 1073, 12610),
                new Point(1634, 1073, 12824),
                new Point(1420, 1073, 12824),
                new Point(1420, 1073, 12610),
            );
            $add(
                new Point(1420, 1073, 12824),
                new Point(1634, 1073, 12824),
                new Point(1634, 897, 12824),
                new Point(1420, 897, 12824),
            );
            $add(
                new Point(1634, 1073, 12824),
                new Point(1634, 1073, 12610),
                new Point(1634, 897, 12610),
                new Point(1634, 897, 12824),
            );
            $add(
                new Point(1420, 1073, 12610),
                new Point(1420, 1073, 12824),
                new Point(1420, 897, 12824),
                new Point(1420, 897, 12610),
            );
            $add(
                new Point(1634, 1073, 12610),
                new Point(1420, 1073, 12610),
                new Point(1420, 897, 12610),
                new Point(1634, 897, 12610),
            );

            // box.011
            $add(
                new Point(1583, 1073, 11501),
                new Point(1583, 1073, 11715),
                new Point(1362, 1073, 11715),
                new Point(1362, 1073, 11501),
            );
            $add(
                new Point(1362, 1073, 11715),
                new Point(1583, 1073, 11715),
                new Point(1583, 895, 11715),
                new Point(1362, 895, 11715),
            );
            $add(
                new Point(1583, 1073, 11715),
                new Point(1583, 1073, 11501),
                new Point(1583, 895, 11501),
                new Point(1583, 895, 11715),
            );
            $add(
                new Point(1583, 1073, 11501),
                new Point(1362, 1073, 11501),
                new Point(1362, 895, 11501),
                new Point(1583, 895, 11501),
            );

            // box.012
            $add(
                new Point(4518, 641, 8749),
                new Point(4044, 641, 8749),
                new Point(4044, 641, 8506),
                new Point(4518, 641, 8506),
            );
            $add(
                new Point(4518, 641, 8749),
                new Point(4518, 641, 8506),
                new Point(4518, 461, 8506),
                new Point(4518, 461, 8749),
            );
            $add(
                new Point(4518, 641, 8506),
                new Point(4044, 641, 8506),
                new Point(4044, 461, 8506),
                new Point(4518, 461, 8506),
            );

            // door
            $add(
                new Point(6737, 965, 9057),
                new Point(6475, 965, 9171),
                new Point(6475, 431, 9171),
                new Point(6737, 431, 9057),
            );
            $add(
                new Point(6731, 962, 9021),
                new Point(6468, 962, 9145),
                new Point(6468, 431, 9145),
                new Point(6731, 431, 9021),
            );

            // door.001
            $add(
                new Point(6409, 962, 8904),
                new Point(6147, 962, 9025),
                new Point(6147, 431, 9025),
                new Point(6409, 431, 8904),
            );
            $add(
                new Point(6419, 965, 8927),
                new Point(6154, 965, 9052),
                new Point(6154, 431, 9052),
                new Point(6419, 431, 8927),
            );

            // door.002
            $add(
                new Point(9575, 1299, 6455),
                new Point(9312, 1299, 6330),
                new Point(9312, 808, 6330),
                new Point(9575, 808, 6455),
            );
            $add(
                new Point(9575, 1299, 6483),
                new Point(9312, 1299, 6359),
                new Point(9312, 807, 6359),
                new Point(9575, 807, 6483),
            );

            // door.003
            $add(
                new Point(9900, 1299, 6364),
                new Point(9637, 1299, 6243),
                new Point(9637, 808, 6243),
                new Point(9900, 808, 6364),
            );
            $add(
                new Point(9900, 1299, 6336),
                new Point(9642, 1299, 6216),
                new Point(9642, 804, 6216),
                new Point(9900, 804, 6336),
            );

            // door.004
            $add(
                new Point(9900, 1354, 4993),
                new Point(9643, 1354, 4873),
                new Point(9643, 822, 4873),
                new Point(9900, 822, 4993),
            );
            $add(
                new Point(9898, 1356, 5019),
                new Point(9632, 1356, 4896),
                new Point(9632, 822, 4896),
                new Point(9898, 822, 5019),
            );

            // door.005
            $add(
                new Point(3866, 1343, 10788),
                new Point(3742, 1343, 11052),
                new Point(3742, 811, 11052),
                new Point(3866, 811, 10788),
            );
            $add(
                new Point(3889, 1345, 10797),
                new Point(3768, 1345, 11056),
                new Point(3768, 811, 11056),
                new Point(3889, 811, 10797),
            );

            // door.006
            $add(
                new Point(3769, 1345, 10474),
                new Point(3645, 1345, 10741),
                new Point(3645, 811, 10741),
                new Point(3769, 811, 10474),
            );
            $add(
                new Point(3741, 1343, 10464),
                new Point(3623, 1343, 10730),
                new Point(3623, 811, 10730),
                new Point(3741, 811, 10464),
            );

            // door.007
            $add(
                new Point(9578, 1356, 5141),
                new Point(9321, 1356, 5023),
                new Point(9321, 822, 5023),
                new Point(9578, 822, 5141),
            );
            $add(
                new Point(9586, 1354, 5117),
                new Point(9319, 1354, 4992),
                new Point(9319, 810, 4992),
                new Point(9586, 810, 5117),
            );

            // magic door
            $add(
                new Point(10846, 808, 7754),
                new Point(10846, 1248, 7754),
                new Point(10876, 1248, 7754),
                new Point(10876, 808, 7754),
            );
            $add(
                new Point(10846, 972, 7712),
                new Point(10846, 972, 7754),
                new Point(10876, 972, 7754),
                new Point(10876, 972, 7712),
            );
            $add(
                new Point(10876, 972, 7754),
                new Point(10876, 808, 7754),
                new Point(10876, 808, 7712),
                new Point(10876, 972, 7712),
            );
            $add(
                new Point(10876, 876, 7712),
                new Point(10876, 808, 7712),
                new Point(10876, 808, 7653),
                new Point(10876, 876, 7653),
            );
            $add(
                new Point(11107, 972, 7754),
                new Point(11107, 972, 7712),
                new Point(11073, 972, 7712),
                new Point(11073, 972, 7754),
            );
            $add(
                new Point(10846, 808, 7653),
                new Point(10846, 876, 7653),
                new Point(10876, 876, 7653),
                new Point(10876, 808, 7653),
            );
            $add(
                new Point(10876, 1248, 7799),
                new Point(10876, 808, 7799),
                new Point(10876, 808, 7754),
                new Point(10876, 1248, 7754),
            );
            $add(
                new Point(11107, 876, 7653),
                new Point(11107, 808, 7653),
                new Point(11073, 808, 7653),
                new Point(11073, 876, 7653),
            );
            $add(
                new Point(10846, 876, 7653),
                new Point(10846, 876, 7712),
                new Point(10876, 876, 7712),
                new Point(10876, 876, 7653),
            );
            $add(
                new Point(10846, 808, 7712),
                new Point(10846, 972, 7712),
                new Point(10876, 972, 7712),
                new Point(10876, 808, 7712),
            );
            $add(
                new Point(11073, 1248, 7799),
                new Point(11073, 1248, 7754),
                new Point(11073, 808, 7754),
                new Point(11073, 808, 7799),
            );
            $add(
                new Point(11073, 808, 7754),
                new Point(11073, 972, 7754),
                new Point(11073, 972, 7712),
                new Point(11073, 808, 7712),
            );
            $add(
                new Point(11073, 808, 7712),
                new Point(11073, 876, 7712),
                new Point(11073, 876, 7653),
                new Point(11073, 808, 7653),
            );
            $add(
                new Point(11107, 972, 7712),
                new Point(11107, 808, 7712),
                new Point(11073, 808, 7712),
                new Point(11073, 972, 7712),
            );
            $add(
                new Point(11107, 876, 7712),
                new Point(11107, 876, 7653),
                new Point(11073, 876, 7653),
                new Point(11073, 876, 7712),
            );
            $add(
                new Point(11107, 1248, 7754),
                new Point(11107, 808, 7754),
                new Point(11073, 808, 7754),
                new Point(11073, 1248, 7754),
            );
            $add(
                new Point(10846, 876, 7712),
                new Point(10846, 876, 7653),
                new Point(10846, 808, 7653),
                new Point(10846, 808, 7712),
            );
            $add(
                new Point(11107, 1248, 7754),
                new Point(11107, 1248, 7799),
                new Point(11107, 808, 7799),
                new Point(11107, 808, 7754),
            );
            $add(
                new Point(10846, 1248, 7799),
                new Point(10846, 1248, 7754),
                new Point(10846, 808, 7754),
                new Point(10846, 808, 7799),
            );
            $add(
                new Point(11107, 972, 7712),
                new Point(11107, 972, 7754),
                new Point(11107, 808, 7754),
                new Point(11107, 808, 7712),
            );
            $add(
                new Point(11107, 876, 7653),
                new Point(11107, 876, 7712),
                new Point(11107, 808, 7712),
                new Point(11107, 808, 7653),
            );
            $add(
                new Point(10846, 972, 7754),
                new Point(10846, 972, 7712),
                new Point(10846, 808, 7712),
                new Point(10846, 808, 7754),
            );
            $add(
                new Point(10846, 1248, 7799),
                new Point(10846, 1248, 7754),
                new Point(11107, 1248, 7754),
                new Point(11107, 1248, 7799),
            );

            // prop.001
            $add(
                new Point(3398, 2309, 5555),
                new Point(3398, 824, 5555),
                new Point(3490, 824, 5555),
                new Point(3490, 2309, 5555),
            );
            $add(
                new Point(3490, 2309, 5555),
                new Point(3490, 824, 5555),
                new Point(3490, 824, 5500),
                new Point(3490, 2309, 5500),
            );
            $add(
                new Point(3398, 824, 5555),
                new Point(3398, 2309, 5555),
                new Point(3398, 2309, 5500),
                new Point(3398, 824, 5500),
            );
            $add(
                new Point(3490, 2309, 5500),
                new Point(3490, 824, 5500),
                new Point(3398, 824, 5500),
                new Point(3398, 2309, 5500),
            );

            // prop.002
            $add(
                new Point(1965, 2291, 5564),
                new Point(1965, 806, 5564),
                new Point(2057, 806, 5564),
                new Point(2057, 2291, 5564),
            );
            $add(
                new Point(2057, 2291, 5564),
                new Point(2057, 806, 5564),
                new Point(2057, 806, 5509),
                new Point(2057, 2291, 5509),
            );
            $add(
                new Point(1965, 806, 5564),
                new Point(1965, 2291, 5564),
                new Point(1965, 2291, 5509),
                new Point(1965, 806, 5509),
            );
            $add(
                new Point(2057, 2291, 5509),
                new Point(2057, 806, 5509),
                new Point(1965, 806, 5509),
                new Point(1965, 2291, 5509),
            );

            // prop.003
            $add(
                new Point(8948, 2299, 3162),
                new Point(8948, 814, 3162),
                new Point(9040, 814, 3162),
                new Point(9040, 2299, 3162),
            );
            $add(
                new Point(9040, 2299, 3162),
                new Point(9040, 814, 3162),
                new Point(9040, 814, 3107),
                new Point(9040, 2299, 3107),
            );
            $add(
                new Point(8948, 814, 3162),
                new Point(8948, 2299, 3162),
                new Point(8948, 2299, 3107),
                new Point(8948, 814, 3107),
            );
            $add(
                new Point(9040, 2299, 3107),
                new Point(9040, 814, 3107),
                new Point(8948, 814, 3107),
                new Point(8948, 2299, 3107),
            );

            // prop.004
            $add(
                new Point(6931, 2296, 6044),
                new Point(6931, 811, 6044),
                new Point(7023, 811, 6044),
                new Point(7023, 2296, 6044),
            );
            $add(
                new Point(7023, 2296, 6044),
                new Point(7023, 811, 6044),
                new Point(7023, 811, 5989),
                new Point(7023, 2296, 5989),
            );
            $add(
                new Point(6931, 811, 6044),
                new Point(6931, 2296, 6044),
                new Point(6931, 2296, 5989),
                new Point(6931, 811, 5989),
            );
            $add(
                new Point(7023, 2296, 5989),
                new Point(7023, 811, 5989),
                new Point(6931, 811, 5989),
                new Point(6931, 2296, 5989),
            );

            // prop.005
            $add(
                new Point(4856, 2052, 11488),
                new Point(4856, 567, 11488),
                new Point(4948, 567, 11488),
                new Point(4948, 2052, 11488),
            );
            $add(
                new Point(4948, 2052, 11488),
                new Point(4948, 567, 11488),
                new Point(4948, 567, 11433),
                new Point(4948, 2052, 11433),
            );
            $add(
                new Point(4856, 567, 11488),
                new Point(4856, 2052, 11488),
                new Point(4856, 2052, 11433),
                new Point(4856, 567, 11433),
            );
            $add(
                new Point(4948, 2052, 11433),
                new Point(4948, 567, 11433),
                new Point(4856, 567, 11433),
                new Point(4856, 2052, 11433),
            );

            // prop.009
            $add(
                new Point(9242, 1204, 11973),
                new Point(9304, 1204, 11973),
                new Point(9304, 1089, 11973),
                new Point(9242, 1089, 11973),
            );
            $add(
                new Point(9304, 1204, 11973),
                new Point(9304, 1204, 11910),
                new Point(9304, 1089, 11910),
                new Point(9304, 1089, 11973),
            );
            $add(
                new Point(9242, 1204, 11910),
                new Point(9242, 1204, 11973),
                new Point(9242, 1089, 11973),
                new Point(9242, 1089, 11910),
            );
            $add(
                new Point(9242, 1204, 11973),
                new Point(9242, 1204, 11910),
                new Point(9304, 1204, 11910),
                new Point(9304, 1204, 11973),
            );
            $add(
                new Point(9304, 1204, 11910),
                new Point(9242, 1204, 11910),
                new Point(9242, 1089, 11910),
                new Point(9304, 1089, 11910),
            );

            // prop.010
            $add(
                new Point(9315, 1204, 11850),
                new Point(9315, 1204, 11788),
                new Point(9377, 1204, 11788),
                new Point(9377, 1204, 11850),
            );
            $add(
                new Point(9315, 1204, 11788),
                new Point(9315, 1204, 11850),
                new Point(9315, 1089, 11850),
                new Point(9315, 1089, 11788),
            );
            $add(
                new Point(9315, 1204, 11850),
                new Point(9377, 1204, 11850),
                new Point(9377, 1089, 11850),
                new Point(9315, 1089, 11850),
            );
            $add(
                new Point(9377, 1204, 11850),
                new Point(9377, 1204, 11788),
                new Point(9377, 1089, 11788),
                new Point(9377, 1089, 11850),
            );
            $add(
                new Point(9377, 1204, 11788),
                new Point(9315, 1204, 11788),
                new Point(9315, 1089, 11788),
                new Point(9377, 1089, 11788),
            );

            // prop.011
            $add(
                new Point(10240, 1227, 12384),
                new Point(10240, 1227, 12305),
                new Point(10318, 1227, 12305),
                new Point(10318, 1227, 12384),
            );
            $add(
                new Point(10318, 1227, 12305),
                new Point(10240, 1227, 12305),
                new Point(10240, 1088, 12305),
                new Point(10318, 1088, 12305),
            );
            $add(
                new Point(10240, 1227, 12305),
                new Point(10240, 1227, 12384),
                new Point(10240, 1088, 12384),
                new Point(10240, 1088, 12305),
            );
            $add(
                new Point(10240, 1227, 12384),
                new Point(10318, 1227, 12384),
                new Point(10318, 1088, 12384),
                new Point(10240, 1088, 12384),
            );
            $add(
                new Point(10318, 1227, 12384),
                new Point(10318, 1227, 12305),
                new Point(10318, 1088, 12305),
                new Point(10318, 1088, 12384),
            );

            // prop.012
            $add(
                new Point(9865, 1203, 11467),
                new Point(9927, 1203, 11467),
                new Point(9927, 1089, 11467),
                new Point(9865, 1089, 11467),
            );
            $add(
                new Point(9927, 1203, 11467),
                new Point(9927, 1203, 11404),
                new Point(9927, 1089, 11404),
                new Point(9927, 1089, 11467),
            );
            $add(
                new Point(9865, 1203, 11404),
                new Point(9865, 1203, 11467),
                new Point(9865, 1089, 11467),
                new Point(9865, 1089, 11404),
            );
            $add(
                new Point(9865, 1203, 11467),
                new Point(9865, 1203, 11404),
                new Point(9927, 1203, 11404),
                new Point(9927, 1203, 11467),
            );
            $add(
                new Point(9927, 1203, 11404),
                new Point(9865, 1203, 11404),
                new Point(9865, 1089, 11404),
                new Point(9927, 1089, 11404),
            );

            // prop.013
            $add(
                new Point(9986, 1206, 11466),
                new Point(9986, 1206, 11403),
                new Point(10048, 1206, 11403),
                new Point(10048, 1206, 11466),
            );
            $add(
                new Point(9986, 1206, 11403),
                new Point(9986, 1206, 11466),
                new Point(9986, 1088, 11466),
                new Point(9986, 1088, 11403),
            );
            $add(
                new Point(9986, 1206, 11466),
                new Point(10048, 1206, 11466),
                new Point(10048, 1088, 11466),
                new Point(9986, 1088, 11466),
            );
            $add(
                new Point(10048, 1206, 11466),
                new Point(10048, 1206, 11403),
                new Point(10048, 1088, 11403),
                new Point(10048, 1088, 11466),
            );
            $add(
                new Point(10048, 1206, 11403),
                new Point(9986, 1206, 11403),
                new Point(9986, 1088, 11403),
                new Point(10048, 1088, 11403),
            );

            // prop.015
            $add(
                new Point(10295, 1227, 12276),
                new Point(10355, 1227, 12276),
                new Point(10355, 1086, 12276),
                new Point(10295, 1086, 12276),
            );
            $add(
                new Point(10355, 1227, 12276),
                new Point(10355, 1227, 12216),
                new Point(10355, 1086, 12216),
                new Point(10355, 1086, 12276),
            );
            $add(
                new Point(10295, 1227, 12216),
                new Point(10295, 1227, 12276),
                new Point(10295, 1086, 12276),
                new Point(10295, 1086, 12216),
            );
            $add(
                new Point(10295, 1227, 12276),
                new Point(10295, 1227, 12216),
                new Point(10355, 1227, 12216),
                new Point(10355, 1227, 12276),
            );
            $add(
                new Point(10355, 1227, 12216),
                new Point(10295, 1227, 12216),
                new Point(10295, 1086, 12216),
                new Point(10355, 1086, 12216),
            );

            // prop.016
            $add(
                new Point(10230, 1168, 12303),
                new Point(10091, 1168, 12303),
                new Point(10091, 1088, 12303),
                new Point(10230, 1088, 12303),
            );
            $add(
                new Point(10091, 1168, 12381),
                new Point(10230, 1168, 12381),
                new Point(10230, 1088, 12381),
                new Point(10091, 1088, 12381),
            );
            $add(
                new Point(10230, 1168, 12381),
                new Point(10230, 1168, 12303),
                new Point(10230, 1088, 12303),
                new Point(10230, 1088, 12381),
            );
            $add(
                new Point(10091, 1168, 12381),
                new Point(10091, 1168, 12303),
                new Point(10230, 1168, 12303),
                new Point(10230, 1168, 12381),
            );
            $add(
                new Point(10091, 1168, 12303),
                new Point(10091, 1168, 12381),
                new Point(10091, 1088, 12381),
                new Point(10091, 1088, 12303),
            );

            // prop.017
            $add(
                new Point(11228, 1228, 11341),
                new Point(11228, 1228, 11280),
                new Point(11288, 1228, 11280),
                new Point(11288, 1228, 11341),
            );
            $add(
                new Point(11228, 1228, 11280),
                new Point(11228, 1228, 11341),
                new Point(11228, 1089, 11341),
                new Point(11228, 1089, 11280),
            );
            $add(
                new Point(11228, 1228, 11341),
                new Point(11288, 1228, 11341),
                new Point(11288, 1089, 11341),
                new Point(11228, 1089, 11341),
            );
            $add(
                new Point(11288, 1228, 11341),
                new Point(11288, 1228, 11280),
                new Point(11288, 1089, 11280),
                new Point(11288, 1089, 11341),
            );
            $add(
                new Point(11288, 1228, 11280),
                new Point(11228, 1228, 11280),
                new Point(11228, 1089, 11280),
                new Point(11288, 1089, 11280),
            );

            // prop.018
            $add(
                new Point(11659, 1337, 12622),
                new Point(11597, 1337, 12622),
                new Point(11597, 1189, 12622),
                new Point(11659, 1189, 12622),
            );
            $add(
                new Point(11597, 1337, 12685),
                new Point(11659, 1337, 12685),
                new Point(11659, 1189, 12685),
                new Point(11597, 1189, 12685),
            );
            $add(
                new Point(11659, 1337, 12685),
                new Point(11659, 1337, 12622),
                new Point(11659, 1189, 12622),
                new Point(11659, 1189, 12685),
            );
            $add(
                new Point(11597, 1337, 12685),
                new Point(11597, 1337, 12622),
                new Point(11659, 1337, 12622),
                new Point(11659, 1337, 12685),
            );
            $add(
                new Point(11597, 1337, 12622),
                new Point(11597, 1337, 12685),
                new Point(11597, 1189, 12685),
                new Point(11597, 1189, 12622),
            );

            // prop.019
            $add(
                new Point(11727, 1337, 12699),
                new Point(11789, 1337, 12699),
                new Point(11789, 1190, 12699),
                new Point(11727, 1190, 12699),
            );
            $add(
                new Point(11789, 1337, 12699),
                new Point(11789, 1337, 12637),
                new Point(11789, 1190, 12637),
                new Point(11789, 1190, 12699),
            );
            $add(
                new Point(11727, 1337, 12637),
                new Point(11727, 1337, 12699),
                new Point(11727, 1190, 12699),
                new Point(11727, 1190, 12637),
            );
            $add(
                new Point(11727, 1337, 12699),
                new Point(11727, 1337, 12637),
                new Point(11789, 1337, 12637),
                new Point(11789, 1337, 12699),
            );
            $add(
                new Point(11789, 1337, 12637),
                new Point(11727, 1337, 12637),
                new Point(11727, 1190, 12637),
                new Point(11789, 1190, 12637),
            );

            // prop.020
            $add(
                new Point(11528, 885, 7870),
                new Point(11466, 885, 7870),
                new Point(11466, 806, 7870),
                new Point(11528, 806, 7870),
            );
            $add(
                new Point(11466, 885, 8005),
                new Point(11528, 885, 8005),
                new Point(11528, 806, 8005),
                new Point(11466, 806, 8005),
            );
            $add(
                new Point(11528, 885, 8005),
                new Point(11528, 885, 7870),
                new Point(11528, 806, 7870),
                new Point(11528, 806, 8005),
            );
            $add(
                new Point(11466, 885, 8005),
                new Point(11466, 885, 7870),
                new Point(11528, 885, 7870),
                new Point(11528, 885, 8005),
            );
            $add(
                new Point(11466, 885, 7870),
                new Point(11466, 885, 8005),
                new Point(11466, 806, 8005),
                new Point(11466, 806, 7870),
            );

            // prop.021
            $add(
                new Point(10000, 942, 6732),
                new Point(10062, 942, 6732),
                new Point(10062, 802, 6732),
                new Point(10000, 802, 6732),
            );
            $add(
                new Point(10062, 942, 6732),
                new Point(10062, 942, 6670),
                new Point(10062, 802, 6670),
                new Point(10062, 802, 6732),
            );
            $add(
                new Point(10000, 942, 6670),
                new Point(10000, 942, 6732),
                new Point(10000, 802, 6732),
                new Point(10000, 802, 6670),
            );
            $add(
                new Point(10000, 942, 6732),
                new Point(10000, 942, 6670),
                new Point(10062, 942, 6670),
                new Point(10062, 942, 6732),
            );
            $add(
                new Point(10062, 942, 6670),
                new Point(10000, 942, 6670),
                new Point(10000, 802, 6670),
                new Point(10062, 802, 6670),
            );

            // prop.022
            $add(
                new Point(10200, 942, 6500),
                new Point(10138, 942, 6500),
                new Point(10138, 800, 6500),
                new Point(10200, 800, 6500),
            );
            $add(
                new Point(10138, 942, 6563),
                new Point(10200, 942, 6563),
                new Point(10200, 800, 6563),
                new Point(10138, 800, 6563),
            );
            $add(
                new Point(10200, 942, 6563),
                new Point(10200, 942, 6500),
                new Point(10200, 800, 6500),
                new Point(10200, 800, 6563),
            );
            $add(
                new Point(10138, 942, 6563),
                new Point(10138, 942, 6500),
                new Point(10200, 942, 6500),
                new Point(10200, 942, 6563),
            );
            $add(
                new Point(10138, 942, 6500),
                new Point(10138, 942, 6563),
                new Point(10138, 800, 6563),
                new Point(10138, 800, 6500),
            );

            // prop.023
            $add(
                new Point(1618, 1342, 2095),
                new Point(1556, 1342, 2095),
                new Point(1556, 1203, 2095),
                new Point(1618, 1203, 2095),
            );
            $add(
                new Point(1556, 1342, 2158),
                new Point(1618, 1342, 2158),
                new Point(1618, 1203, 2158),
                new Point(1556, 1203, 2158),
            );
            $add(
                new Point(1618, 1342, 2158),
                new Point(1618, 1342, 2095),
                new Point(1618, 1203, 2095),
                new Point(1618, 1203, 2158),
            );
            $add(
                new Point(1556, 1342, 2158),
                new Point(1556, 1342, 2095),
                new Point(1618, 1342, 2095),
                new Point(1618, 1342, 2158),
            );
            $add(
                new Point(1556, 1342, 2095),
                new Point(1556, 1342, 2158),
                new Point(1556, 1203, 2158),
                new Point(1556, 1203, 2095),
            );

            // prop.024
            $add(
                new Point(2018, 1205, 2555),
                new Point(2086, 1205, 2555),
                new Point(2086, 1205, 2413),
                new Point(2018, 1205, 2413),
            );
            $add(
                new Point(2018, 1205, 2555),
                new Point(2018, 1205, 2413),
                new Point(2018, 1103, 2413),
                new Point(2018, 1103, 2555),
            );
            $add(
                new Point(2086, 1205, 2555),
                new Point(2018, 1205, 2555),
                new Point(2018, 1103, 2555),
                new Point(2086, 1103, 2555),
            );
            $add(
                new Point(2086, 1205, 2413),
                new Point(2086, 1205, 2555),
                new Point(2086, 1103, 2555),
                new Point(2086, 1103, 2413),
            );
            $add(
                new Point(2018, 1205, 2413),
                new Point(2086, 1205, 2413),
                new Point(2086, 1103, 2413),
                new Point(2018, 1103, 2413),
            );

            // prop.025
            $add(
                new Point(3070, 1328, 3518),
                new Point(3010, 1328, 3518),
                new Point(3010, 1188, 3518),
                new Point(3070, 1188, 3518),
            );
            $add(
                new Point(3010, 1328, 3579),
                new Point(3070, 1328, 3579),
                new Point(3070, 1188, 3579),
                new Point(3010, 1188, 3579),
            );
            $add(
                new Point(3070, 1328, 3579),
                new Point(3070, 1328, 3518),
                new Point(3070, 1188, 3518),
                new Point(3070, 1188, 3579),
            );
            $add(
                new Point(3010, 1328, 3579),
                new Point(3010, 1328, 3518),
                new Point(3070, 1328, 3518),
                new Point(3070, 1328, 3579),
            );
            $add(
                new Point(3010, 1328, 3518),
                new Point(3010, 1328, 3579),
                new Point(3010, 1188, 3579),
                new Point(3010, 1188, 3518),
            );

            // prop.026
            $add(
                new Point(2968, 1327, 3589),
                new Point(2906, 1327, 3589),
                new Point(2906, 1186, 3589),
                new Point(2968, 1186, 3589),
            );
            $add(
                new Point(2906, 1327, 3652),
                new Point(2968, 1327, 3652),
                new Point(2968, 1186, 3652),
                new Point(2906, 1186, 3652),
            );
            $add(
                new Point(2968, 1327, 3652),
                new Point(2968, 1327, 3589),
                new Point(2968, 1186, 3589),
                new Point(2968, 1186, 3652),
            );
            $add(
                new Point(2906, 1327, 3652),
                new Point(2906, 1327, 3589),
                new Point(2968, 1327, 3589),
                new Point(2968, 1327, 3652),
            );
            $add(
                new Point(2906, 1327, 3589),
                new Point(2906, 1327, 3652),
                new Point(2906, 1186, 3652),
                new Point(2906, 1186, 3589),
            );

            // prop.027
            $add(
                new Point(2260, 1004, 5678),
                new Point(1709, 1004, 5678),
                new Point(1709, 880, 5678),
                new Point(2260, 880, 5678),
            );
            $add(
                new Point(1709, 1004, 5840),
                new Point(2260, 1004, 5840),
                new Point(2260, 880, 5840),
                new Point(1709, 880, 5840),
            );
            $add(
                new Point(2260, 1004, 5840),
                new Point(2260, 1004, 5678),
                new Point(2260, 880, 5678),
                new Point(2260, 880, 5840),
            );
            $add(
                new Point(1876, 995, 5910),
                new Point(1876, 890, 5910),
                new Point(1978, 890, 5910),
                new Point(1978, 995, 5910),
            );
            $add(
                new Point(1709, 1004, 5840),
                new Point(1709, 1004, 5678),
                new Point(2260, 1004, 5678),
                new Point(2260, 1004, 5840),
            );
            $add(
                new Point(1709, 1004, 5678),
                new Point(1709, 1004, 5840),
                new Point(1709, 880, 5840),
                new Point(1709, 880, 5678),
            );
            $add(
                new Point(1876, 995, 5823),
                new Point(1978, 995, 5823),
                new Point(1978, 890, 5823),
                new Point(1876, 890, 5823),
            );
            $add(
                new Point(1876, 995, 5910),
                new Point(1978, 995, 5910),
                new Point(1978, 995, 5823),
                new Point(1876, 995, 5823),
            );
            $add(
                new Point(1978, 890, 5910),
                new Point(1876, 890, 5910),
                new Point(1876, 890, 5823),
                new Point(1978, 890, 5823),
            );
            $add(
                new Point(1876, 890, 5910),
                new Point(1876, 995, 5910),
                new Point(1876, 995, 5823),
                new Point(1876, 890, 5823),
            );
            $add(
                new Point(1978, 995, 5910),
                new Point(1978, 890, 5910),
                new Point(1978, 890, 5823),
                new Point(1978, 995, 5823),
            );

            // prop.028
            $add(
                new Point(1815, 1091, 5766),
                new Point(1717, 1091, 5766),
                new Point(1717, 981, 5766),
                new Point(1815, 981, 5766),
            );
            $add(
                new Point(1717, 1091, 5851),
                new Point(1815, 1091, 5851),
                new Point(1815, 981, 5851),
                new Point(1717, 981, 5851),
            );
            $add(
                new Point(1815, 1091, 5851),
                new Point(1815, 1091, 5766),
                new Point(1815, 981, 5766),
                new Point(1815, 981, 5851),
            );
            $add(
                new Point(1717, 1091, 5851),
                new Point(1717, 1091, 5766),
                new Point(1815, 1091, 5766),
                new Point(1815, 1091, 5851),
            );
            $add(
                new Point(1717, 1091, 5766),
                new Point(1717, 1091, 5851),
                new Point(1717, 981, 5851),
                new Point(1717, 981, 5766),
            );

            // prop.029
            $add(
                new Point(3020, 1024, 6087),
                new Point(3020, 1024, 6250),
                new Point(3020, 896, 6250),
                new Point(3020, 896, 6087),
            );
            $add(
                new Point(3195, 1024, 6087),
                new Point(3020, 1024, 6087),
                new Point(3020, 896, 6087),
                new Point(3195, 896, 6087),
            );
            $add(
                new Point(3020, 1024, 6250),
                new Point(3020, 1024, 6087),
                new Point(3195, 1024, 6087),
                new Point(3195, 1024, 6250),
            );
            $add(
                new Point(3195, 1024, 6250),
                new Point(3195, 1024, 6087),
                new Point(3195, 896, 6087),
                new Point(3195, 896, 6250),
            );

            // prop.030
            $add(
                new Point(2129, 1036, 12085),
                new Point(2191, 1036, 12085),
                new Point(2191, 896, 12085),
                new Point(2129, 896, 12085),
            );
            $add(
                new Point(2191, 1036, 12085),
                new Point(2191, 1036, 12022),
                new Point(2191, 896, 12022),
                new Point(2191, 896, 12085),
            );
            $add(
                new Point(2129, 1036, 12022),
                new Point(2129, 1036, 12085),
                new Point(2129, 896, 12085),
                new Point(2129, 896, 12022),
            );
            $add(
                new Point(2129, 1036, 12085),
                new Point(2129, 1036, 12022),
                new Point(2191, 1036, 12022),
                new Point(2191, 1036, 12085),
            );
            $add(
                new Point(2191, 1036, 12022),
                new Point(2129, 1036, 12022),
                new Point(2129, 896, 12022),
                new Point(2191, 896, 12022),
            );

            // prop.031
            $add(
                new Point(2210, 1036, 12105),
                new Point(2210, 1036, 12168),
                new Point(2210, 896, 12168),
                new Point(2210, 896, 12105),
            );
            $add(
                new Point(2272, 1036, 12105),
                new Point(2210, 1036, 12105),
                new Point(2210, 896, 12105),
                new Point(2272, 896, 12105),
            );
            $add(
                new Point(2210, 1036, 12168),
                new Point(2272, 1036, 12168),
                new Point(2272, 896, 12168),
                new Point(2210, 896, 12168),
            );
            $add(
                new Point(2210, 1036, 12168),
                new Point(2210, 1036, 12105),
                new Point(2272, 1036, 12105),
                new Point(2272, 1036, 12168),
            );
            $add(
                new Point(2272, 1036, 12168),
                new Point(2272, 1036, 12105),
                new Point(2272, 896, 12105),
                new Point(2272, 896, 12168),
            );

            // prop.032
            $add(
                new Point(2339, 1036, 12455),
                new Point(2339, 1036, 12517),
                new Point(2339, 897, 12517),
                new Point(2339, 897, 12455),
            );
            $add(
                new Point(2401, 1036, 12455),
                new Point(2339, 1036, 12455),
                new Point(2339, 897, 12455),
                new Point(2401, 897, 12455),
            );
            $add(
                new Point(2339, 1036, 12517),
                new Point(2401, 1036, 12517),
                new Point(2401, 897, 12517),
                new Point(2339, 897, 12517),
            );
            $add(
                new Point(2339, 1036, 12517),
                new Point(2339, 1036, 12455),
                new Point(2401, 1036, 12455),
                new Point(2401, 1036, 12517),
            );
            $add(
                new Point(2401, 1036, 12517),
                new Point(2401, 1036, 12455),
                new Point(2401, 897, 12455),
                new Point(2401, 897, 12517),
            );

            // prop.033
            $add(
                new Point(3643, 945, 11241),
                new Point(3581, 945, 11241),
                new Point(3581, 804, 11241),
                new Point(3643, 804, 11241),
            );
            $add(
                new Point(3581, 945, 11303),
                new Point(3643, 945, 11303),
                new Point(3643, 804, 11303),
                new Point(3581, 804, 11303),
            );
            $add(
                new Point(3643, 945, 11303),
                new Point(3643, 945, 11241),
                new Point(3643, 804, 11241),
                new Point(3643, 804, 11303),
            );
            $add(
                new Point(3581, 945, 11303),
                new Point(3581, 945, 11241),
                new Point(3643, 945, 11241),
                new Point(3643, 945, 11303),
            );
            $add(
                new Point(3581, 945, 11241),
                new Point(3581, 945, 11303),
                new Point(3581, 804, 11303),
                new Point(3581, 804, 11241),
            );

            // prop.034
            $add(
                new Point(3574, 946, 11129),
                new Point(3574, 946, 11192),
                new Point(3574, 804, 11192),
                new Point(3574, 804, 11129),
            );
            $add(
                new Point(3636, 946, 11129),
                new Point(3574, 946, 11129),
                new Point(3574, 804, 11129),
                new Point(3636, 804, 11129),
            );
            $add(
                new Point(3574, 946, 11192),
                new Point(3636, 946, 11192),
                new Point(3636, 804, 11192),
                new Point(3574, 804, 11192),
            );
            $add(
                new Point(3574, 946, 11192),
                new Point(3574, 946, 11129),
                new Point(3636, 946, 11129),
                new Point(3636, 946, 11192),
            );
            $add(
                new Point(3636, 946, 11192),
                new Point(3636, 946, 11129),
                new Point(3636, 804, 11129),
                new Point(3636, 804, 11192),
            );

            // prop.035
            $add(
                new Point(2125, 1091, 11435),
                new Point(2125, 1091, 11148),
                new Point(2412, 1091, 11148),
                new Point(2412, 1091, 11435),
            );
            $add(
                new Point(2125, 1091, 11148),
                new Point(2125, 1091, 11435),
                new Point(2125, 808, 11435),
                new Point(2125, 808, 11148),
            );
            $add(
                new Point(2125, 1091, 11435),
                new Point(2412, 1091, 11435),
                new Point(2412, 808, 11435),
                new Point(2125, 808, 11435),
            );
            $add(
                new Point(2412, 1091, 11435),
                new Point(2412, 1091, 11148),
                new Point(2412, 808, 11148),
                new Point(2412, 808, 11435),
            );
            $add(
                new Point(2412, 1091, 11148),
                new Point(2125, 1091, 11148),
                new Point(2125, 808, 11148),
                new Point(2412, 808, 11148),
            );

            // prop.036
            $add(
                new Point(2703, 1187, 11867),
                new Point(2703, 1187, 11674),
                new Point(2897, 1187, 11674),
                new Point(2897, 1187, 11867),
            );
            $add(
                new Point(2703, 1187, 11674),
                new Point(2703, 1187, 11867),
                new Point(2703, 814, 11867),
                new Point(2703, 814, 11674),
            );
            $add(
                new Point(2703, 1187, 11867),
                new Point(2897, 1187, 11867),
                new Point(2897, 814, 11867),
                new Point(2703, 814, 11867),
            );
            $add(
                new Point(2897, 1187, 11867),
                new Point(2897, 1187, 11674),
                new Point(2897, 814, 11674),
                new Point(2897, 814, 11867),
            );
            $add(
                new Point(2897, 1187, 11674),
                new Point(2703, 1187, 11674),
                new Point(2703, 814, 11674),
                new Point(2897, 814, 11674),
            );

            // prop.037
            $add(
                new Point(3462, 1139, 12305),
                new Point(3462, 1139, 11901),
                new Point(3851, 1139, 11901),
                new Point(3851, 1139, 12305),
            );
            $add(
                new Point(3462, 1139, 11901),
                new Point(3462, 1139, 12305),
                new Point(3462, 814, 12305),
                new Point(3462, 814, 11901),
            );
            $add(
                new Point(3462, 1139, 12305),
                new Point(3851, 1139, 12305),
                new Point(3851, 814, 12305),
                new Point(3462, 814, 12305),
            );
            $add(
                new Point(3851, 1139, 12305),
                new Point(3851, 1139, 11901),
                new Point(3851, 814, 11901),
                new Point(3851, 814, 12305),
            );
            $add(
                new Point(3851, 1139, 11901),
                new Point(3462, 1139, 11901),
                new Point(3462, 814, 11901),
                new Point(3851, 814, 11901),
            );

            // prop.038
            $add(
                new Point(3312, 1091, 12071),
                new Point(3312, 1091, 11927),
                new Point(3477, 1091, 11927),
                new Point(3477, 1091, 12071),
            );
            $add(
                new Point(3312, 1091, 11927),
                new Point(3312, 1091, 12071),
                new Point(3312, 923, 12071),
                new Point(3312, 923, 11927),
            );
            $add(
                new Point(3312, 1091, 12071),
                new Point(3477, 1091, 12071),
                new Point(3477, 923, 12071),
                new Point(3312, 923, 12071),
            );
            $add(
                new Point(3477, 1091, 11927),
                new Point(3312, 1091, 11927),
                new Point(3312, 923, 11927),
                new Point(3477, 923, 11927),
            );

            // prop.039
            $add(
                new Point(3244, 947, 12127),
                new Point(3244, 947, 11910),
                new Point(3456, 947, 11910),
                new Point(3456, 947, 12127),
            );
            $add(
                new Point(3244, 947, 11910),
                new Point(3244, 947, 12127),
                new Point(3244, 779, 12127),
                new Point(3244, 779, 11910),
            );
            $add(
                new Point(3244, 947, 12127),
                new Point(3456, 947, 12127),
                new Point(3456, 779, 12127),
                new Point(3244, 779, 12127),
            );
            $add(
                new Point(3456, 947, 12127),
                new Point(3456, 947, 11910),
                new Point(3456, 779, 11910),
                new Point(3456, 779, 12127),
            );
            $add(
                new Point(3456, 947, 11910),
                new Point(3244, 947, 11910),
                new Point(3244, 779, 11910),
                new Point(3456, 779, 11910),
            );

            // prop.040
            $add(
                new Point(2802, 983, 12359),
                new Point(2892, 983, 12556),
                new Point(2892, 813, 12556),
                new Point(2802, 813, 12359),
            );
            $add(
                new Point(2892, 983, 12556),
                new Point(3089, 983, 12473),
                new Point(3089, 813, 12473),
                new Point(2892, 813, 12556),
            );
            $add(
                new Point(3089, 983, 12473),
                new Point(3003, 983, 12275),
                new Point(3003, 813, 12275),
                new Point(3089, 813, 12473),
            );
            $add(
                new Point(3003, 983, 12275),
                new Point(2802, 983, 12359),
                new Point(2802, 813, 12359),
                new Point(3003, 813, 12275),
            );
            $add(
                new Point(2865, 983, 12488),
                new Point(2865, 983, 12344),
                new Point(3030, 983, 12344),
                new Point(3030, 983, 12488),
            );

            // prop.042
            $add(
                new Point(2736, 995, 9268),
                new Point(2872, 995, 9404),
                new Point(2872, 805, 9404),
                new Point(2736, 805, 9268),
            );
            $add(
                new Point(2872, 995, 9404),
                new Point(3008, 995, 9268),
                new Point(3008, 805, 9268),
                new Point(2872, 805, 9404),
            );
            $add(
                new Point(2872, 995, 9133),
                new Point(2736, 995, 9268),
                new Point(2736, 805, 9268),
                new Point(2872, 805, 9133),
            );
            $add(
                new Point(2809, 995, 9327),
                new Point(2809, 995, 9199),
                new Point(2945, 995, 9199),
                new Point(2945, 995, 9327),
            );

            // prop.044
            $add(
                new Point(2655, 911, 9578),
                new Point(3153, 911, 9858),
                new Point(3153, 821, 9858),
                new Point(2655, 821, 9578),
            );
            $add(
                new Point(3153, 911, 9858),
                new Point(3266, 911, 9649),
                new Point(3266, 821, 9649),
                new Point(3153, 821, 9858),
            );
            $add(
                new Point(3266, 911, 9649),
                new Point(2760, 911, 9382),
                new Point(2760, 821, 9382),
                new Point(3266, 821, 9649),
            );
            $add(
                new Point(2760, 911, 9382),
                new Point(2655, 911, 9578),
                new Point(2655, 821, 9578),
                new Point(2760, 821, 9382),
            );
            $add(
                new Point(2871, 977, 9679),
                new Point(3010, 977, 9679),
                new Point(3010, 977, 9549),
                new Point(2871, 977, 9549),
            );
            $add(
                new Point(2871, 977, 9679),
                new Point(2871, 977, 9549),
                new Point(2871, 877, 9549),
                new Point(2871, 877, 9679),
            );
            $add(
                new Point(3010, 977, 9679),
                new Point(2871, 977, 9679),
                new Point(2871, 877, 9679),
                new Point(3010, 877, 9679),
            );
            $add(
                new Point(3010, 977, 9549),
                new Point(3010, 977, 9679),
                new Point(3010, 877, 9679),
                new Point(3010, 877, 9549),
            );
            $add(
                new Point(2871, 977, 9549),
                new Point(3010, 977, 9549),
                new Point(3010, 877, 9549),
                new Point(2871, 877, 9549),
            );
            $add(
                new Point(3035, 911, 9780),
                new Point(3035, 911, 9625),
                new Point(3201, 911, 9625),
                new Point(3201, 911, 9780),
            );
            $add(
                new Point(2717, 911, 9592),
                new Point(2717, 911, 9456),
                new Point(2862, 911, 9456),
                new Point(2862, 911, 9592),
            );

            // prop.050
            $add(
                new Point(4993, 731, 11810),
                new Point(5207, 731, 11810),
                new Point(5207, 731, 11608),
                new Point(4993, 731, 11608),
            );
            $add(
                new Point(5207, 731, 11608),
                new Point(5207, 731, 11810),
                new Point(5207, 524, 11810),
                new Point(5207, 524, 11608),
            );
            $add(
                new Point(4993, 731, 11608),
                new Point(5207, 731, 11608),
                new Point(5207, 524, 11608),
                new Point(4993, 524, 11608),
            );
            $add(
                new Point(4993, 731, 11810),
                new Point(4993, 731, 11608),
                new Point(4993, 524, 11608),
                new Point(4993, 524, 11810),
            );
            $add(
                new Point(5207, 731, 11810),
                new Point(4993, 731, 11810),
                new Point(4993, 524, 11810),
                new Point(5207, 524, 11810),
            );

            // prop.051
            $add(
                new Point(4990, 719, 11605),
                new Point(5229, 719, 11605),
                new Point(5229, 719, 11359),
                new Point(4990, 719, 11359),
            );
            $add(
                new Point(5229, 719, 11605),
                new Point(4990, 719, 11605),
                new Point(4990, 507, 11605),
                new Point(5229, 507, 11605),
            );
            $add(
                new Point(5229, 719, 11359),
                new Point(5229, 719, 11605),
                new Point(5229, 507, 11605),
                new Point(5229, 507, 11359),
            );
            $add(
                new Point(4990, 719, 11359),
                new Point(5229, 719, 11359),
                new Point(5229, 507, 11359),
                new Point(4990, 507, 11359),
            );
            $add(
                new Point(4990, 719, 11605),
                new Point(4990, 719, 11359),
                new Point(4990, 507, 11359),
                new Point(4990, 507, 11605),
            );

            // prop.052
            $add(
                new Point(4747, 827, 11741),
                new Point(4951, 827, 11741),
                new Point(4951, 827, 11537),
                new Point(4747, 827, 11537),
            );
            $add(
                new Point(4951, 827, 11741),
                new Point(4747, 827, 11741),
                new Point(4747, 717, 11741),
                new Point(4951, 717, 11741),
            );
            $add(
                new Point(4951, 827, 11537),
                new Point(4951, 827, 11741),
                new Point(4951, 717, 11741),
                new Point(4951, 717, 11537),
            );
            $add(
                new Point(4747, 827, 11537),
                new Point(4951, 827, 11537),
                new Point(4951, 717, 11537),
                new Point(4747, 717, 11537),
            );
            $add(
                new Point(4747, 827, 11741),
                new Point(4747, 827, 11537),
                new Point(4747, 717, 11537),
                new Point(4747, 717, 11741),
            );

            // prop.055
            $add(
                new Point(3910, 873, 10926),
                new Point(4037, 873, 10926),
                new Point(4037, 873, 11151),
                new Point(3910, 873, 11151),
            );
            $add(
                new Point(4037, 873, 10926),
                new Point(3910, 873, 10926),
                new Point(3910, 809, 10926),
                new Point(4037, 809, 10926),
            );
            $add(
                new Point(3910, 873, 10926),
                new Point(3910, 873, 11151),
                new Point(3910, 809, 11151),
                new Point(3910, 809, 10926),
            );
            $add(
                new Point(3910, 873, 11151),
                new Point(4037, 873, 11151),
                new Point(4037, 809, 11151),
                new Point(3910, 809, 11151),
            );
            $add(
                new Point(4037, 873, 11151),
                new Point(4037, 873, 10926),
                new Point(4037, 809, 10926),
                new Point(4037, 809, 11151),
            );

            // prop.056
            $add(
                new Point(2413, 1386, 7118),
                new Point(2413, 1386, 7214),
                new Point(2413, 891, 7214),
                new Point(2413, 891, 7118),
            );
            $add(
                new Point(2413, 1386, 7214),
                new Point(2509, 1386, 7214),
                new Point(2509, 891, 7214),
                new Point(2413, 891, 7214),
            );
            $add(
                new Point(2509, 1386, 7214),
                new Point(2509, 1386, 7118),
                new Point(2509, 891, 7118),
                new Point(2509, 891, 7214),
            );
            $add(
                new Point(2509, 1386, 7118),
                new Point(2413, 1386, 7118),
                new Point(2413, 891, 7118),
                new Point(2509, 891, 7118),
            );

            // prop.057
            $add(
                new Point(2412, 1387, 7981),
                new Point(2412, 1387, 8077),
                new Point(2412, 892, 8077),
                new Point(2412, 892, 7981),
            );
            $add(
                new Point(2412, 1387, 8077),
                new Point(2508, 1387, 8077),
                new Point(2508, 892, 8077),
                new Point(2412, 892, 8077),
            );
            $add(
                new Point(2508, 1387, 8077),
                new Point(2508, 1387, 7981),
                new Point(2508, 892, 7981),
                new Point(2508, 892, 8077),
            );
            $add(
                new Point(2508, 1387, 7981),
                new Point(2412, 1387, 7981),
                new Point(2412, 892, 7981),
                new Point(2508, 892, 7981),
            );

            // prop.058
            $add(
                new Point(3615, 1073, 7991),
                new Point(3615, 1073, 7768),
                new Point(3859, 1073, 7768),
                new Point(3859, 1073, 7991),
            );
            $add(
                new Point(3615, 1073, 7768),
                new Point(3615, 1073, 7991),
                new Point(3615, 892, 7991),
                new Point(3615, 892, 7768),
            );
            $add(
                new Point(3859, 1073, 7991),
                new Point(3859, 1073, 7768),
                new Point(3859, 892, 7768),
                new Point(3859, 892, 7991),
            );
            $add(
                new Point(3859, 1073, 7768),
                new Point(3615, 1073, 7768),
                new Point(3615, 892, 7768),
                new Point(3859, 892, 7768),
            );

            // prop.059
            $add(
                new Point(3843, 1121, 7991),
                new Point(3843, 1121, 7771),
                new Point(4055, 1121, 7771),
                new Point(4055, 1121, 7991),
            );
            $add(
                new Point(3843, 1121, 7771),
                new Point(3843, 1121, 7991),
                new Point(3843, 940, 7991),
                new Point(3843, 940, 7771),
            );
            $add(
                new Point(3843, 1121, 7991),
                new Point(4055, 1121, 7991),
                new Point(4055, 940, 7991),
                new Point(3843, 940, 7991),
            );
            $add(
                new Point(4055, 1121, 7991),
                new Point(4055, 1121, 7771),
                new Point(4055, 940, 7771),
                new Point(4055, 940, 7991),
            );
            $add(
                new Point(4055, 1121, 7771),
                new Point(3843, 1121, 7771),
                new Point(3843, 940, 7771),
                new Point(4055, 940, 7771),
            );

            // prop.060
            $add(
                new Point(4073, 1084, 7807),
                new Point(4073, 1084, 7726),
                new Point(4159, 1084, 7726),
                new Point(4159, 1084, 7807),
            );
            $add(
                new Point(4073, 1084, 7726),
                new Point(4073, 1084, 7807),
                new Point(4073, 946, 7807),
                new Point(4073, 946, 7726),
            );
            $add(
                new Point(4073, 1084, 7807),
                new Point(4159, 1084, 7807),
                new Point(4159, 946, 7807),
                new Point(4073, 946, 7807),
            );
            $add(
                new Point(4159, 1084, 7807),
                new Point(4159, 1084, 7726),
                new Point(4159, 946, 7726),
                new Point(4159, 946, 7807),
            );
            $add(
                new Point(4159, 1084, 7726),
                new Point(4073, 1084, 7726),
                new Point(4073, 946, 7726),
                new Point(4159, 946, 7726),
            );

            // prop.061
            $add(
                new Point(2326, 1035, 8038),
                new Point(2326, 1035, 7956),
                new Point(2412, 1035, 7956),
                new Point(2412, 1035, 8038),
            );
            $add(
                new Point(2326, 1035, 7956),
                new Point(2326, 1035, 8038),
                new Point(2326, 899, 8038),
                new Point(2326, 899, 7956),
            );
            $add(
                new Point(2326, 1035, 8038),
                new Point(2412, 1035, 8038),
                new Point(2412, 899, 8038),
                new Point(2326, 899, 8038),
            );
            $add(
                new Point(2412, 1035, 8038),
                new Point(2412, 1035, 7956),
                new Point(2412, 899, 7956),
                new Point(2412, 899, 8038),
            );
            $add(
                new Point(2412, 1035, 7956),
                new Point(2326, 1035, 7956),
                new Point(2326, 899, 7956),
                new Point(2412, 899, 7956),
            );

            // prop.062
            $add(
                new Point(2250, 1036, 7975),
                new Point(2250, 1036, 7894),
                new Point(2336, 1036, 7894),
                new Point(2336, 1036, 7975),
            );
            $add(
                new Point(2250, 1036, 7894),
                new Point(2250, 1036, 7975),
                new Point(2250, 899, 7975),
                new Point(2250, 899, 7894),
            );
            $add(
                new Point(2250, 1036, 7975),
                new Point(2336, 1036, 7975),
                new Point(2336, 899, 7975),
                new Point(2250, 899, 7975),
            );
            $add(
                new Point(2336, 1036, 7975),
                new Point(2336, 1036, 7894),
                new Point(2336, 899, 7894),
                new Point(2336, 899, 7975),
            );
            $add(
                new Point(2336, 1036, 7894),
                new Point(2250, 1036, 7894),
                new Point(2250, 899, 7894),
                new Point(2336, 899, 7894),
            );

            // prop.063
            $add(
                new Point(3171, 1091, 3690),
                new Point(3171, 1091, 4353),
                new Point(3473, 1091, 4353),
                new Point(3473, 1091, 3690),
            );
            $add(
                new Point(3171, 1091, 4353),
                new Point(3171, 1091, 3690),
                new Point(3171, 804, 3690),
                new Point(3171, 804, 4353),
            );
            $add(
                new Point(3171, 1091, 3690),
                new Point(3473, 1091, 3690),
                new Point(3473, 804, 3690),
                new Point(3171, 804, 3690),
            );
            $add(
                new Point(3473, 1091, 3690),
                new Point(3473, 1091, 4353),
                new Point(3473, 804, 4353),
                new Point(3473, 804, 3690),
            );
            $add(
                new Point(3473, 1091, 4353),
                new Point(3171, 1091, 4353),
                new Point(3171, 804, 4353),
                new Point(3473, 804, 4353),
            );

            // prop.064
            $add(
                new Point(2373, 1367, 3225),
                new Point(2373, 1367, 3705),
                new Point(2603, 1367, 3705),
                new Point(2603, 1367, 3225),
            );
            $add(
                new Point(2373, 1367, 3705),
                new Point(2373, 1367, 3225),
                new Point(2373, 1203, 3225),
                new Point(2373, 1203, 3705),
            );
            $add(
                new Point(2373, 1367, 3225),
                new Point(2603, 1367, 3225),
                new Point(2603, 1203, 3225),
                new Point(2373, 1203, 3225),
            );
            $add(
                new Point(2603, 1367, 3225),
                new Point(2603, 1367, 3705),
                new Point(2603, 1203, 3705),
                new Point(2603, 1203, 3225),
            );
            $add(
                new Point(2603, 1367, 3705),
                new Point(2373, 1367, 3705),
                new Point(2373, 1203, 3705),
                new Point(2603, 1203, 3705),
            );

            // prop.065
            $add(
                new Point(2597, 1367, 3455),
                new Point(2597, 1367, 3687),
                new Point(2856, 1367, 3687),
                new Point(2856, 1367, 3455),
            );
            $add(
                new Point(2597, 1367, 3687),
                new Point(2597, 1367, 3455),
                new Point(2597, 1203, 3455),
                new Point(2597, 1203, 3687),
            );
            $add(
                new Point(2597, 1367, 3455),
                new Point(2856, 1367, 3455),
                new Point(2856, 1203, 3455),
                new Point(2597, 1203, 3455),
            );
            $add(
                new Point(2856, 1367, 3455),
                new Point(2856, 1367, 3687),
                new Point(2856, 1203, 3687),
                new Point(2856, 1203, 3455),
            );
            $add(
                new Point(2856, 1367, 3687),
                new Point(2597, 1367, 3687),
                new Point(2597, 1203, 3687),
                new Point(2856, 1203, 3687),
            );

            // prop.066
            $add(
                new Point(4556, 1286, 2247),
                new Point(4556, 1286, 2848),
                new Point(4815, 1286, 2848),
                new Point(4815, 1286, 2247),
            );
            $add(
                new Point(4556, 1286, 2848),
                new Point(4556, 1286, 2247),
                new Point(4556, 1187, 2247),
                new Point(4556, 1187, 2848),
            );
            $add(
                new Point(4556, 1286, 2247),
                new Point(4815, 1286, 2247),
                new Point(4815, 1187, 2247),
                new Point(4556, 1187, 2247),
            );
            $add(
                new Point(4815, 1286, 2247),
                new Point(4815, 1286, 2848),
                new Point(4815, 1187, 2848),
                new Point(4815, 1187, 2247),
            );
            $add(
                new Point(4815, 1286, 2848),
                new Point(4556, 1286, 2848),
                new Point(4556, 1187, 2848),
                new Point(4815, 1187, 2848),
            );

            // prop.067
            $add(
                new Point(6156, 995, 3567),
                new Point(6156, 995, 3951),
                new Point(6346, 995, 3951),
                new Point(6346, 995, 3567),
            );
            $add(
                new Point(6156, 995, 3567),
                new Point(6346, 995, 3567),
                new Point(6346, 816, 3567),
                new Point(6156, 816, 3567),
            );
            $add(
                new Point(6346, 995, 3567),
                new Point(6346, 995, 3951),
                new Point(6346, 816, 3951),
                new Point(6346, 816, 3567),
            );
            $add(
                new Point(6346, 995, 3951),
                new Point(6156, 995, 3951),
                new Point(6156, 816, 3951),
                new Point(6346, 816, 3951),
            );

            // prop.068
            $add(
                new Point(6156, 1187, 3711),
                new Point(6156, 1187, 3903),
                new Point(6346, 1187, 3903),
                new Point(6346, 1187, 3711),
            );
            $add(
                new Point(6156, 1187, 3711),
                new Point(6346, 1187, 3711),
                new Point(6346, 979, 3711),
                new Point(6156, 979, 3711),
            );
            $add(
                new Point(6346, 1187, 3711),
                new Point(6346, 1187, 3903),
                new Point(6346, 979, 3903),
                new Point(6346, 979, 3711),
            );
            $add(
                new Point(6346, 1187, 3903),
                new Point(6156, 1187, 3903),
                new Point(6156, 979, 3903),
                new Point(6346, 979, 3903),
            );

            // prop.069
            $add(
                new Point(8913, 1091, 8167),
                new Point(8913, 1091, 8461),
                new Point(9194, 1091, 8461),
                new Point(9194, 1091, 8167),
            );
            $add(
                new Point(8913, 1091, 8461),
                new Point(8913, 1091, 8167),
                new Point(8913, 789, 8167),
                new Point(8913, 789, 8461),
            );
            $add(
                new Point(9194, 1091, 8461),
                new Point(8913, 1091, 8461),
                new Point(8913, 789, 8461),
                new Point(9194, 789, 8461),
            );

            // prop.070
            $add(
                new Point(8981, 1264, 10045),
                new Point(8981, 1264, 10252),
                new Point(9192, 1264, 10252),
                new Point(9192, 1264, 10045),
            );
            $add(
                new Point(8981, 1264, 10252),
                new Point(8981, 1264, 10045),
                new Point(8981, 1070, 10045),
                new Point(8981, 1070, 10252),
            );
            $add(
                new Point(8981, 1264, 10045),
                new Point(9192, 1264, 10045),
                new Point(9192, 1070, 10045),
                new Point(8981, 1070, 10045),
            );
            $add(
                new Point(9192, 1264, 10045),
                new Point(9192, 1264, 10252),
                new Point(9192, 1070, 10252),
                new Point(9192, 1070, 10045),
            );
            $add(
                new Point(9192, 1264, 10252),
                new Point(8981, 1264, 10252),
                new Point(8981, 1070, 10252),
                new Point(9192, 1070, 10252),
            );

            // prop.071
            $add(
                new Point(11720, 1029, 11409),
                new Point(11720, 1029, 11207),
                new Point(11720, 833, 11207),
                new Point(11720, 833, 11409),
            );
            $add(
                new Point(11720, 1029, 11207),
                new Point(11508, 1029, 11207),
                new Point(11508, 833, 11207),
                new Point(11720, 833, 11207),
            );
            $add(
                new Point(11508, 1029, 11409),
                new Point(11720, 1029, 11409),
                new Point(11720, 833, 11409),
                new Point(11508, 833, 11409),
            );
            $add(
                new Point(11720, 1029, 11207),
                new Point(11720, 1029, 11409),
                new Point(11508, 1029, 11409),
                new Point(11508, 1029, 11207),
            );
            $add(
                new Point(11720, 1029, 11409),
                new Point(11508, 1029, 11409),
                new Point(11508, 1084, 11409),
                new Point(11719, 1084, 11409),
            );
            $add(
                new Point(11719, 1084, 11409),
                new Point(11508, 1084, 11409),
                new Point(11508, 1033, 11217),
                new Point(11719, 1033, 11217),
            );

            // prop.072
            $add(
                new Point(8069, 995, 3637),
                new Point(8069, 995, 4126),
                new Point(8341, 995, 4126),
                new Point(8341, 995, 3637),
            );
            $add(
                new Point(8069, 995, 3637),
                new Point(8341, 995, 3637),
                new Point(8341, 836, 3637),
                new Point(8069, 836, 3637),
            );
            $add(
                new Point(8341, 995, 3637),
                new Point(8341, 995, 4126),
                new Point(8341, 836, 4126),
                new Point(8341, 836, 3637),
            );
            $add(
                new Point(8341, 995, 4126),
                new Point(8069, 995, 4126),
                new Point(8069, 836, 4126),
                new Point(8341, 836, 4126),
            );

            // prop.073
            $add(
                new Point(8066, 1199, 3877),
                new Point(8066, 1199, 4126),
                new Point(8326, 1199, 4126),
                new Point(8326, 1199, 3877),
            );
            $add(
                new Point(8066, 1199, 3877),
                new Point(8326, 1199, 3877),
                new Point(8326, 988, 3877),
                new Point(8066, 988, 3877),
            );
            $add(
                new Point(8326, 1199, 3877),
                new Point(8326, 1199, 4126),
                new Point(8326, 988, 4126),
                new Point(8326, 988, 3877),
            );
            $add(
                new Point(8326, 1199, 4126),
                new Point(8066, 1199, 4126),
                new Point(8066, 988, 4126),
                new Point(8326, 988, 4126),
            );

            // prop.074
            $add(
                new Point(8067, 932, 3076),
                new Point(8067, 932, 3649),
                new Point(8336, 932, 3649),
                new Point(8336, 932, 3076),
            );
            $add(
                new Point(8067, 932, 3076),
                new Point(8336, 932, 3076),
                new Point(8336, 829, 3076),
                new Point(8067, 829, 3076),
            );
            $add(
                new Point(8336, 932, 3076),
                new Point(8336, 932, 3649),
                new Point(8336, 829, 3649),
                new Point(8336, 829, 3076),
            );
            $add(
                new Point(8336, 932, 3649),
                new Point(8067, 932, 3649),
                new Point(8067, 829, 3649),
                new Point(8336, 829, 3649),
            );

            // prop.075
            $add(
                new Point(8074, 999, 3229),
                new Point(8074, 999, 3410),
                new Point(8336, 999, 3410),
                new Point(8336, 999, 3229),
            );
            $add(
                new Point(8074, 999, 3229),
                new Point(8336, 999, 3229),
                new Point(8336, 820, 3229),
                new Point(8074, 820, 3229),
            );
            $add(
                new Point(8336, 999, 3229),
                new Point(8336, 999, 3410),
                new Point(8336, 820, 3410),
                new Point(8336, 820, 3229),
            );
            $add(
                new Point(8336, 999, 3410),
                new Point(8074, 999, 3410),
                new Point(8074, 820, 3410),
                new Point(8336, 820, 3410),
            );

            // prop.076
            $add(
                new Point(9762, 935, 3800),
                new Point(9643, 935, 4229),
                new Point(9643, 813, 4229),
                new Point(9762, 813, 3800),
            );
            $add(
                new Point(9643, 935, 4229),
                new Point(9851, 935, 4281),
                new Point(9851, 813, 4281),
                new Point(9643, 813, 4229),
            );
            $add(
                new Point(9851, 935, 4281),
                new Point(9956, 935, 3850),
                new Point(9956, 813, 3850),
                new Point(9851, 813, 4281),
            );
            $add(
                new Point(9956, 935, 3850),
                new Point(9762, 935, 3800),
                new Point(9762, 813, 3800),
                new Point(9956, 813, 3850),
            );
            $add(
                new Point(9708, 935, 4238),
                new Point(9708, 935, 4123),
                new Point(9830, 935, 4123),
                new Point(9830, 935, 4238),
            );
            $add(
                new Point(9736, 981, 4085),
                new Point(9913, 981, 4085),
                new Point(9913, 981, 3887),
                new Point(9736, 981, 3887),
            );
            $add(
                new Point(9736, 981, 3887),
                new Point(9913, 981, 3887),
                new Point(9913, 893, 3887),
                new Point(9736, 893, 3887),
            );
            $add(
                new Point(9736, 981, 4085),
                new Point(9736, 981, 3887),
                new Point(9736, 893, 3887),
                new Point(9736, 893, 4085),
            );
            $add(
                new Point(9913, 981, 4085),
                new Point(9736, 981, 4085),
                new Point(9736, 893, 4085),
                new Point(9913, 893, 4085),
            );
            $add(
                new Point(9913, 981, 3887),
                new Point(9913, 981, 4085),
                new Point(9913, 893, 4085),
                new Point(9913, 893, 3887),
            );

            // prop.077
            $add(
                new Point(7580, 708, 11054),
                new Point(7784, 708, 11258),
                new Point(7784, 411, 11258),
                new Point(7580, 411, 11054),
            );
            $add(
                new Point(7784, 708, 11258),
                new Point(7989, 708, 11052),
                new Point(7989, 411, 11052),
                new Point(7784, 411, 11258),
            );
            $add(
                new Point(7989, 708, 11052),
                new Point(7786, 708, 10848),
                new Point(7786, 411, 10848),
                new Point(7989, 411, 11052),
            );
            $add(
                new Point(7786, 708, 10848),
                new Point(7580, 708, 11054),
                new Point(7580, 411, 11054),
                new Point(7786, 411, 10848),
            );
            $add(
                new Point(7671, 708, 11159),
                new Point(7671, 708, 10962),
                new Point(7882, 708, 10962),
                new Point(7882, 708, 11159),
            );

            // stairs
            $add(
                new Point(12605, 995, 7153),
                new Point(12838, 995, 7153),
                new Point(12805, 1043, 7218),
                new Point(12637, 1043, 7218),
            );

            // stairs.001
            $add(
                new Point(12922, 992, 5495),
                new Point(12922, 992, 5262),
                new Point(12963, 1040, 5295),
                new Point(12963, 1040, 5463),
            );

            // stairs.002
            $add(
                new Point(12583, 995, 6633),
                new Point(12583, 995, 7029),
                new Point(12397, 797, 7082),
                new Point(12397, 797, 6580),
            );

            // stairs.003
            $add(
                new Point(2138, 899, 11461),
                new Point(1740, 899, 11461),
                new Point(1685, 806, 11312),
                new Point(2193, 806, 11312),
            );

            // stairs.005
            $add(
                new Point(3065, 899, 5774),
                new Point(2334, 899, 5774),
                new Point(2308, 803, 5667),
                new Point(3091, 803, 5667),
            );
        }

        // Map - T spawn
        if (true) {

            // spawn to long doors walls
            $add(
                new Point(9992, 1603, 2992),
                new Point(8841, 1603, 2992),
                new Point(8841, 769, 2992),
                new Point(9992, 769, 2992),
            );
            $add(
                new Point(9992, 1603, 2992),
                new Point(9992, 769, 2992),
                new Point(9992, 769, 4911),
                new Point(9992, 1603, 4911),
            );
            $add(
                new Point(9992, 1603, 4911),
                new Point(9992, 769, 4911),
                new Point(9897, 769, 4911),
                new Point(9897, 1603, 4911),
            );
            $add(
                new Point(8841, 769, 2992),
                new Point(8841, 1603, 2992),
                new Point(8841, 1603, 1457),
                new Point(8841, 769, 1457),
            );
            $add(
                new Point(8841, 769, 1457),
                new Point(8841, 1603, 1457),
                new Point(7498, 1603, 1457),
                new Point(7498, 769, 1457),
            );
            $add(
                new Point(7498, 769, 1457),
                new Point(7498, 1603, 1457),
                new Point(7306, 1603, 1265),
                new Point(7306, 769, 1265),
            );
            $add(
                new Point(7306, 769, 1265),
                new Point(7306, 1603, 1265),
                new Point(7306, 1603, 1075),
                new Point(7306, 769, 1075),
            );
            $add(
                new Point(9897, 1603, 4911),
                new Point(9897, 769, 4911),
                new Point(9897, 769, 5006),
                new Point(9897, 1603, 5006),
            );
            $add(
                new Point(9897, 1603, 5006),
                new Point(9897, 769, 5006),
                new Point(10002, 769, 5006),
                new Point(10002, 1603, 5006),
            );
            $add(
                new Point(9992, 769, 4911),
                new Point(9992, 1603, 4911),
                new Point(9989, 1603, 6023),
                new Point(9989, 769, 6023),
            );

            // spawn wall
            $add(
                new Point(7351, 1975, 1097),
                new Point(1812, 1975, 1097),
                new Point(1812, 911, 1097),
                new Point(7351, 911, 1097),
            );

            // t awp stairs
            $add(
                new Point(7270, 996, 2320),
                new Point(7341, 996, 2320),
                new Point(7341, 996, 2224),
                new Point(7270, 996, 2224),
            );
            $add(
                new Point(7411, 952, 2320),
                new Point(7482, 952, 2320),
                new Point(7482, 952, 2224),
                new Point(7411, 952, 2224),
            );
            $add(
                new Point(7482, 812, 2320),
                new Point(7482, 952, 2320),
                new Point(7411, 952, 2320),
                new Point(7411, 812, 2320),
            );
            $add(
                new Point(7690, 812, 2320),
                new Point(7690, 901, 2320),
                new Point(7622, 901, 2320),
                new Point(7622, 812, 2320),
            );
            $add(
                new Point(6355, 1238, 2224),
                new Point(6355, 1238, 2320),
                new Point(6355, 1190, 2320),
                new Point(6355, 1190, 2224),
            );
            $add(
                new Point(7270, 812, 2320),
                new Point(7270, 1009, 2320),
                new Point(7200, 1009, 2320),
                new Point(7200, 812, 2320),
            );
            $add(
                new Point(6777, 812, 2320),
                new Point(6777, 1141, 2320),
                new Point(6707, 1141, 2320),
                new Point(6707, 812, 2320),
            );
            $add(
                new Point(6636, 812, 2320),
                new Point(6636, 1167, 2320),
                new Point(6566, 1167, 2320),
                new Point(6566, 812, 2320),
            );
            $add(
                new Point(6355, 812, 2320),
                new Point(6355, 1238, 2320),
                new Point(6284, 1238, 2320),
                new Point(6284, 812, 2320),
            );
            $add(
                new Point(7622, 812, 2320),
                new Point(7622, 921, 2320),
                new Point(7552, 921, 2320),
                new Point(7552, 812, 2320),
            );
            $add(
                new Point(6495, 812, 2320),
                new Point(6495, 1203, 2320),
                new Point(6425, 1203, 2320),
                new Point(6425, 812, 2320),
            );
            $add(
                new Point(7552, 812, 2320),
                new Point(7552, 938, 2320),
                new Point(7482, 938, 2320),
                new Point(7482, 812, 2320),
            );
            $add(
                new Point(7482, 938, 2320),
                new Point(7552, 938, 2320),
                new Point(7552, 938, 2224),
                new Point(7482, 938, 2224),
            );
            $add(
                new Point(7059, 1051, 2320),
                new Point(7129, 1051, 2320),
                new Point(7129, 1051, 2224),
                new Point(7059, 1051, 2224),
            );
            $add(
                new Point(7622, 901, 2320),
                new Point(7690, 901, 2320),
                new Point(7690, 901, 2224),
                new Point(7622, 901, 2224),
            );
            $add(
                new Point(6918, 1088, 2320),
                new Point(6989, 1088, 2320),
                new Point(6989, 1088, 2224),
                new Point(6918, 1088, 2224),
            );
            $add(
                new Point(7341, 973, 2320),
                new Point(7411, 973, 2320),
                new Point(7411, 973, 2224),
                new Point(7341, 973, 2224),
            );
            $add(
                new Point(6989, 812, 2320),
                new Point(6989, 1088, 2320),
                new Point(6918, 1088, 2320),
                new Point(6918, 812, 2320),
            );
            $add(
                new Point(7552, 921, 2320),
                new Point(7622, 921, 2320),
                new Point(7622, 921, 2224),
                new Point(7552, 921, 2224),
            );
            $add(
                new Point(6707, 812, 2320),
                new Point(6707, 1153, 2320),
                new Point(6636, 1153, 2320),
                new Point(6636, 812, 2320),
            );
            $add(
                new Point(6214, 1256, 2320),
                new Point(6284, 1256, 2320),
                new Point(6284, 1256, 2224),
                new Point(6214, 1256, 2224),
            );
            $add(
                new Point(7200, 1009, 2320),
                new Point(7270, 1009, 2320),
                new Point(7270, 1009, 2224),
                new Point(7200, 1009, 2224),
            );
            $add(
                new Point(7059, 1051, 2224),
                new Point(7129, 1051, 2224),
                new Point(7129, 896, 2224),
                new Point(7059, 896, 2224),
            );
            $add(
                new Point(7341, 973, 2224),
                new Point(7411, 973, 2224),
                new Point(7411, 824, 2224),
                new Point(7341, 824, 2224),
            );
            $add(
                new Point(6707, 1153, 2224),
                new Point(6707, 1153, 2320),
                new Point(6707, 1066, 2320),
                new Point(6707, 1066, 2224),
            );
            $add(
                new Point(7482, 938, 2224),
                new Point(7552, 938, 2224),
                new Point(7552, 783, 2224),
                new Point(7482, 783, 2224),
            );
            $add(
                new Point(6214, 1256, 2224),
                new Point(6284, 1256, 2224),
                new Point(6284, 1113, 2224),
                new Point(6214, 1113, 2224),
            );
            $add(
                new Point(7200, 1009, 2224),
                new Point(7270, 1009, 2224),
                new Point(7270, 864, 2224),
                new Point(7200, 864, 2224),
            );
            $add(
                new Point(7552, 921, 2224),
                new Point(7622, 921, 2224),
                new Point(7622, 766, 2224),
                new Point(7552, 766, 2224),
            );
            $add(
                new Point(7411, 952, 2224),
                new Point(7482, 952, 2224),
                new Point(7482, 797, 2224),
                new Point(7411, 797, 2224),
            );
            $add(
                new Point(6918, 1088, 2224),
                new Point(6989, 1088, 2224),
                new Point(6989, 934, 2224),
                new Point(6918, 934, 2224),
            );
            $add(
                new Point(7622, 901, 2224),
                new Point(7690, 901, 2224),
                new Point(7690, 746, 2224),
                new Point(7622, 746, 2224),
            );
            $add(
                new Point(6989, 1088, 2224),
                new Point(6989, 1088, 2320),
                new Point(6989, 988, 2320),
                new Point(6989, 988, 2224),
            );
            $add(
                new Point(6636, 1153, 2224),
                new Point(6707, 1153, 2224),
                new Point(6707, 1012, 2224),
                new Point(6636, 1012, 2224),
            );
            $add(
                new Point(7129, 1051, 2224),
                new Point(7129, 1051, 2320),
                new Point(7129, 950, 2320),
                new Point(7129, 950, 2224),
            );
            $add(
                new Point(7411, 973, 2224),
                new Point(7411, 973, 2320),
                new Point(7411, 878, 2320),
                new Point(7411, 878, 2224),
            );
            $add(
                new Point(7690, 901, 2224),
                new Point(7690, 901, 2320),
                new Point(7690, 800, 2320),
                new Point(7690, 800, 2224),
            );
            $add(
                new Point(6848, 1123, 2224),
                new Point(6848, 1123, 2320),
                new Point(6848, 1022, 2320),
                new Point(6848, 1022, 2224),
            );
            $add(
                new Point(6284, 1256, 2224),
                new Point(6284, 1256, 2320),
                new Point(6284, 1168, 2320),
                new Point(6284, 1168, 2224),
            );
            $add(
                new Point(7482, 952, 2224),
                new Point(7482, 952, 2320),
                new Point(7482, 851, 2320),
                new Point(7482, 851, 2224),
            );
            $add(
                new Point(7622, 921, 2224),
                new Point(7622, 921, 2320),
                new Point(7622, 820, 2320),
                new Point(7622, 820, 2224),
            );
            $add(
                new Point(7270, 1009, 2224),
                new Point(7270, 1009, 2320),
                new Point(7270, 918, 2320),
                new Point(7270, 918, 2224),
            );
            $add(
                new Point(7552, 938, 2224),
                new Point(7552, 938, 2320),
                new Point(7552, 837, 2320),
                new Point(7552, 837, 2224),
            );
            $add(
                new Point(6707, 1141, 2224),
                new Point(6777, 1141, 2224),
                new Point(6777, 975, 2224),
                new Point(6707, 975, 2224),
            );
            $add(
                new Point(6707, 1141, 2320),
                new Point(6777, 1141, 2320),
                new Point(6777, 1141, 2224),
                new Point(6707, 1141, 2224),
            );
            $add(
                new Point(6777, 1123, 2320),
                new Point(6848, 1123, 2320),
                new Point(6848, 1123, 2224),
                new Point(6777, 1123, 2224),
            );
            $add(
                new Point(6777, 1123, 2224),
                new Point(6848, 1123, 2224),
                new Point(6848, 975, 2224),
                new Point(6777, 975, 2224),
            );
            $add(
                new Point(6848, 812, 2320),
                new Point(6848, 1123, 2320),
                new Point(6777, 1123, 2320),
                new Point(6777, 812, 2320),
            );
            $add(
                new Point(7270, 996, 2224),
                new Point(7341, 996, 2224),
                new Point(7341, 862, 2224),
                new Point(7270, 862, 2224),
            );
            $add(
                new Point(7341, 812, 2320),
                new Point(7341, 996, 2320),
                new Point(7270, 996, 2320),
                new Point(7270, 812, 2320),
            );
            $add(
                new Point(7129, 812, 2320),
                new Point(7129, 1051, 2320),
                new Point(7059, 1051, 2320),
                new Point(7059, 812, 2320),
            );
            $add(
                new Point(6566, 1167, 2224),
                new Point(6636, 1167, 2224),
                new Point(6636, 1012, 2224),
                new Point(6566, 1012, 2224),
            );
            $add(
                new Point(6566, 1167, 2320),
                new Point(6636, 1167, 2320),
                new Point(6636, 1167, 2224),
                new Point(6566, 1167, 2224),
            );
            $add(
                new Point(6425, 1203, 2224),
                new Point(6495, 1203, 2224),
                new Point(6495, 1048, 2224),
                new Point(6425, 1048, 2224),
            );
            $add(
                new Point(6425, 1203, 2320),
                new Point(6495, 1203, 2320),
                new Point(6495, 1203, 2224),
                new Point(6425, 1203, 2224),
            );
            $add(
                new Point(6636, 1153, 2320),
                new Point(6707, 1153, 2320),
                new Point(6707, 1153, 2224),
                new Point(6636, 1153, 2224),
            );
            $add(
                new Point(6284, 1238, 2224),
                new Point(6355, 1238, 2224),
                new Point(6355, 1083, 2224),
                new Point(6284, 1083, 2224),
            );
            $add(
                new Point(6284, 1238, 2320),
                new Point(6355, 1238, 2320),
                new Point(6355, 1238, 2224),
                new Point(6284, 1238, 2224),
            );
            $add(
                new Point(6284, 812, 2320),
                new Point(6284, 1256, 2320),
                new Point(6214, 1256, 2320),
                new Point(6214, 812, 2320),
            );
            $add(
                new Point(6214, 812, 2320),
                new Point(6214, 1269, 2320),
                new Point(6144, 1269, 2320),
                new Point(6144, 812, 2320),
            );
            $add(
                new Point(6144, 1269, 2224),
                new Point(6214, 1269, 2224),
                new Point(6214, 1113, 2224),
                new Point(6144, 1113, 2224),
            );
            $add(
                new Point(6144, 1269, 2320),
                new Point(6214, 1269, 2320),
                new Point(6214, 1269, 2224),
                new Point(6144, 1269, 2224),
            );
            $add(
                new Point(6425, 812, 2320),
                new Point(6425, 1222, 2320),
                new Point(6355, 1222, 2320),
                new Point(6355, 812, 2320),
            );
            $add(
                new Point(6425, 1222, 2224),
                new Point(6425, 1222, 2320),
                new Point(6425, 1122, 2320),
                new Point(6425, 1122, 2224),
            );
            $add(
                new Point(6355, 1222, 2224),
                new Point(6425, 1222, 2224),
                new Point(6425, 1083, 2224),
                new Point(6355, 1083, 2224),
            );
            $add(
                new Point(6355, 1222, 2320),
                new Point(6425, 1222, 2320),
                new Point(6425, 1222, 2224),
                new Point(6355, 1222, 2224),
            );
            $add(
                new Point(6495, 1185, 2320),
                new Point(6566, 1185, 2320),
                new Point(6566, 1185, 2224),
                new Point(6495, 1185, 2224),
            );
            $add(
                new Point(6495, 1185, 2224),
                new Point(6566, 1185, 2224),
                new Point(6566, 1048, 2224),
                new Point(6495, 1048, 2224),
            );
            $add(
                new Point(6566, 1185, 2224),
                new Point(6566, 1185, 2320),
                new Point(6566, 1102, 2320),
                new Point(6566, 1102, 2224),
            );
            $add(
                new Point(6566, 812, 2320),
                new Point(6566, 1185, 2320),
                new Point(6495, 1185, 2320),
                new Point(6495, 812, 2320),
            );
            $add(
                new Point(6918, 812, 2320),
                new Point(6918, 1104, 2320),
                new Point(6848, 1104, 2320),
                new Point(6848, 812, 2320),
            );
            $add(
                new Point(6848, 1104, 2320),
                new Point(6918, 1104, 2320),
                new Point(6918, 1104, 2224),
                new Point(6848, 1104, 2224),
            );
            $add(
                new Point(6848, 1104, 2224),
                new Point(6918, 1104, 2224),
                new Point(6918, 934, 2224),
                new Point(6848, 934, 2224),
            );
            $add(
                new Point(7059, 812, 2320),
                new Point(7059, 1069, 2320),
                new Point(6989, 1069, 2320),
                new Point(6989, 812, 2320),
            );
            $add(
                new Point(6989, 1069, 2224),
                new Point(7059, 1069, 2224),
                new Point(7059, 896, 2224),
                new Point(6989, 896, 2224),
            );
            $add(
                new Point(6989, 1069, 2320),
                new Point(7059, 1069, 2320),
                new Point(7059, 1069, 2224),
                new Point(6989, 1069, 2224),
            );
            $add(
                new Point(6495, 1203, 2224),
                new Point(6495, 1203, 2320),
                new Point(6495, 1154, 2320),
                new Point(6495, 1154, 2224),
            );
            $add(
                new Point(6636, 1167, 2224),
                new Point(6636, 1167, 2320),
                new Point(6636, 1118, 2320),
                new Point(6636, 1118, 2224),
            );
            $add(
                new Point(7411, 812, 2320),
                new Point(7411, 973, 2320),
                new Point(7341, 973, 2320),
                new Point(7341, 812, 2320),
            );
            $add(
                new Point(7200, 812, 2320),
                new Point(7200, 1028, 2320),
                new Point(7129, 1028, 2320),
                new Point(7129, 812, 2320),
            );
            $add(
                new Point(7129, 1028, 2320),
                new Point(7200, 1028, 2320),
                new Point(7200, 1028, 2224),
                new Point(7129, 1028, 2224),
            );
            $add(
                new Point(7129, 1028, 2224),
                new Point(7200, 1028, 2224),
                new Point(7200, 864, 2224),
                new Point(7129, 864, 2224),
            );
            $add(
                new Point(7341, 996, 2224),
                new Point(7341, 996, 2320),
                new Point(7341, 938, 2320),
                new Point(7341, 938, 2224),
            );
            $add(
                new Point(6918, 1104, 2224),
                new Point(6918, 1104, 2320),
                new Point(6918, 1058, 2320),
                new Point(6918, 1058, 2224),
            );
            $add(
                new Point(6777, 1141, 2224),
                new Point(6777, 1141, 2320),
                new Point(6777, 1093, 2320),
                new Point(6777, 1093, 2224),
            );
            $add(
                new Point(7059, 1069, 2224),
                new Point(7059, 1069, 2320),
                new Point(7059, 1020, 2320),
                new Point(7059, 1020, 2224),
            );
            $add(
                new Point(7200, 1028, 2224),
                new Point(7200, 1028, 2320),
                new Point(7200, 976, 2320),
                new Point(7200, 976, 2224),
            );
            $add(
                new Point(6214, 1269, 2224),
                new Point(6214, 1269, 2320),
                new Point(6214, 1220, 2320),
                new Point(6214, 1220, 2224),
            );

            // t floor mud
            $add(
                new Point(6144, 1294, 1099),
                new Point(1063, 1294, 1099),
                new Point(1021, 1198, 1465),
                new Point(6159, 1198, 1465),
            );
            $add(
                new Point(6154, 1198, 1465),
                new Point(7615, 838, 1627),
                new Point(7306, 1038, 1099),
                null,
            );
            $add(
                new Point(6154, 1198, 1465),
                new Point(7306, 1038, 1099),
                new Point(6141, 1294, 1099),
                null,
            );
            $add(
                new Point(7496, 834, 1627),
                new Point(7483, 896, 1457),
                new Point(8828, 896, 1457),
                new Point(8841, 834, 1627),
            );

            // t ramp
            $add(
                new Point(2317, 806, 2224),
                new Point(2317, 1283, 2224),
                new Point(2317, 1283, 3759),
                new Point(2317, 806, 3759),
            );
            $add(
                new Point(2317, 806, 3759),
                new Point(2317, 1283, 3759),
                new Point(3526, 1283, 3759),
                new Point(3526, 806, 3759),
            );
            $add(
                new Point(3526, 1283, 3759),
                new Point(2317, 1283, 3759),
                new Point(2317, 1283, 3711),
                new Point(3526, 1283, 3711),
            );
            $add(
                new Point(2317, 1283, 3759),
                new Point(2317, 1283, 2224),
                new Point(2364, 1283, 2224),
                new Point(2364, 1283, 3759),
            );
            $add(
                new Point(2317, 1283, 2224),
                new Point(2317, 806, 2224),
                new Point(2369, 806, 2224),
                new Point(2364, 1283, 2224),
            );
            $add(
                new Point(2364, 1283, 3759),
                new Point(2364, 1283, 2224),
                new Point(2364, 1170, 2224),
                new Point(2364, 1170, 3759),
            );
            $add(
                new Point(3526, 1283, 3711),
                new Point(2317, 1283, 3711),
                new Point(2317, 1170, 3711),
                new Point(3526, 1170, 3711),
            );

            // t spawn walls
            $add(
                new Point(6154, 1911, 2224),
                new Point(5003, 1911, 2224),
                new Point(5003, 1192, 2224),
                new Point(6154, 1192, 2224),
            );
            $add(
                new Point(5003, 1192, 2224),
                new Point(5003, 1911, 2224),
                new Point(5003, 1911, 2032),
                new Point(5003, 1192, 2032),
            );
            $add(
                new Point(5003, 1192, 2032),
                new Point(5003, 1911, 2032),
                new Point(4811, 1911, 2032),
                new Point(4811, 1192, 2032),
            );
            $add(
                new Point(4811, 1192, 2032),
                new Point(4811, 1911, 2032),
                new Point(4811, 1911, 3663),
                new Point(4811, 1192, 3663),
            );
            $add(
                new Point(4811, 1192, 3663),
                new Point(4811, 1911, 3663),
                new Point(4235, 1911, 3663),
                new Point(4235, 1192, 3663),
            );
            $add(
                new Point(4235, 1192, 3663),
                new Point(4235, 1911, 3663),
                new Point(4235, 1911, 3567),
                new Point(4235, 1192, 3567),
            );
            $add(
                new Point(4235, 1192, 3567),
                new Point(4235, 1911, 3567),
                new Point(4044, 1911, 3376),
                new Point(4044, 1192, 3376),
            );
            $add(
                new Point(4044, 1192, 3376),
                new Point(4044, 1911, 3376),
                new Point(3660, 1911, 3376),
                new Point(3660, 1192, 3376),
            );
            $add(
                new Point(3660, 1192, 3376),
                new Point(3660, 1911, 3376),
                new Point(3468, 1911, 3567),
                new Point(3468, 1192, 3567),
            );
            $add(
                new Point(3468, 1192, 3567),
                new Point(3468, 1911, 3567),
                new Point(3468, 1911, 3750),
                new Point(3468, 1192, 3750),
            );
            $add(
                new Point(6154, 1911, 2224),
                new Point(6154, 1192, 2224),
                new Point(6154, 1192, 2294),
                new Point(6154, 1911, 2294),
            );
            $add(
                new Point(3468, 1192, 3750),
                new Point(3468, 1911, 3750),
                new Point(3510, 1911, 3750),
                new Point(3510, 1192, 3750),
            );

            // t spawn walls.001
            $add(
                new Point(5867, 1210, 1068),
                new Point(5867, 1210, 1222),
                new Point(5867, 1960, 1222),
                new Point(5867, 1960, 1068),
            );
            $add(
                new Point(5867, 1960, 1222),
                new Point(5867, 1210, 1222),
                new Point(5003, 1210, 1222),
                new Point(5003, 1960, 1222),
            );
            $add(
                new Point(5003, 1960, 1222),
                new Point(5003, 1210, 1222),
                new Point(5003, 1210, 1361),
                new Point(5003, 1960, 1361),
            );
            $add(
                new Point(4811, 1960, 1361),
                new Point(4811, 1210, 1361),
                new Point(4811, 1210, 1080),
                new Point(4811, 1960, 1080),
            );
            $add(
                new Point(5003, 1960, 1368),
                new Point(5003, 1780, 1368),
                new Point(5003, 1780, 2062),
                new Point(5003, 1960, 2062),
            );
            $add(
                new Point(4811, 1780, 1368),
                new Point(4811, 1960, 1368),
                new Point(4811, 1960, 2062),
                new Point(4811, 1780, 2062),
            );
            $add(
                new Point(4811, 1781, 1327),
                new Point(5003, 1781, 1327),
                new Point(5003, 1781, 2044),
                new Point(4811, 1781, 2044),
            );
            $add(
                new Point(5003, 1960, 1361),
                new Point(5003, 1210, 1361),
                new Point(4811, 1210, 1361),
                new Point(4811, 1960, 1361),
            );

            // t spawn walls.002
            $add(
                new Point(4235, 1917, 1217),
                new Point(3372, 1917, 1217),
                new Point(3372, 1226, 1217),
                new Point(4235, 1226, 1217),
            );
            $add(
                new Point(4235, 1917, 1217),
                new Point(4235, 1226, 1217),
                new Point(4235, 1226, 1063),
                new Point(4235, 1917, 1063),
            );
            $add(
                new Point(3372, 1226, 1217),
                new Point(3372, 1917, 1217),
                new Point(3372, 1917, 1072),
                new Point(3372, 1226, 1072),
            );

            // t spawn walls.003
            $add(
                new Point(1165, 1079, 1313),
                new Point(1165, 1975, 1313),
                new Point(1165, 1975, 1409),
                new Point(1165, 1079, 1409),
            );
            $add(
                new Point(1165, 1079, 2080),
                new Point(1165, 1975, 2080),
                new Point(1453, 1975, 2080),
                new Point(1453, 1079, 2080),
            );
            $add(
                new Point(1165, 1975, 1313),
                new Point(1165, 1079, 1313),
                new Point(1669, 1079, 1313),
                new Point(1669, 1975, 1313),
            );
            $add(
                new Point(1669, 1975, 1313),
                new Point(1669, 1079, 1313),
                new Point(1669, 1079, 1145),
                new Point(1669, 1975, 1145),
            );
            $add(
                new Point(1669, 1975, 1145),
                new Point(1669, 1079, 1145),
                new Point(2293, 1079, 1145),
                new Point(2293, 1975, 1145),
            );
            $add(
                new Point(2293, 1975, 1145),
                new Point(2293, 1079, 1145),
                new Point(2293, 1079, 1084),
                new Point(2293, 1975, 1084),
            );
            $add(
                new Point(1165, 1079, 1409),
                new Point(1165, 1975, 1409),
                new Point(1069, 1975, 1409),
                new Point(1069, 1079, 1409),
            );
            $add(
                new Point(1165, 1079, 1984),
                new Point(1165, 1975, 1984),
                new Point(1165, 1975, 2080),
                new Point(1165, 1079, 2080),
            );
            $add(
                new Point(1069, 1079, 1409),
                new Point(1069, 1975, 1409),
                new Point(1069, 1975, 1984),
                new Point(1069, 1079, 1984),
            );
            $add(
                new Point(1165, 1975, 1984),
                new Point(1165, 1079, 1984),
                new Point(1069, 1079, 1984),
                new Point(1069, 1975, 1984),
            );

            // t-spawn main floor
            $add(
                new Point(6178, 1198, 2283),
                new Point(6178, 1198, 1022),
                new Point(7686, 834, 1022),
                new Point(7686, 834, 2283),
            );
            $add(
                new Point(7686, 834, 2283),
                new Point(7686, 834, 1022),
                new Point(8915, 834, 1022),
                new Point(8915, 834, 2283),
            );
            $add(
                new Point(1044, 1196, 2283),
                new Point(2374, 1196, 2283),
                new Point(2374, 808, 3826),
                new Point(1044, 808, 3826),
            );
            $add(
                new Point(2315, 1193, 2283),
                new Point(4860, 1193, 2283),
                new Point(4860, 1193, 3711),
                new Point(2315, 1193, 3711),
            );
            $add(
                new Point(6178, 1198, 2283),
                new Point(1044, 1198, 2283),
                new Point(1044, 1198, 1022),
                new Point(6178, 1198, 1022),
            );
            $add(
                new Point(7686, 834, 2283),
                new Point(8915, 834, 2283),
                new Point(8837, 808, 2283),
                new Point(7686, 808, 2283),
            );
            $add(
                new Point(1044, 1198, 2283),
                new Point(6178, 1198, 2283),
                new Point(6178, 1178, 2283),
                new Point(1044, 1178, 2283),
            );
        }

        // Map - Upper Tunnel
        if (true) {

            // upper tunnel main floor
            $add(
                new Point(3858, 899, 5774),
                new Point(3858, 899, 8687),
                new Point(1031, 899, 8687),
                new Point(1031, 899, 5774),
            );
            $add(
                new Point(3955, 824, 5829),
                new Point(1477, 824, 5829),
                new Point(1477, 824, 3726),
                new Point(3955, 824, 3726),
            );
            $add(
                new Point(1551, 899, 8564),
                new Point(1936, 899, 8564),
                new Point(1936, 899, 9484),
                new Point(1551, 899, 9484),
            );
            $add(
                new Point(3858, 899, 8687),
                new Point(3858, 899, 5774),
                new Point(3858, 406, 5760),
                new Point(3858, 406, 8687),
            );
            $add(
                new Point(3858, 899, 5774),
                new Point(1031, 899, 5774),
                new Point(1031, 753, 5774),
                new Point(3858, 753, 5774),
            );

            // upper tunnel main floor.001
            $add(
                new Point(1551, 899, 9484),
                new Point(1936, 899, 9484),
                new Point(1989, 818, 9588),
                new Point(1499, 818, 9588),
            );

            // upper tunnel walls
            $add(
                new Point(1933, 1445, 8365),
                new Point(1933, 1445, 9516),
                new Point(1933, 886, 9516),
                new Point(1933, 886, 8365),
            );
            $add(
                new Point(1933, 1445, 8365),
                new Point(1933, 886, 8365),
                new Point(2700, 886, 8365),
                new Point(2700, 1445, 8365),
            );
            $add(
                new Point(2700, 1445, 8365),
                new Point(2700, 886, 8365),
                new Point(2700, 886, 8173),
                new Point(2700, 1445, 8173),
            );
            $add(
                new Point(2700, 1445, 8173),
                new Point(2700, 886, 8173),
                new Point(2727, 886, 8077),
                new Point(2727, 1445, 8077),
            );
            $add(
                new Point(2727, 1445, 8077),
                new Point(2727, 886, 8077),
                new Point(2796, 886, 8008),
                new Point(2796, 1445, 8008),
            );
            $add(
                new Point(2796, 1445, 8008),
                new Point(2796, 886, 8008),
                new Point(2890, 886, 7981),
                new Point(2890, 1445, 7981),
            );
            $add(
                new Point(2890, 1445, 7981),
                new Point(2890, 886, 7981),
                new Point(4616, 886, 7981),
                new Point(4616, 1445, 7981),
            );

            // upper tunnel walls.001
            $add(
                new Point(2892, 1477, 6254),
                new Point(2892, 889, 6254),
                new Point(3500, 889, 6254),
                new Point(3500, 1477, 6254),
            );
            $add(
                new Point(2506, 1477, 6254),
                new Point(2506, 1317, 6254),
                new Point(2897, 1317, 6254),
                new Point(2897, 1477, 6254),
            );
            $add(
                new Point(1918, 1477, 6254),
                new Point(1918, 859, 6254),
                new Point(2508, 859, 6254),
                new Point(2508, 1477, 6254),
            );
            $add(
                new Point(2508, 1477, 6254),
                new Point(2508, 859, 6254),
                new Point(2508, 859, 7022),
                new Point(2508, 1477, 7022),
            );
            $add(
                new Point(2892, 889, 6254),
                new Point(2892, 1477, 6254),
                new Point(2892, 1477, 7022),
                new Point(2892, 889, 7022),
            );
            $add(
                new Point(2897, 1317, 6254),
                new Point(2506, 1317, 6254),
                new Point(2506, 1317, 7032),
                new Point(2897, 1317, 7032),
            );
            $add(
                new Point(2892, 889, 7022),
                new Point(2892, 1477, 7022),
                new Point(3084, 1477, 7022),
                new Point(3084, 889, 7022),
            );
            $add(
                new Point(3084, 889, 7022),
                new Point(3084, 1477, 7022),
                new Point(3084, 1477, 7214),
                new Point(3084, 889, 7214),
            );
            $add(
                new Point(3084, 889, 7214),
                new Point(3084, 1477, 7214),
                new Point(3856, 1477, 7214),
                new Point(3856, 889, 7214),
            );
            $add(
                new Point(2508, 1477, 7022),
                new Point(2508, 859, 7022),
                new Point(2317, 859, 7022),
                new Point(2317, 1477, 7022),
            );
            $add(
                new Point(2317, 1477, 7022),
                new Point(2317, 859, 7022),
                new Point(2293, 859, 7118),
                new Point(2293, 1477, 7118),
            );
            $add(
                new Point(2293, 1477, 7118),
                new Point(2293, 859, 7118),
                new Point(2221, 859, 7190),
                new Point(2221, 1477, 7190),
            );
            $add(
                new Point(2221, 1477, 7190),
                new Point(2221, 859, 7190),
                new Point(2127, 859, 7214),
                new Point(2127, 1477, 7214),
            );
            $add(
                new Point(2127, 1477, 7214),
                new Point(2127, 859, 7214),
                new Point(1069, 859, 7214),
                new Point(1069, 1477, 7214),
            );
            $add(
                new Point(1069, 1477, 7214),
                new Point(1069, 859, 7214),
                new Point(1069, 859, 7981),
                new Point(1069, 1477, 7981),
            );
            $add(
                new Point(1069, 1477, 7981),
                new Point(1069, 859, 7981),
                new Point(1549, 859, 7981),
                new Point(1549, 1477, 7981),
            );
            $add(
                new Point(1549, 1477, 7981),
                new Point(1549, 859, 7981),
                new Point(1549, 859, 9516),
                new Point(1549, 1477, 9516),
            );
        }

        // Plant - Plant
        if (true) {
            $this->plantArea->add(new Box(new Point(10527, 1099, 11404), 309, 20, 557));
            $this->plantArea->add(new Box(new Point(10826, 1099, 11113), 647, 20, 848));
            $this->plantArea->add(new Box(new Point(2525, 813, 11557), 1120, 20, 1195));
        }

        // Spawn - Attackers
        if (true) {
            $this->spawnPositionAttacker[] = new Point(4498, 1198, 1775);
            $this->spawnPositionAttacker[] = new Point(5356, 1198, 1947);
            $this->spawnPositionAttacker[] = new Point(5947, 1198, 2021);
            $this->spawnPositionAttacker[] = new Point(5984, 1198, 1666);
            $this->spawnPositionAttacker[] = new Point(5621, 1198, 1467);
            $this->spawnPositionAttacker[] = new Point(5159, 1198, 1540);
            $this->spawnPositionAttacker[] = new Point(3853, 1198, 2112);
            $this->spawnPositionAttacker[] = new Point(4478, 1198, 1593);
            $this->spawnPositionAttacker[] = new Point(4332, 1198, 2032);
            $this->spawnPositionAttacker[] = new Point(3755, 1198, 1538);
        }

        // Spawn - Defenders
        if (true) {
            $this->spawnPositionDefender[] = new Point(9111, 440, 10531);
            $this->spawnPositionDefender[] = new Point(9115, 440, 10943);
            $this->spawnPositionDefender[] = new Point(8984, 440, 11325);
            $this->spawnPositionDefender[] = new Point(8705, 440, 11580);
            $this->spawnPositionDefender[] = new Point(8141, 440, 11556);
            $this->spawnPositionDefender[] = new Point(8275, 440, 11270);
            $this->spawnPositionDefender[] = new Point(8705, 440, 11029);
            $this->spawnPositionDefender[] = new Point(8378, 440, 10613);
            $this->spawnPositionDefender[] = new Point(7873, 440, 10696);
            $this->spawnPositionDefender[] = new Point(7845, 440, 10290);
        }

        // Store - Store
        if (true) {
            $this->buyAreaAttackers->add(new Box(new Point(3918, 667, 959), 4081, 1280, 1699));
            $this->buyAreaDefenders->add(new Box(new Point(6111, 423, 9974), 4292, 300, 1314));
        }

/** END DATA */
    }

    /** @param list<Plane> $planes */
    public function addPlanes(array $planes, bool $penetrable, bool $supportNavmesh): void
    {
        foreach ($planes as $plane) {
            $plane->setPenetrable($penetrable);
            if ($plane instanceof Wall) {
                $this->walls[] = $plane;
            } elseif ($plane instanceof Floor) {
                $plane->supportNavmesh = $supportNavmesh;
                $this->floors[] = $plane;
            }
        }
    }

    public function getPlantArea(): BoxGroup
    {
        return $this->plantArea;
    }

    public function getBuyArea(bool $forAttackers): BoxGroup
    {
        if ($forAttackers) {
            return $this->buyAreaAttackers;
        }

        return $this->buyAreaDefenders;
    }

    #[\Override]
    public function getWalls(): array
    {
        return $this->walls;
    }

    #[\Override]
    public function getFloors(): array
    {
        return $this->floors;
    }

}
