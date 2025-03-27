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
                new Point(11521, 835, 11158),
                new Point(12539, 835, 11158),
                new Point(12539, 1098, 12109),
                new Point(11521, 1098, 12109),
            );
            $add(
                new Point(11521, 1192, 12566),
                new Point(12539, 1192, 12566),
                new Point(12539, 1192, 13386),
                new Point(11521, 1192, 13386),
            );
            $add(
                new Point(11333, 835, 7887),
                new Point(12609, 835, 7887),
                new Point(12609, 835, 11158),
                new Point(11333, 835, 11158),
            );
            $add(
                new Point(11521, 1098, 12109),
                new Point(12539, 1098, 12109),
                new Point(12539, 1192, 12566),
                new Point(11521, 1192, 12566),
            );
            $add(
                new Point(11333, 835, 7887),
                new Point(11333, 835, 11158),
                new Point(11333, 814, 11158),
                new Point(11333, 814, 7887),
            );

            // a long walls
            $add(
                new Point(11436, 1688, 10202),
                new Point(10296, 1688, 10202),
                new Point(10296, 787, 10202),
                new Point(11436, 787, 10202),
            );
            $add(
                new Point(11436, 1688, 10202),
                new Point(11436, 787, 10202),
                new Point(11436, 787, 9611),
                new Point(11436, 1688, 9611),
            );
            $add(
                new Point(11436, 1688, 9611),
                new Point(11436, 787, 9611),
                new Point(11388, 787, 9611),
                new Point(11388, 1688, 9611),
            );
            $add(
                new Point(11388, 1688, 9611),
                new Point(11388, 787, 9611),
                new Point(11388, 787, 8762),
                new Point(11388, 1688, 8762),
            );
            $add(
                new Point(11388, 1688, 8762),
                new Point(11388, 787, 8762),
                new Point(11458, 787, 8762),
                new Point(11458, 1688, 8762),
            );
            $add(
                new Point(11458, 1688, 8762),
                new Point(11458, 787, 8762),
                new Point(11458, 787, 7774),
                new Point(11458, 1688, 7774),
            );
            $add(
                new Point(11458, 1528, 7774),
                new Point(11458, 787, 7774),
                new Point(9499, 787, 7774),
                new Point(9499, 1528, 7774),
            );
            $add(
                new Point(10381, 1775, 10295),
                new Point(9234, 1775, 10295),
                new Point(9234, 392, 10295),
                new Point(10381, 392, 10295),
            );
            $add(
                new Point(10381, 1775, 10295),
                new Point(10381, 392, 10295),
                new Point(10381, 392, 10186),
                new Point(10381, 1775, 10186),
            );

            // long doors
            $add(
                new Point(9913, 823, 6222),
                new Point(9913, 1480, 6222),
                new Point(9704, 1480, 6222),
                new Point(9704, 823, 6222),
            );
            $add(
                new Point(9704, 823, 6222),
                new Point(9704, 1480, 6222),
                new Point(9704, 1480, 5916),
                new Point(9704, 823, 5916),
            );
            $add(
                new Point(9704, 823, 5916),
                new Point(9704, 1480, 5916),
                new Point(10006, 1480, 5916),
                new Point(10006, 823, 5916),
            );
            $add(
                new Point(9910, 1480, 6454),
                new Point(9277, 1480, 6454),
                new Point(9277, 1280, 6454),
                new Point(9910, 1280, 6454),
            );
            $add(
                new Point(9415, 1480, 6454),
                new Point(9222, 1480, 6454),
                new Point(9222, 823, 6454),
                new Point(9415, 823, 6454),
            );
            $add(
                new Point(9266, 799, 5016),
                new Point(9266, 799, 7509),
                new Point(9266, 1481, 7509),
                new Point(9266, 1481, 5016),
            );
            $add(
                new Point(9266, 799, 5016),
                new Point(9266, 1481, 5016),
                new Point(9346, 1481, 5016),
                new Point(9346, 799, 5016),
            );
            $add(
                new Point(9659, 832, 7834),
                new Point(9246, 832, 7426),
                new Point(9246, 1514, 7426),
                new Point(9659, 1514, 7834),
            );

            // long walls
            $add(
                new Point(12466, 1924, 13336),
                new Point(10691, 1924, 13336),
                new Point(10691, 1139, 13336),
                new Point(12466, 1139, 13336),
            );
            $add(
                new Point(13084, 1614, 9518),
                new Point(12523, 1614, 9518),
                new Point(12523, 611, 9518),
                new Point(13084, 611, 9518),
            );
            $add(
                new Point(12478, 826, 10980),
                new Point(12478, 1620, 10980),
                new Point(13056, 1620, 10980),
                new Point(13056, 826, 10980),
            );
            $add(
                new Point(12379, 1121, 12454),
                new Point(12379, 1849, 12454),
                new Point(12545, 1849, 12454),
                new Point(12545, 1121, 12454),
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
                new Point(13056, 826, 9666),
                new Point(13056, 826, 10980),
                new Point(13056, 1620, 10980),
                new Point(13056, 1620, 9666),
            );
            $add(
                new Point(12478, 826, 10980),
                new Point(12478, 826, 12483),
                new Point(12478, 1620, 12483),
                new Point(12478, 1620, 10980),
            );
            $add(
                new Point(12379, 1121, 12454),
                new Point(12379, 1121, 13367),
                new Point(12379, 1849, 13367),
                new Point(12379, 1849, 12454),
            );
            $add(
                new Point(12523, 611, 9518),
                new Point(12523, 1614, 9518),
                new Point(12523, 1614, 8321),
                new Point(12523, 611, 8321),
            );
        }

        // Map - A Short
        if (true) {

            // a short main floor
            $add(
                new Point(8842, 1095, 9504),
                new Point(8442, 1095, 9504),
                new Point(8442, 811, 9084),
                new Point(8842, 811, 9084),
            );
            $add(
                new Point(9298, 818, 9592),
                new Point(6966, 818, 9592),
                new Point(6966, 818, 6403),
                new Point(9298, 818, 6403),
            );
            $add(
                new Point(6966, 818, 6403),
                new Point(6966, 818, 9592),
                new Point(6966, 406, 9592),
                new Point(6966, 406, 6403),
            );

            // a short main floor.001
            $add(
                new Point(8890, 1022, 9444),
                new Point(8890, 1022, 9384),
                new Point(8890, 812, 9384),
                new Point(8890, 812, 9444),
            );
            $add(
                new Point(8890, 994, 9384),
                new Point(8890, 994, 9324),
                new Point(8890, 812, 9324),
                new Point(8890, 812, 9384),
            );
            $add(
                new Point(8890, 964, 9324),
                new Point(8890, 964, 9264),
                new Point(8890, 812, 9264),
                new Point(8890, 812, 9324),
            );
            $add(
                new Point(8890, 934, 9264),
                new Point(8890, 934, 9204),
                new Point(8890, 812, 9204),
                new Point(8890, 812, 9264),
            );
            $add(
                new Point(8890, 901, 9204),
                new Point(8890, 901, 9136),
                new Point(8890, 812, 9136),
                new Point(8890, 812, 9204),
            );
            $add(
                new Point(8890, 1054, 9504),
                new Point(8890, 1054, 9444),
                new Point(8890, 812, 9444),
                new Point(8890, 812, 9504),
            );
            $add(
                new Point(8890, 934, 9204),
                new Point(8890, 934, 9264),
                new Point(8841, 934, 9264),
                new Point(8841, 934, 9204),
            );
            $add(
                new Point(8890, 1022, 9384),
                new Point(8890, 1022, 9444),
                new Point(8841, 1022, 9444),
                new Point(8841, 1022, 9384),
            );
            $add(
                new Point(8890, 994, 9324),
                new Point(8890, 994, 9384),
                new Point(8841, 994, 9384),
                new Point(8841, 994, 9324),
            );
            $add(
                new Point(8890, 1054, 9444),
                new Point(8890, 1054, 9504),
                new Point(8841, 1054, 9504),
                new Point(8841, 1054, 9444),
            );
            $add(
                new Point(8890, 901, 9136),
                new Point(8890, 901, 9204),
                new Point(8841, 901, 9204),
                new Point(8841, 901, 9136),
            );
            $add(
                new Point(8890, 964, 9264),
                new Point(8890, 964, 9324),
                new Point(8841, 964, 9324),
                new Point(8841, 964, 9264),
            );
            $add(
                new Point(8841, 1054, 9444),
                new Point(8841, 1054, 9504),
                new Point(8841, 812, 9504),
                new Point(8841, 812, 9444),
            );
            $add(
                new Point(8841, 934, 9204),
                new Point(8841, 934, 9264),
                new Point(8841, 812, 9264),
                new Point(8841, 812, 9204),
            );
            $add(
                new Point(8841, 994, 9324),
                new Point(8841, 994, 9384),
                new Point(8841, 812, 9384),
                new Point(8841, 812, 9324),
            );
            $add(
                new Point(8841, 1022, 9384),
                new Point(8841, 1022, 9444),
                new Point(8841, 812, 9444),
                new Point(8841, 812, 9384),
            );
            $add(
                new Point(8841, 901, 9136),
                new Point(8841, 901, 9204),
                new Point(8841, 812, 9204),
                new Point(8841, 812, 9136),
            );
            $add(
                new Point(8841, 964, 9264),
                new Point(8841, 964, 9324),
                new Point(8841, 812, 9324),
                new Point(8841, 812, 9264),
            );
            $add(
                new Point(8841, 901, 9136),
                new Point(8841, 812, 9136),
                new Point(8890, 812, 9136),
                new Point(8890, 901, 9136),
            );
            $add(
                new Point(8841, 934, 9204),
                new Point(8841, 812, 9204),
                new Point(8890, 812, 9204),
                new Point(8890, 934, 9204),
            );
            $add(
                new Point(8841, 964, 9264),
                new Point(8841, 812, 9264),
                new Point(8890, 812, 9264),
                new Point(8890, 964, 9264),
            );
            $add(
                new Point(8841, 994, 9324),
                new Point(8841, 812, 9324),
                new Point(8890, 812, 9324),
                new Point(8890, 994, 9324),
            );
            $add(
                new Point(8841, 1022, 9384),
                new Point(8841, 812, 9384),
                new Point(8890, 812, 9384),
                new Point(8890, 1022, 9384),
            );
            $add(
                new Point(8841, 1054, 9444),
                new Point(8841, 812, 9444),
                new Point(8890, 812, 9444),
                new Point(8890, 1054, 9444),
            );

            // short walls
            $add(
                new Point(8467, 772, 8941),
                new Point(8467, 772, 10032),
                new Point(8467, 1689, 10032),
                new Point(8467, 1689, 8941),
            );
            $add(
                new Point(8467, 772, 8941),
                new Point(8467, 1689, 8941),
                new Point(8264, 1689, 8745),
                new Point(8264, 772, 8745),
            );
            $add(
                new Point(8264, 772, 8745),
                new Point(8264, 1689, 8745),
                new Point(8070, 1689, 8745),
                new Point(8070, 772, 8745),
            );
            $add(
                new Point(8070, 772, 8745),
                new Point(8070, 1689, 8745),
                new Point(7877, 1689, 8940),
                new Point(7877, 772, 8940),
            );
            $add(
                new Point(7877, 772, 8940),
                new Point(7877, 1689, 8940),
                new Point(7305, 1689, 8940),
                new Point(7305, 772, 8940),
            );
            $add(
                new Point(7305, 772, 8940),
                new Point(7305, 1689, 8940),
                new Point(7118, 1689, 8748),
                new Point(7118, 772, 8748),
            );
            $add(
                new Point(7118, 772, 8748),
                new Point(7118, 1689, 8748),
                new Point(6925, 1689, 8748),
                new Point(6925, 772, 8748),
            );
        }

        // Map - A Side
        if (true) {

            // a boundary.001
            $add(
                new Point(10812, 1192, 11389),
                new Point(10755, 1192, 11389),
                new Point(10755, 1192, 11047),
                new Point(10812, 1192, 11047),
            );
            $add(
                new Point(10812, 1192, 11389),
                new Point(10812, 1192, 11047),
                new Point(10812, 1084, 11047),
                new Point(10812, 1084, 11389),
            );
            $add(
                new Point(10755, 1192, 11047),
                new Point(10755, 1192, 11389),
                new Point(10755, 818, 11389),
                new Point(10755, 818, 11047),
            );

            // a boundary.002
            $add(
                new Point(10792, 1192, 11392),
                new Point(9277, 1192, 11392),
                new Point(9277, 1192, 11336),
                new Point(10792, 1192, 11336),
            );
            $add(
                new Point(9277, 1192, 11392),
                new Point(10792, 1192, 11392),
                new Point(10792, 1066, 11392),
                new Point(9277, 1066, 11392),
            );
            $add(
                new Point(10792, 1192, 11336),
                new Point(9277, 1192, 11336),
                new Point(9277, 522, 11336),
                new Point(10792, 522, 11336),
            );

            // a boundary.003
            $add(
                new Point(11529, 1192, 11103),
                new Point(10755, 1192, 11103),
                new Point(10755, 1192, 11047),
                new Point(11529, 1192, 11047),
            );
            $add(
                new Point(10755, 1192, 11103),
                new Point(11529, 1192, 11103),
                new Point(11529, 1100, 11103),
                new Point(10767, 1100, 11103),
            );
            $add(
                new Point(11529, 1192, 11047),
                new Point(10755, 1192, 11047),
                new Point(10755, 797, 11047),
                new Point(11529, 797, 11047),
            );

            // a car
            $add(
                new Point(13071, 1105, 9490),
                new Point(13071, 1105, 11007),
                new Point(12456, 813, 11007),
                new Point(12456, 813, 9490),
            );

            // a corner barrels
            $add(
                new Point(8874, 1214, 12457),
                new Point(8874, 1214, 12243),
                new Point(8520, 1214, 12243),
                new Point(8520, 1214, 12457),
            );
            $add(
                new Point(8520, 1214, 12243),
                new Point(8874, 1214, 12243),
                new Point(8874, 1100, 12243),
                new Point(8520, 1100, 12243),
            );
            $add(
                new Point(8874, 1214, 12243),
                new Point(8874, 1214, 12457),
                new Point(8874, 1100, 12457),
                new Point(8874, 1100, 12243),
            );

            // a side
            $add(
                new Point(10792, 1105, 12493),
                new Point(10792, 1105, 11072),
                new Point(11499, 1105, 11072),
                new Point(11499, 1105, 12493),
            );
            $add(
                new Point(8421, 1105, 11348),
                new Point(10792, 1105, 11348),
                new Point(10792, 1105, 12643),
                new Point(8421, 1105, 12643),
            );
            $add(
                new Point(9298, 1105, 9493),
                new Point(9298, 1105, 11594),
                new Point(8421, 1105, 11594),
                new Point(8421, 1105, 9493),
            );
            $add(
                new Point(9298, 1105, 9493),
                new Point(8421, 1105, 9493),
                new Point(8421, 813, 9493),
                new Point(9298, 813, 9493),
            );

            // a side wall
            $add(
                new Point(11529, 1192, 12535),
                new Point(11479, 1192, 12535),
                new Point(11479, 1192, 11047),
                new Point(11529, 1192, 11047),
            );
            $add(
                new Point(11529, 1192, 12535),
                new Point(11529, 1192, 11047),
                new Point(11529, 813, 11047),
                new Point(11529, 813, 12535),
            );
            $add(
                new Point(11479, 1192, 11047),
                new Point(11479, 1192, 12535),
                new Point(11479, 817, 12535),
                new Point(11479, 817, 11047),
            );
            $add(
                new Point(11529, 1192, 12535),
                new Point(11529, 813, 12535),
                new Point(10733, 813, 12535),
                new Point(10733, 1192, 12535),
            );
            $add(
                new Point(11529, 1192, 12535),
                new Point(10733, 1192, 12535),
                new Point(10733, 1192, 13393),
                new Point(11529, 1192, 13393),
            );
            $add(
                new Point(11529, 1192, 12535),
                new Point(11529, 1192, 13393),
                new Point(11529, 1129, 13393),
                new Point(11529, 1129, 12535),
            );

            // a site to short walls
            $add(
                new Point(10768, 1048, 12386),
                new Point(10768, 1048, 13388),
                new Point(10768, 1721, 13388),
                new Point(10768, 1721, 12386),
            );
            $add(
                new Point(10768, 1048, 12386),
                new Point(10768, 1721, 12386),
                new Point(9847, 1721, 12386),
                new Point(9847, 1048, 12386),
            );
            $add(
                new Point(9847, 1048, 12386),
                new Point(9847, 1721, 12386),
                new Point(9847, 1721, 12295),
                new Point(9847, 1048, 12295),
            );
            $add(
                new Point(9847, 1048, 12295),
                new Point(9847, 1721, 12295),
                new Point(9452, 1721, 12295),
                new Point(9452, 1048, 12295),
            );
            $add(
                new Point(9452, 1048, 12295),
                new Point(9452, 1721, 12295),
                new Point(9452, 1721, 12533),
                new Point(9452, 1048, 12533),
            );
            $add(
                new Point(9452, 1048, 12533),
                new Point(9452, 1721, 12533),
                new Point(9324, 1721, 12533),
                new Point(9324, 1048, 12533),
            );
            $add(
                new Point(9324, 1048, 12533),
                new Point(9324, 1721, 12533),
                new Point(9324, 1721, 12483),
                new Point(9324, 1048, 12483),
            );
            $add(
                new Point(9324, 1048, 12483),
                new Point(9324, 1721, 12483),
                new Point(8471, 1721, 12483),
                new Point(8471, 1048, 12483),
            );
            $add(
                new Point(8471, 1048, 12483),
                new Point(8471, 1721, 12483),
                new Point(8471, 1721, 12310),
                new Point(8471, 1048, 12310),
            );
            $add(
                new Point(8471, 1048, 12310),
                new Point(8471, 1721, 12310),
                new Point(8554, 1721, 12235),
                new Point(8554, 1048, 12235),
            );
            $add(
                new Point(8554, 1048, 12235),
                new Point(8554, 1721, 12235),
                new Point(8554, 1721, 12030),
                new Point(8554, 1048, 12030),
            );
            $add(
                new Point(8554, 1048, 12030),
                new Point(8554, 1721, 12030),
                new Point(8463, 1721, 11940),
                new Point(8463, 1048, 11940),
            );
            $add(
                new Point(8463, 1048, 11940),
                new Point(8463, 1721, 11940),
                new Point(8463, 1721, 10335),
                new Point(8463, 1048, 10335),
            );
            $add(
                new Point(8463, 1048, 10335),
                new Point(8463, 1721, 10335),
                new Point(8555, 1721, 10237),
                new Point(8555, 1048, 10237),
            );
            $add(
                new Point(8555, 1048, 10237),
                new Point(8555, 1721, 10237),
                new Point(8555, 1721, 10046),
                new Point(8555, 1048, 10046),
            );
            $add(
                new Point(8555, 1048, 10046),
                new Point(8555, 1721, 10046),
                new Point(8450, 1721, 9940),
                new Point(8450, 1048, 9940),
            );

            // Goose
            $add(
                new Point(11534, 1103, 12418),
                new Point(11497, 1188, 12547),
                new Point(10759, 1188, 12547),
                new Point(10722, 1103, 12418),
            );

            // short boost wall
            $add(
                new Point(9329, 872, 10382),
                new Point(9329, 872, 10968),
                new Point(9329, 1190, 10968),
                new Point(9329, 1190, 10382),
            );
            $add(
                new Point(9329, 833, 10968),
                new Point(9329, 833, 11344),
                new Point(9329, 1190, 11344),
                new Point(9329, 1190, 10968),
            );
            $add(
                new Point(9329, 809, 10281),
                new Point(9329, 809, 10382),
                new Point(9329, 1190, 10382),
                new Point(9329, 1190, 10281),
            );
            $add(
                new Point(9897, 739, 11047),
                new Point(9329, 739, 11047),
                new Point(9329, 739, 11399),
                new Point(9897, 739, 11399),
            );
            $add(
                new Point(9329, 436, 11047),
                new Point(9329, 739, 11047),
                new Point(9897, 739, 11047),
                new Point(9897, 436, 11047),
            );
            $add(
                new Point(9897, 739, 11047),
                new Point(9897, 739, 11399),
                new Point(9897, 626, 11399),
                new Point(9897, 626, 11047),
            );
            $add(
                new Point(9329, 833, 11344),
                new Point(9329, 833, 11047),
                new Point(9329, 436, 11047),
                new Point(9329, 436, 11344),
            );
            $add(
                new Point(9329, 436, 11047),
                new Point(9329, 833, 11047),
                new Point(9302, 833, 11047),
                new Point(9302, 436, 11047),
            );
            $add(
                new Point(9302, 436, 11047),
                new Point(9302, 833, 11047),
                new Point(9302, 833, 11079),
                new Point(9302, 436, 11079),
            );
            $add(
                new Point(9329, 1190, 10281),
                new Point(9329, 1190, 11391),
                new Point(9272, 1190, 11391),
                new Point(9272, 1190, 10281),
            );
            $add(
                new Point(9272, 1190, 10281),
                new Point(9272, 1190, 11391),
                new Point(9272, 1102, 11391),
                new Point(9272, 1102, 10281),
            );
        }

        // Map - B Side
        if (true) {

            // b back plat floor
            $add(
                new Point(2458, 909, 13575),
                new Point(1348, 909, 13575),
                new Point(1348, 909, 11437),
                new Point(2458, 909, 11437),
            );
            $add(
                new Point(2458, 909, 13575),
                new Point(2458, 909, 11437),
                new Point(2458, 996, 11437),
                new Point(2458, 996, 13575),
            );
            $add(
                new Point(2458, 996, 13575),
                new Point(2458, 996, 11437),
                new Point(2509, 996, 11437),
                new Point(2509, 996, 13575),
            );
            $add(
                new Point(2509, 996, 13575),
                new Point(2509, 996, 11437),
                new Point(2509, 808, 11437),
                new Point(2509, 808, 13575),
            );
            $add(
                new Point(2458, 909, 11437),
                new Point(1348, 909, 11437),
                new Point(1348, 797, 11437),
                new Point(2458, 797, 11437),
            );

            // b back plat walls
            $add(
                new Point(1732, 909, 13256),
                new Point(2060, 909, 13256),
                new Point(2060, 1455, 13256),
                new Point(1732, 1455, 13256),
            );
            $add(
                new Point(1732, 909, 13256),
                new Point(1732, 1455, 13256),
                new Point(1732, 1455, 13421),
                new Point(1732, 909, 13421),
            );
            $add(
                new Point(1732, 909, 13421),
                new Point(1732, 1455, 13421),
                new Point(1338, 1455, 13421),
                new Point(1338, 909, 13421),
            );

            // b boxes
            $add(
                new Point(4268, 1025, 10579),
                new Point(4268, 1025, 10299),
                new Point(4547, 1025, 10299),
                new Point(4547, 1025, 10579),
            );
            $add(
                new Point(4268, 1025, 10299),
                new Point(4268, 1025, 10579),
                new Point(4268, 695, 10579),
                new Point(4268, 695, 10299),
            );
            $add(
                new Point(4268, 1025, 10579),
                new Point(4547, 1025, 10579),
                new Point(4547, 695, 10579),
                new Point(4268, 695, 10579),
            );
            $add(
                new Point(4547, 1025, 10579),
                new Point(4547, 1025, 10299),
                new Point(4547, 695, 10299),
                new Point(4547, 695, 10579),
            );
            $add(
                new Point(4268, 1025, 10579),
                new Point(4268, 1025, 10299),
                new Point(4307, 1069, 10299),
                new Point(4307, 1069, 10579),
            );
            $add(
                new Point(4307, 1069, 10579),
                new Point(4307, 1069, 10299),
                new Point(4411, 1021, 10299),
                new Point(4411, 1021, 10579),
            );

            // b boxes bottom
            $add(
                new Point(3989, 949, 10434),
                new Point(3989, 949, 10278),
                new Point(4264, 949, 10278),
                new Point(4264, 949, 10434),
            );
            $add(
                new Point(3989, 949, 10278),
                new Point(3989, 949, 10434),
                new Point(3989, 823, 10434),
                new Point(3989, 823, 10278),
            );
            $add(
                new Point(3989, 949, 10434),
                new Point(4264, 949, 10434),
                new Point(4264, 823, 10434),
                new Point(3989, 823, 10434),
            );
            $add(
                new Point(4264, 949, 10434),
                new Point(4264, 949, 10278),
                new Point(4264, 823, 10278),
                new Point(4264, 823, 10434),
            );
            $add(
                new Point(4264, 949, 10278),
                new Point(3989, 949, 10278),
                new Point(3989, 823, 10278),
                new Point(4264, 823, 10278),
            );

            // b plat walls
            $add(
                new Point(1307, 811, 11429),
                new Point(1745, 811, 11429),
                new Point(1745, 997, 11429),
                new Point(1307, 997, 11429),
            );
            $add(
                new Point(1745, 997, 11429),
                new Point(1745, 811, 11429),
                new Point(1745, 811, 11485),
                new Point(1745, 997, 11485),
            );
            $add(
                new Point(1745, 997, 11485),
                new Point(1745, 811, 11485),
                new Point(1307, 811, 11485),
                new Point(1307, 997, 11485),
            );
            $add(
                new Point(1745, 997, 11485),
                new Point(1307, 997, 11485),
                new Point(1307, 997, 11429),
                new Point(1745, 997, 11429),
            );
            $add(
                new Point(2510, 811, 11490),
                new Point(2120, 811, 11490),
                new Point(2120, 997, 11490),
                new Point(2510, 997, 11490),
            );
            $add(
                new Point(2120, 997, 11490),
                new Point(2120, 811, 11490),
                new Point(2120, 811, 11434),
                new Point(2120, 997, 11434),
            );
            $add(
                new Point(2120, 997, 11434),
                new Point(2120, 811, 11434),
                new Point(2510, 811, 11434),
                new Point(2510, 997, 11434),
            );
            $add(
                new Point(2120, 997, 11434),
                new Point(2510, 997, 11434),
                new Point(2510, 997, 11490),
                new Point(2120, 997, 11490),
            );

            // b side entry from ct
            $add(
                new Point(3656, 1480, 11051),
                new Point(3656, 1480, 12009),
                new Point(3656, 811, 12009),
                new Point(3656, 811, 11051),
            );
            $add(
                new Point(3656, 811, 12009),
                new Point(3656, 1480, 12009),
                new Point(3853, 1480, 12009),
                new Point(3853, 811, 12009),
            );
            $add(
                new Point(3853, 811, 12009),
                new Point(3853, 1480, 12009),
                new Point(3853, 1480, 11049),
                new Point(3853, 811, 11049),
            );
            $add(
                new Point(3656, 1480, 11051),
                new Point(3656, 811, 11051),
                new Point(3856, 811, 11051),
                new Point(3856, 1480, 11051),
            );
            $add(
                new Point(3656, 1480, 11075),
                new Point(3656, 1340, 11075),
                new Point(3656, 1340, 10468),
                new Point(3656, 1480, 10468),
            );
            $add(
                new Point(3853, 1340, 11074),
                new Point(3853, 1480, 11074),
                new Point(3853, 1480, 10456),
                new Point(3853, 1340, 10456),
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
                new Point(1552, 1477, 9520),
                new Point(1552, 801, 9520),
                new Point(1166, 801, 9520),
                new Point(1166, 1477, 9520),
            );
            $add(
                new Point(1166, 1477, 9520),
                new Point(1166, 801, 9520),
                new Point(1166, 801, 10377),
                new Point(1166, 1477, 10377),
            );
            $add(
                new Point(1166, 1477, 10377),
                new Point(1166, 801, 10377),
                new Point(1262, 801, 10377),
                new Point(1262, 1477, 10377),
            );
            $add(
                new Point(1262, 1477, 10377),
                new Point(1262, 801, 10377),
                new Point(1262, 801, 10476),
                new Point(1262, 1477, 10476),
            );
            $add(
                new Point(1262, 1477, 10476),
                new Point(1262, 801, 10476),
                new Point(1214, 801, 10476),
                new Point(1214, 1477, 10476),
            );
            $add(
                new Point(1214, 1477, 10476),
                new Point(1214, 801, 10476),
                new Point(1214, 801, 11049),
                new Point(1214, 1477, 11049),
            );
            $add(
                new Point(1214, 1477, 11049),
                new Point(1214, 801, 11049),
                new Point(1263, 801, 11049),
                new Point(1263, 1477, 11049),
            );
            $add(
                new Point(1263, 1477, 11049),
                new Point(1263, 801, 11049),
                new Point(1263, 801, 11148),
                new Point(1263, 1477, 11148),
            );
            $add(
                new Point(1263, 1477, 11148),
                new Point(1263, 801, 11148),
                new Point(1166, 801, 11148),
                new Point(1166, 1477, 11148),
            );
            $add(
                new Point(1166, 1477, 11148),
                new Point(1166, 801, 11148),
                new Point(1166, 801, 11435),
                new Point(1166, 1477, 11435),
            );
            $add(
                new Point(1166, 1477, 11435),
                new Point(1166, 801, 11435),
                new Point(1360, 801, 11435),
                new Point(1360, 1477, 11435),
            );
            $add(
                new Point(1360, 1477, 11435),
                new Point(1360, 801, 11435),
                new Point(1360, 801, 13967),
                new Point(1360, 1477, 13967),
            );
            $add(
                new Point(1360, 1477, 13967),
                new Point(1360, 801, 13967),
                new Point(2027, 801, 13967),
                new Point(2027, 1477, 13967),
            );
            $add(
                new Point(2027, 1477, 13967),
                new Point(2027, 801, 13967),
                new Point(2027, 801, 13015),
                new Point(2027, 1477, 13015),
            );
            $add(
                new Point(2027, 1477, 13015),
                new Point(2027, 801, 13015),
                new Point(2265, 801, 12775),
                new Point(2265, 1477, 12775),
            );
            $add(
                new Point(2265, 1477, 12775),
                new Point(2265, 801, 12775),
                new Point(3179, 801, 12775),
                new Point(3179, 1477, 12775),
            );
            $add(
                new Point(3179, 1477, 12775),
                new Point(3179, 801, 12775),
                new Point(3653, 801, 12481),
                new Point(3653, 1477, 12481),
            );
            $add(
                new Point(3653, 1477, 12481),
                new Point(3653, 801, 12481),
                new Point(3653, 801, 12247),
                new Point(3653, 1477, 12247),
            );
            $add(
                new Point(3901, 1477, 12247),
                new Point(3901, 801, 12247),
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
                new Point(3653, 1477, 12247),
                new Point(3653, 801, 12247),
                new Point(3901, 801, 12247),
                new Point(3901, 1477, 12247),
            );

            // b walls
            $add(
                new Point(2365, 1477, 9520),
                new Point(2365, 801, 9520),
                new Point(1918, 801, 9520),
                new Point(1918, 1477, 9520),
            );
            $add(
                new Point(2365, 801, 9520),
                new Point(2365, 1477, 9520),
                new Point(2514, 1477, 9378),
                new Point(2514, 801, 9378),
            );
            $add(
                new Point(2514, 801, 9378),
                new Point(2514, 1477, 9378),
                new Point(2514, 1477, 8990),
                new Point(2514, 801, 8990),
            );
            $add(
                new Point(2514, 801, 8990),
                new Point(2514, 1477, 8990),
                new Point(2888, 1477, 8990),
                new Point(2888, 801, 8990),
            );
            $add(
                new Point(2888, 801, 8990),
                new Point(2888, 1477, 8990),
                new Point(2888, 1477, 9136),
                new Point(2888, 801, 9136),
            );
            $add(
                new Point(2888, 801, 9136),
                new Point(2888, 1477, 9136),
                new Point(3465, 1477, 9710),
                new Point(3465, 801, 9710),
            );
            $add(
                new Point(3465, 801, 9710),
                new Point(3465, 1477, 9710),
                new Point(3563, 1477, 9710),
                new Point(3563, 801, 9710),
            );
            $add(
                new Point(3563, 801, 9710),
                new Point(3563, 1477, 9710),
                new Point(3563, 1477, 10382),
                new Point(3563, 801, 10382),
            );
            $add(
                new Point(3563, 801, 10382),
                new Point(3563, 1477, 10382),
                new Point(3659, 1477, 10382),
                new Point(3659, 801, 10382),
            );
            $add(
                new Point(3659, 801, 10382),
                new Point(3659, 1477, 10382),
                new Point(3659, 1477, 10479),
                new Point(3659, 801, 10479),
            );
            $add(
                new Point(3659, 801, 10479),
                new Point(3659, 1477, 10479),
                new Point(3852, 1477, 10479),
                new Point(3852, 801, 10479),
            );
            $add(
                new Point(3852, 801, 10479),
                new Point(3852, 1477, 10479),
                new Point(3852, 1477, 10382),
                new Point(3852, 801, 10382),
            );
            $add(
                new Point(3852, 801, 10382),
                new Point(3852, 1477, 10382),
                new Point(3998, 1477, 10382),
                new Point(3998, 801, 10382),
            );
            $add(
                new Point(3998, 801, 10382),
                new Point(3998, 1477, 10382),
                new Point(3998, 1477, 10289),
                new Point(3998, 801, 10289),
            );
            $add(
                new Point(3998, 801, 10289),
                new Point(3998, 1477, 10289),
                new Point(4569, 1477, 10289),
                new Point(4569, 801, 10289),
            );
            $add(
                new Point(4569, 801, 10289),
                new Point(4569, 1477, 10289),
                new Point(4569, 1477, 10381),
                new Point(4569, 801, 10381),
            );
            $add(
                new Point(1960, 1477, 9520),
                new Point(1960, 1317, 9520),
                new Point(1513, 1317, 9520),
                new Point(1513, 1477, 9520),
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
                new Point(4579, 700, 11872),
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
        }

        // Map - CT spawn
        if (true) {

            // ct spawn main floor
            $add(
                new Point(4240, 818, 11948),
                new Point(4240, 818, 10239),
                new Point(4573, 705, 10239),
                new Point(4573, 705, 11948),
            );
            $add(
                new Point(4573, 705, 11948),
                new Point(4573, 705, 10239),
                new Point(5277, 542, 10239),
                new Point(5277, 542, 11948),
            );
            $add(
                new Point(5277, 542, 11948),
                new Point(5277, 542, 10239),
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
                new Point(9374, 440, 11324),
                new Point(9374, 440, 10163),
                new Point(10210, 760, 10163),
                new Point(10210, 760, 11324),
            );
            $add(
                new Point(10210, 760, 11324),
                new Point(10210, 760, 10163),
                new Point(10649, 820, 10163),
                new Point(10649, 820, 11324),
            );
            $add(
                new Point(10649, 820, 11324),
                new Point(10649, 820, 10163),
                new Point(11355, 820, 10163),
                new Point(11355, 820, 11324),
            );
            $add(
                new Point(5750, 440, 11948),
                new Point(5750, 440, 10013),
                new Point(9374, 440, 10013),
                new Point(9374, 440, 11948),
            );

            // ct to b side walls
            $add(
                new Point(9222, 994, 11712),
                new Point(9222, 994, 11051),
                new Point(9222, 436, 11051),
                new Point(9222, 436, 11712),
            );
            $add(
                new Point(9222, 994, 11712),
                new Point(9222, 436, 11712),
                new Point(7884, 436, 11712),
                new Point(7884, 994, 11712),
            );
            $add(
                new Point(7884, 994, 11712),
                new Point(7884, 436, 11712),
                new Point(7884, 436, 11323),
                new Point(7884, 994, 11323),
            );
            $add(
                new Point(7884, 994, 11323),
                new Point(7884, 436, 11323),
                new Point(7595, 436, 11051),
                new Point(7595, 994, 11051),
            );
            $add(
                new Point(7595, 994, 11051),
                new Point(7595, 436, 11051),
                new Point(6922, 436, 11051),
                new Point(6922, 994, 11051),
            );
            $add(
                new Point(6922, 1803, 11051),
                new Point(6922, 436, 11051),
                new Point(6720, 436, 11242),
                new Point(6720, 1803, 11242),
            );
            $add(
                new Point(6720, 1803, 11242),
                new Point(6720, 436, 11242),
                new Point(6720, 436, 11427),
                new Point(6720, 1803, 11427),
            );
            $add(
                new Point(6720, 1803, 11427),
                new Point(6720, 436, 11427),
                new Point(6048, 436, 11427),
                new Point(6048, 1803, 11427),
            );
            $add(
                new Point(6048, 1803, 11427),
                new Point(6048, 436, 11427),
                new Point(5861, 436, 11511),
                new Point(5861, 1803, 11511),
            );
            $add(
                new Point(5861, 1803, 11511),
                new Point(5861, 436, 11511),
                new Point(5861, 436, 11579),
                new Point(5861, 1803, 11579),
            );
            $add(
                new Point(5861, 1803, 11579),
                new Point(5861, 436, 11579),
                new Point(5657, 436, 11731),
                new Point(5657, 1803, 11731),
            );
            $add(
                new Point(5657, 1803, 11731),
                new Point(5657, 436, 11731),
                new Point(5383, 436, 11731),
                new Point(5383, 1803, 11731),
            );
            $add(
                new Point(5383, 1803, 11731),
                new Point(5383, 436, 11731),
                new Point(5078, 436, 11865),
                new Point(5078, 1803, 11865),
            );
            $add(
                new Point(5078, 1803, 11865),
                new Point(5078, 436, 11865),
                new Point(4372, 436, 11865),
                new Point(4372, 1803, 11865),
            );
            $add(
                new Point(4372, 1803, 11865),
                new Point(4372, 436, 11865),
                new Point(4372, 436, 12301),
                new Point(4372, 1803, 12301),
            );
            $add(
                new Point(4372, 1803, 12301),
                new Point(4372, 436, 12301),
                new Point(3671, 436, 12301),
                new Point(3671, 1803, 12301),
            );
            $add(
                new Point(3671, 1803, 12301),
                new Point(3671, 436, 12301),
                new Point(3671, 436, 13268),
                new Point(3671, 1803, 13268),
            );
            $add(
                new Point(7014, 812, 11175),
                new Point(7014, 1666, 11175),
                new Point(7014, 1666, 10881),
                new Point(7014, 812, 10881),
            );
            $add(
                new Point(7014, 884, 10881),
                new Point(7014, 884, 10097),
                new Point(6918, 884, 10097),
                new Point(6918, 884, 10881),
            );
            $add(
                new Point(6918, 884, 10881),
                new Point(6918, 884, 10097),
                new Point(6918, 1094, 10097),
                new Point(6918, 1094, 10881),
            );
            $add(
                new Point(7014, 884, 10097),
                new Point(7014, 1666, 10097),
                new Point(6912, 1666, 10097),
                new Point(6912, 884, 10097),
            );
            $add(
                new Point(7014, 1666, 10097),
                new Point(7014, 884, 10097),
                new Point(7014, 884, 10881),
                new Point(7014, 1666, 10881),
            );
            $add(
                new Point(6918, 884, 10881),
                new Point(6918, 1094, 10881),
                new Point(6918, 1094, 11107),
                new Point(6918, 884, 11107),
            );
            $add(
                new Point(6918, 1094, 11107),
                new Point(6918, 1094, 10881),
                new Point(7046, 1094, 10881),
                new Point(7046, 1094, 11107),
            );
            $add(
                new Point(6918, 1094, 10881),
                new Point(6918, 1094, 10097),
                new Point(7046, 1094, 10097),
                new Point(7046, 1094, 10881),
            );
            $add(
                new Point(6922, 436, 11051),
                new Point(6922, 1803, 11051),
                new Point(7036, 1803, 11051),
                new Point(7036, 436, 11051),
            );
            $add(
                new Point(9222, 436, 11051),
                new Point(9222, 994, 11051),
                new Point(9316, 994, 11051),
                new Point(9316, 436, 11051),
            );

            // mid to b walls
            $add(
                new Point(4569, 312, 10381),
                new Point(4569, 1477, 10381),
                new Point(4961, 1477, 10381),
                new Point(4961, 312, 10381),
            );
            $add(
                new Point(4961, 312, 10381),
                new Point(4961, 1477, 10381),
                new Point(4961, 1477, 10298),
                new Point(4961, 312, 10298),
            );
            $add(
                new Point(4961, 312, 10298),
                new Point(4961, 1477, 10298),
                new Point(5528, 1477, 10298),
                new Point(5528, 312, 10298),
            );
            $add(
                new Point(5528, 312, 10298),
                new Point(5528, 1477, 10298),
                new Point(5528, 1477, 10383),
                new Point(5528, 312, 10383),
            );
            $add(
                new Point(5528, 312, 10383),
                new Point(5528, 1477, 10383),
                new Point(5924, 1477, 10383),
                new Point(5924, 312, 10383),
            );
            $add(
                new Point(5924, 312, 10383),
                new Point(5924, 1477, 10383),
                new Point(5924, 1477, 9990),
                new Point(5924, 312, 9990),
            );
            $add(
                new Point(5924, 312, 9990),
                new Point(5924, 1477, 9990),
                new Point(5823, 1477, 9990),
                new Point(5823, 312, 9990),
            );
            $add(
                new Point(5823, 312, 9990),
                new Point(5823, 1477, 9990),
                new Point(5823, 1477, 9422),
                new Point(5823, 312, 9422),
            );
            $add(
                new Point(5823, 312, 9422),
                new Point(5823, 1477, 9422),
                new Point(5918, 1477, 9422),
                new Point(5918, 312, 9422),
            );
            $add(
                new Point(5918, 312, 9422),
                new Point(5918, 1477, 9422),
                new Point(5918, 1477, 9043),
                new Point(5918, 312, 9043),
            );
            $add(
                new Point(5918, 312, 9043),
                new Point(5918, 1477, 9043),
                new Point(5692, 1477, 9043),
                new Point(5692, 312, 9043),
            );
        }

        // Map - Lower Tunnel
        if (true) {

            // lower tunnel
            $add(
                new Point(4628, 464, 7918),
                new Point(3996, 464, 7918),
                new Point(3996, 654, 7592),
                new Point(4628, 654, 7592),
            );
            $add(
                new Point(4628, 654, 7592),
                new Point(3996, 654, 7592),
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
                new Point(4048, 464, 8079),
                new Point(4048, 464, 8744),
                new Point(4048, 949, 8744),
                new Point(4048, 949, 8079),
            );
            $add(
                new Point(4048, 464, 8079),
                new Point(4048, 949, 8079),
                new Point(4238, 949, 8079),
                new Point(4238, 464, 8079),
            );
            $add(
                new Point(4238, 464, 8079),
                new Point(4238, 949, 8079),
                new Point(4238, 949, 7597),
                new Point(4238, 464, 7599),
            );
            $add(
                new Point(4238, 949, 7597),
                new Point(4238, 949, 8079),
                new Point(3879, 949, 8079),
                new Point(3879, 949, 7597),
            );
            $add(
                new Point(3879, 949, 7597),
                new Point(3879, 949, 8079),
                new Point(3879, 857, 8079),
                new Point(3879, 857, 7597),
            );
            $add(
                new Point(4238, 949, 7597),
                new Point(3879, 949, 7597),
                new Point(3879, 648, 7597),
                new Point(4238, 648, 7597),
            );
            $add(
                new Point(4048, 949, 8744),
                new Point(4048, 464, 8744),
                new Point(6056, 464, 8744),
                new Point(6056, 949, 8744),
            );
            $add(
                new Point(6056, 949, 8744),
                new Point(6056, 464, 8744),
                new Point(6056, 464, 8605),
                new Point(6056, 949, 8605),
            );
            $add(
                new Point(6056, 949, 8605),
                new Point(6056, 464, 8605),
                new Point(6159, 464, 8605),
                new Point(6159, 949, 8605),
            );
            $add(
                new Point(6159, 949, 8605),
                new Point(6159, 464, 8605),
                new Point(6159, 464, 9140),
                new Point(6159, 949, 9140),
            );
            $add(
                new Point(6159, 949, 9140),
                new Point(6159, 464, 9140),
                new Point(5906, 464, 9140),
                new Point(5906, 949, 9140),
            );
            $add(
                new Point(4628, 472, 7753),
                new Point(4628, 472, 8871),
                new Point(3996, 472, 8871),
                new Point(3996, 472, 7753),
            );
            $add(
                new Point(6057, 472, 8080),
                new Point(6057, 472, 8871),
                new Point(4628, 472, 8871),
                new Point(4628, 472, 8080),
            );
            $add(
                new Point(6057, 472, 8080),
                new Point(4628, 472, 8080),
                new Point(4628, 1020, 8080),
                new Point(6057, 1020, 8080),
            );
            $add(
                new Point(6057, 472, 8080),
                new Point(6057, 1020, 8080),
                new Point(6057, 1020, 8240),
                new Point(6057, 472, 8240),
            );
            $add(
                new Point(6057, 472, 8240),
                new Point(6057, 1020, 8240),
                new Point(6162, 1020, 8240),
                new Point(6162, 472, 8240),
            );
            $add(
                new Point(6057, 472, 8871),
                new Point(6057, 472, 8080),
                new Point(6057, 387, 8080),
                new Point(6057, 387, 8871),
            );
            $add(
                new Point(4628, 1020, 8080),
                new Point(4628, 472, 8080),
                new Point(4628, 472, 8032),
                new Point(4628, 1020, 8032),
            );
            $add(
                new Point(4185, 756, 7614),
                new Point(4185, 756, 7214),
                new Point(4237, 649, 7214),
                new Point(4237, 649, 7614),
            );

            // lower tunnel stairs wall
            $add(
                new Point(4616, 1697, 8066),
                new Point(4616, 406, 8066),
                new Point(4616, 406, 7515),
                new Point(4616, 1697, 7515),
            );
            $add(
                new Point(4363, 1697, 7227),
                new Point(4363, 406, 7227),
                new Point(3827, 406, 7227),
                new Point(3827, 1697, 7227),
            );
            $add(
                new Point(4616, 406, 8066),
                new Point(4616, 1697, 8066),
                new Point(4652, 1697, 8066),
                new Point(4652, 406, 8066),
            );
            $add(
                new Point(4476, 1697, 7286),
                new Point(4476, 406, 7286),
                new Point(4363, 406, 7227),
                new Point(4363, 1697, 7227),
            );
            $add(
                new Point(4595, 1697, 7421),
                new Point(4595, 406, 7421),
                new Point(4476, 406, 7286),
                new Point(4476, 1697, 7286),
            );
            $add(
                new Point(4616, 406, 7515),
                new Point(4595, 406, 7421),
                new Point(4595, 1697, 7421),
                new Point(4616, 1697, 7515),
            );
            $add(
                new Point(3827, 1697, 7227),
                new Point(3827, 406, 7227),
                new Point(3827, 406, 7178),
                new Point(3827, 1697, 7178),
            );

            // lower tunnel to mid
            $add(
                new Point(6162, 464, 7486),
                new Point(6162, 1144, 7486),
                new Point(6060, 1144, 7486),
                new Point(6060, 464, 7486),
            );
            $add(
                new Point(6060, 464, 7486),
                new Point(6060, 1353, 7486),
                new Point(6060, 1353, 6767),
                new Point(6060, 464, 6767),
            );
            $add(
                new Point(6162, 464, 8240),
                new Point(6162, 913, 8240),
                new Point(6162, 913, 7486),
                new Point(6162, 464, 7486),
            );
            $add(
                new Point(6162, 913, 7486),
                new Point(6162, 913, 8240),
                new Point(6162, 1144, 8240),
                new Point(6162, 1144, 7486),
            );
            $add(
                new Point(6162, 1144, 8240),
                new Point(6162, 913, 8240),
                new Point(6162, 913, 9136),
                new Point(6162, 1144, 9136),
            );
            $add(
                new Point(6162, 1144, 8935),
                new Point(6749, 1144, 8935),
                new Point(6749, 1144, 9134),
                new Point(6162, 1144, 9134),
            );
            $add(
                new Point(6162, 1144, 8240),
                new Point(6162, 1144, 9136),
                new Point(5824, 1144, 9136),
                new Point(5824, 1144, 8240),
            );
            $add(
                new Point(6162, 1144, 7486),
                new Point(6162, 1144, 8240),
                new Point(5824, 1144, 8240),
                new Point(5824, 1144, 7486),
            );
            $add(
                new Point(5824, 1144, 8240),
                new Point(5824, 1144, 9136),
                new Point(5824, 1574, 9136),
                new Point(5824, 1574, 8240),
            );
            $add(
                new Point(5824, 1144, 7486),
                new Point(5824, 1144, 8240),
                new Point(5824, 1574, 8240),
                new Point(5824, 1574, 7486),
            );
            $add(
                new Point(6162, 1144, 8935),
                new Point(6162, 962, 8935),
                new Point(6749, 962, 8935),
                new Point(6749, 1144, 8935),
            );
            $add(
                new Point(6162, 1144, 9134),
                new Point(6749, 1144, 9134),
                new Point(6749, 946, 9134),
                new Point(6162, 946, 9134),
            );
            $add(
                new Point(6162, 1144, 9134),
                new Point(6162, 946, 9134),
                new Point(5865, 946, 9134),
                new Point(5865, 1144, 9134),
            );
            $add(
                new Point(5824, 1144, 7486),
                new Point(5824, 1574, 7486),
                new Point(5824, 1574, 6718),
                new Point(5824, 1144, 6718),
            );

            // lower tunnel to upper
            $add(
                new Point(3887, 946, 7597),
                new Point(3887, 946, 7802),
                new Point(3813, 885, 7802),
                new Point(3813, 885, 7597),
            );
        }

        // Map - Mid
        if (true) {

            // mid main floor
            $add(
                new Point(6983, 464, 9039),
                new Point(6011, 464, 9039),
                new Point(6011, 464, 7796),
                new Point(6983, 464, 7796),
            );
            $add(
                new Point(6983, 818, 6387),
                new Point(6983, 485, 7845),
                new Point(6027, 485, 7845),
                new Point(6027, 818, 6387),
            );
            $add(
                new Point(6027, 485, 7845),
                new Point(6983, 485, 7845),
                new Point(6983, 402, 7845),
                new Point(6027, 402, 7845),
            );
            $add(
                new Point(6011, 464, 9039),
                new Point(6983, 464, 9039),
                new Point(6983, 391, 9039),
                new Point(6011, 391, 9039),
            );

            // mid walls
            $add(
                new Point(9247, 415, 10295),
                new Point(9247, 995, 10295),
                new Point(7971, 995, 10295),
                new Point(7971, 415, 10295),
            );
            $add(
                new Point(7971, 415, 10295),
                new Point(7971, 995, 10295),
                new Point(7680, 995, 10105),
                new Point(7680, 415, 10105),
            );
            $add(
                new Point(7680, 415, 10105),
                new Point(7680, 995, 10105),
                new Point(6917, 995, 10105),
                new Point(6917, 415, 10105),
            );
            $add(
                new Point(6724, 415, 9904),
                new Point(6724, 1866, 9904),
                new Point(6724, 1866, 9711),
                new Point(6724, 415, 9711),
            );
            $add(
                new Point(6917, 415, 10105),
                new Point(6917, 1866, 10105),
                new Point(6724, 1866, 9904),
                new Point(6724, 415, 9904),
            );
            $add(
                new Point(6724, 415, 9711),
                new Point(6724, 1866, 9711),
                new Point(6916, 1866, 9513),
                new Point(6916, 415, 9513),
            );
            $add(
                new Point(6916, 415, 9513),
                new Point(6916, 1866, 9513),
                new Point(6916, 1866, 9318),
                new Point(6916, 415, 9318),
            );
            $add(
                new Point(6916, 415, 9318),
                new Point(6916, 1866, 9318),
                new Point(6725, 1866, 9135),
                new Point(6725, 415, 9135),
            );
            $add(
                new Point(6725, 415, 9135),
                new Point(6725, 1866, 9135),
                new Point(6725, 1866, 8934),
                new Point(6725, 415, 8934),
            );
            $add(
                new Point(6725, 415, 8934),
                new Point(6725, 1866, 8934),
                new Point(6933, 1866, 8735),
                new Point(6933, 415, 8735),
            );
            $add(
                new Point(6920, 440, 8746),
                new Point(6920, 899, 8746),
                new Point(6920, 899, 6445),
                new Point(6920, 440, 6445),
            );
            $add(
                new Point(6920, 899, 6445),
                new Point(6920, 899, 8746),
                new Point(6970, 899, 8746),
                new Point(6970, 899, 6445),
            );
            $add(
                new Point(6920, 440, 6445),
                new Point(6920, 899, 6445),
                new Point(6969, 899, 6445),
                new Point(6969, 440, 6445),
            );
            $add(
                new Point(6970, 899, 6445),
                new Point(6970, 899, 8746),
                new Point(6970, 799, 8746),
                new Point(6970, 799, 6445),
            );
            $add(
                new Point(6933, 415, 8735),
                new Point(6933, 1866, 8735),
                new Point(6933, 1866, 8755),
                new Point(6933, 415, 8755),
            );

            // short to top mid walls
            $add(
                new Point(9218, 769, 8191),
                new Point(9218, 769, 10301),
                new Point(9218, 1775, 10301),
                new Point(9218, 1775, 8191),
            );
            $add(
                new Point(9218, 769, 8191),
                new Point(9218, 1775, 8191),
                new Point(7582, 1775, 8191),
                new Point(7582, 769, 8191),
            );
            $add(
                new Point(7582, 769, 8191),
                new Point(7582, 1775, 8191),
                new Point(7302, 1775, 7887),
                new Point(7302, 769, 7887),
            );
            $add(
                new Point(7302, 769, 7887),
                new Point(7302, 1775, 7887),
                new Point(7302, 1775, 5863),
                new Point(7302, 769, 5863),
            );
            $add(
                new Point(7302, 769, 5863),
                new Point(7302, 1775, 5863),
                new Point(7594, 1775, 5581),
                new Point(7594, 769, 5581),
            );
            $add(
                new Point(7594, 769, 5581),
                new Point(7594, 1775, 5581),
                new Point(8840, 1775, 5581),
                new Point(8840, 769, 5581),
            );
            $add(
                new Point(8840, 769, 5581),
                new Point(8840, 1775, 5581),
                new Point(8840, 1775, 5101),
                new Point(8840, 769, 5101),
            );
            $add(
                new Point(8840, 769, 5101),
                new Point(8840, 1775, 5101),
                new Point(9032, 1775, 4906),
                new Point(9032, 769, 4906),
            );
            $add(
                new Point(9032, 769, 4906),
                new Point(9032, 1775, 4906),
                new Point(9321, 1775, 4906),
                new Point(9321, 769, 4906),
            );
            $add(
                new Point(9321, 769, 4906),
                new Point(9321, 1775, 4906),
                new Point(9321, 1775, 5019),
                new Point(9321, 769, 5019),
            );
            $add(
                new Point(9218, 1775, 10301),
                new Point(9218, 769, 10301),
                new Point(9270, 769, 10301),
                new Point(9270, 1775, 10301),
            );
            $add(
                new Point(9270, 1775, 10301),
                new Point(9270, 769, 10301),
                new Point(9270, 769, 10267),
                new Point(9270, 1775, 10267),
            );

            // top mid main floor
            $add(
                new Point(5481, 835, 2262),
                new Point(10025, 835, 2262),
                new Point(10025, 835, 6403),
                new Point(5481, 835, 6403),
            );
            $add(
                new Point(5481, 835, 6403),
                new Point(10025, 835, 6403),
                new Point(10025, 766, 6403),
                new Point(5481, 766, 6403),
            );

            // top mid walls
            $add(
                new Point(6157, 726, 2235),
                new Point(6157, 726, 4725),
                new Point(6157, 1778, 4725),
                new Point(6157, 1778, 2235),
            );
            $add(
                new Point(6157, 1778, 4725),
                new Point(6157, 726, 4725),
                new Point(5728, 726, 4725),
                new Point(5728, 1778, 4725),
            );
            $add(
                new Point(5728, 1778, 4725),
                new Point(5728, 726, 4725),
                new Point(5503, 726, 5141),
                new Point(5503, 1778, 5141),
            );
            $add(
                new Point(5503, 1778, 5141),
                new Point(5503, 726, 5141),
                new Point(5503, 726, 5673),
                new Point(5503, 1778, 5673),
            );
            $add(
                new Point(5503, 1778, 5673),
                new Point(5503, 726, 5673),
                new Point(5583, 726, 5673),
                new Point(5583, 1778, 5673),
            );
            $add(
                new Point(5583, 1778, 5673),
                new Point(5583, 726, 5673),
                new Point(5775, 726, 5867),
                new Point(5775, 1778, 5867),
            );
            $add(
                new Point(5775, 1778, 5867),
                new Point(5775, 726, 5867),
                new Point(5775, 726, 6060),
                new Point(5775, 1778, 6060),
            );
            $add(
                new Point(5775, 1778, 6060),
                new Point(5775, 726, 6060),
                new Point(6061, 726, 6060),
                new Point(6061, 1778, 6060),
            );
            $add(
                new Point(6061, 1778, 6060),
                new Point(6061, 726, 6060),
                new Point(6061, 726, 6298),
                new Point(6061, 1778, 6298),
            );
            $add(
                new Point(6061, 1778, 6298),
                new Point(6061, 726, 6298),
                new Point(6162, 726, 6298),
                new Point(6162, 1778, 6298),
            );
            $add(
                new Point(6162, 1778, 6298),
                new Point(6162, 726, 6298),
                new Point(6162, 726, 6784),
                new Point(6162, 1778, 6784),
            );
            $add(
                new Point(6162, 1778, 6784),
                new Point(6162, 726, 6784),
                new Point(5635, 726, 6784),
                new Point(5635, 1778, 6784),
            );
            $add(
                new Point(6157, 726, 2235),
                new Point(6157, 1778, 2235),
                new Point(6106, 1778, 2235),
                new Point(6106, 726, 2235),
            );

            // top mid walls.001
            $add(
                new Point(8081, 786, 2989),
                new Point(8081, 786, 4725),
                new Point(8081, 1620, 4725),
                new Point(8081, 1620, 2989),
            );
            $add(
                new Point(8081, 1620, 4725),
                new Point(8081, 786, 4725),
                new Point(7785, 786, 5014),
                new Point(7785, 1620, 5014),
            );
            $add(
                new Point(7785, 1620, 5014),
                new Point(7785, 786, 5014),
                new Point(6820, 786, 5014),
                new Point(6820, 1620, 5014),
            );
            $add(
                new Point(6820, 1620, 5014),
                new Point(6820, 786, 5014),
                new Point(6531, 786, 4725),
                new Point(6531, 1620, 4725),
            );
            $add(
                new Point(6531, 1620, 4725),
                new Point(6531, 786, 4725),
                new Point(6531, 786, 2983),
                new Point(6531, 1620, 2983),
            );
            $add(
                new Point(6531, 1620, 2983),
                new Point(6531, 786, 2983),
                new Point(6831, 786, 2696),
                new Point(6831, 1620, 2696),
            );
            $add(
                new Point(6831, 1620, 2696),
                new Point(6831, 786, 2696),
                new Point(7781, 786, 2696),
                new Point(7781, 1620, 2696),
            );
            $add(
                new Point(7781, 1620, 2696),
                new Point(7781, 786, 2696),
                new Point(8084, 786, 2990),
                new Point(8084, 1620, 2990),
            );
            $add(
                new Point(6531, 1620, 4725),
                new Point(6531, 1620, 2983),
                new Point(6108, 1620, 2983),
                new Point(6108, 1620, 4725),
            );
            $add(
                new Point(6108, 1620, 2983),
                new Point(6531, 1620, 2983),
                new Point(6531, 1510, 2983),
                new Point(6108, 1510, 2983),
            );
            $add(
                new Point(6531, 1620, 4725),
                new Point(6108, 1620, 4725),
                new Point(6108, 1510, 4725),
                new Point(6531, 1510, 4725),
            );

            // xBox
            $add(
                new Point(6620, 593, 8508),
                new Point(6620, 593, 8554),
                new Point(6693, 593, 8554),
                new Point(6693, 593, 8508),
            );
            $add(
                new Point(6631, 440, 8561),
                new Point(6631, 720, 8561),
                new Point(6631, 720, 8262),
                new Point(6631, 440, 8262),
            );
            $add(
                new Point(6631, 440, 8262),
                new Point(6631, 720, 8262),
                new Point(6931, 720, 8262),
                new Point(6931, 440, 8262),
            );
            $add(
                new Point(6631, 720, 8561),
                new Point(6631, 440, 8561),
                new Point(6961, 440, 8561),
                new Point(6961, 720, 8561),
            );
            $add(
                new Point(6631, 720, 8262),
                new Point(6631, 720, 8561),
                new Point(6955, 720, 8561),
                new Point(6955, 720, 8262),
            );
            $add(
                new Point(6646, 593, 8253),
                new Point(6646, 593, 8300),
                new Point(6930, 593, 8300),
                new Point(6930, 593, 8253),
            );
        }

        // Map - Outside Tunnels
        if (true) {

            // outside tunnels bumps
            $add(
                new Point(2325, 906, 5787),
                new Point(1479, 906, 5787),
                new Point(1479, 906, 5677),
                new Point(2325, 906, 5677),
            );
            $add(
                new Point(2325, 906, 5677),
                new Point(1479, 906, 5677),
                new Point(1479, 808, 5677),
                new Point(2325, 808, 5677),
            );
            $add(
                new Point(2325, 906, 5787),
                new Point(2325, 906, 5677),
                new Point(2325, 814, 5677),
                new Point(2325, 814, 5787),
            );
            $add(
                new Point(3774, 906, 5787),
                new Point(3083, 906, 5787),
                new Point(3083, 906, 5677),
                new Point(3774, 906, 5677),
            );
            $add(
                new Point(3774, 906, 5677),
                new Point(3083, 906, 5677),
                new Point(3083, 808, 5677),
                new Point(3774, 808, 5677),
            );
            $add(
                new Point(3083, 906, 5677),
                new Point(3083, 906, 5787),
                new Point(3083, 803, 5787),
                new Point(3083, 803, 5677),
            );

            // outside tunnels walls
            $add(
                new Point(1481, 794, 5959),
                new Point(1481, 1691, 5959),
                new Point(1481, 1691, 5011),
                new Point(1481, 794, 5011),
            );
            $add(
                new Point(1481, 1691, 5959),
                new Point(1481, 794, 5959),
                new Point(1742, 794, 5959),
                new Point(1742, 1691, 5959),
            );
            $add(
                new Point(1742, 1691, 5959),
                new Point(1742, 794, 5959),
                new Point(1938, 794, 6162),
                new Point(1938, 1691, 6162),
            );
            $add(
                new Point(1938, 1691, 6162),
                new Point(1938, 794, 6162),
                new Point(1938, 794, 6265),
                new Point(1938, 1691, 6265),
            );
            $add(
                new Point(1481, 794, 5011),
                new Point(1481, 1691, 5011),
                new Point(1599, 1691, 5011),
                new Point(1599, 794, 5011),
            );
            $add(
                new Point(1599, 794, 5011),
                new Point(1599, 1691, 5011),
                new Point(1599, 1691, 4501),
                new Point(1599, 794, 4501),
            );
            $add(
                new Point(1599, 794, 4501),
                new Point(1599, 1691, 4501),
                new Point(1555, 1691, 4501),
                new Point(1555, 794, 4501),
            );
            $add(
                new Point(1555, 794, 4501),
                new Point(1555, 1691, 4501),
                new Point(1555, 1691, 3754),
                new Point(1555, 794, 3754),
            );
            $add(
                new Point(1555, 794, 3754),
                new Point(1555, 1691, 3754),
                new Point(1455, 1691, 3754),
                new Point(1455, 794, 3754),
            );
            $add(
                new Point(1455, 794, 3754),
                new Point(1455, 1691, 3754),
                new Point(1455, 1691, 2075),
                new Point(1455, 794, 2075),
            );

            // outside tunnels walls.001
            $add(
                new Point(3946, 800, 4532),
                new Point(3946, 800, 5579),
                new Point(3946, 2173, 5579),
                new Point(3946, 2173, 4532),
            );
            $add(
                new Point(3946, 800, 4532),
                new Point(3946, 2173, 4532),
                new Point(3754, 2173, 4532),
                new Point(3754, 800, 4532),
            );
            $add(
                new Point(3754, 800, 4532),
                new Point(3754, 2173, 4532),
                new Point(3754, 2173, 4149),
                new Point(3754, 800, 4149),
            );
            $add(
                new Point(3754, 800, 4149),
                new Point(3754, 2173, 4149),
                new Point(3654, 2173, 4149),
                new Point(3654, 800, 4149),
            );
            $add(
                new Point(3654, 800, 4149),
                new Point(3654, 2173, 4149),
                new Point(3475, 2173, 3980),
                new Point(3475, 800, 3980),
            );
            $add(
                new Point(3475, 800, 3980),
                new Point(3475, 2173, 3980),
                new Point(3475, 2173, 3717),
                new Point(3475, 800, 3717),
            );
            $add(
                new Point(3946, 2173, 5579),
                new Point(3946, 800, 5579),
                new Point(3752, 800, 5579),
                new Point(3752, 2173, 5579),
            );
            $add(
                new Point(3752, 2173, 5579),
                new Point(3752, 800, 5579),
                new Point(3752, 800, 5960),
                new Point(3752, 2173, 5960),
            );
            $add(
                new Point(3752, 2173, 5960),
                new Point(3752, 800, 5960),
                new Point(3663, 800, 5960),
                new Point(3663, 2173, 5960),
            );
            $add(
                new Point(3663, 2173, 5960),
                new Point(3663, 800, 5960),
                new Point(3465, 800, 6154),
                new Point(3465, 2173, 6154),
            );
            $add(
                new Point(3465, 2173, 6154),
                new Point(3465, 800, 6154),
                new Point(3465, 800, 6275),
                new Point(3465, 2173, 6275),
            );
        }

        // Map - Pit
        if (true) {

            // lower pit walls
            $add(
                new Point(12484, 197, 4583),
                new Point(12484, 197, 6638),
                new Point(12484, 1044, 6638),
                new Point(12484, 1044, 4583),
            );
            $add(
                new Point(12484, 1044, 6638),
                new Point(12484, 197, 6638),
                new Point(12583, 197, 6638),
                new Point(12583, 1044, 6638),
            );
            $add(
                new Point(12484, 1044, 4583),
                new Point(12484, 1044, 6638),
                new Point(12583, 1044, 6638),
                new Point(12583, 1044, 4583),
            );
            $add(
                new Point(12583, 1044, 4583),
                new Point(12583, 1044, 6638),
                new Point(12583, 950, 6638),
                new Point(12583, 950, 4583),
            );
            $add(
                new Point(12633, 925, 4635),
                new Point(11444, 925, 4635),
                new Point(11444, 219, 4635),
                new Point(12633, 219, 4635),
            );
            $add(
                new Point(12558, 1044, 5164),
                new Point(11431, 1044, 5164),
                new Point(11431, 800, 5164),
                new Point(12558, 800, 5164),
            );
            $add(
                new Point(11431, 1044, 5164),
                new Point(12558, 1044, 5164),
                new Point(12558, 1044, 5104),
                new Point(11431, 1044, 5104),
            );
            $add(
                new Point(11431, 800, 5164),
                new Point(11431, 1044, 5164),
                new Point(11431, 1044, 5073),
                new Point(11431, 800, 5073),
            );
            $add(
                new Point(12585, 1048, 7052),
                new Point(12585, 1048, 7231),
                new Point(12484, 1048, 7231),
                new Point(12484, 1048, 7052),
            );
            $add(
                new Point(12484, 1048, 7052),
                new Point(12484, 1048, 7231),
                new Point(12484, 819, 7231),
                new Point(12484, 819, 7052),
            );
            $add(
                new Point(12585, 1048, 7052),
                new Point(12484, 1048, 7052),
                new Point(12484, 826, 7052),
                new Point(12585, 826, 7052),
            );

            // lower pit walls.001
            $add(
                new Point(11429, 797, 4389),
                new Point(11429, 797, 6444),
                new Point(11429, 902, 6444),
                new Point(11429, 902, 4389),
            );
            $add(
                new Point(11429, 902, 4389),
                new Point(11429, 902, 6444),
                new Point(11534, 902, 6444),
                new Point(11534, 902, 4389),
            );
            $add(
                new Point(11534, 902, 6444),
                new Point(11429, 902, 6444),
                new Point(11429, 824, 6444),
                new Point(11534, 824, 6444),
            );
            $add(
                new Point(11534, 902, 4389),
                new Point(11534, 902, 6444),
                new Point(11534, 256, 6444),
                new Point(11534, 256, 4389),
            );

            // pit main floor
            $add(
                new Point(9207, 835, 4697),
                new Point(11509, 835, 4697),
                new Point(11509, 835, 6430),
                new Point(9207, 835, 6430),
            );
            $add(
                new Point(11509, 835, 7887),
                new Point(11509, 835, 6430),
                new Point(12547, 835, 6430),
                new Point(12547, 835, 7887),
            );
            $add(
                new Point(9207, 835, 6430),
                new Point(11509, 835, 6430),
                new Point(11509, 835, 7887),
                new Point(9207, 835, 7887),
            );
            $add(
                new Point(12547, 1015, 7422),
                new Point(12547, 1015, 4966),
                new Point(13132, 1015, 4966),
                new Point(13132, 1015, 7422),
            );
            $add(
                new Point(12547, 835, 6437),
                new Point(11509, 835, 6437),
                new Point(11509, 289, 4797),
                new Point(12547, 289, 4797),
            );
            $add(
                new Point(12547, 289, 4797),
                new Point(11509, 289, 4797),
                new Point(11509, 289, 4567),
                new Point(12547, 289, 4567),
            );

            // pit walls
            $add(
                new Point(11481, 1562, 4733),
                new Point(10548, 1562, 4733),
                new Point(10548, 831, 4733),
                new Point(11481, 831, 4733),
            );
            $add(
                new Point(10576, 799, 4628),
                new Point(10576, 799, 6305),
                new Point(10576, 1481, 6305),
                new Point(10576, 1481, 4628),
            );
            $add(
                new Point(10781, 1487, 4935),
                new Point(10781, 831, 4935),
                new Point(10781, 831, 4699),
                new Point(10781, 1487, 4699),
            );
            $add(
                new Point(10781, 1487, 4935),
                new Point(10548, 1487, 4935),
                new Point(10548, 831, 4935),
                new Point(10781, 831, 4935),
            );
            $add(
                new Point(10576, 1481, 6305),
                new Point(10576, 799, 6305),
                new Point(10404, 799, 6453),
                new Point(10404, 1481, 6453),
            );
            $add(
                new Point(12971, 1638, 5109),
                new Point(11428, 1638, 5109),
                new Point(11428, 932, 5109),
                new Point(12971, 932, 5109),
            );
            $add(
                new Point(12506, 754, 7212),
                new Point(12506, 2143, 7212),
                new Point(13099, 2143, 7212),
                new Point(13099, 754, 7212),
            );
            $add(
                new Point(12956, 1764, 5685),
                new Point(12956, 1002, 5685),
                new Point(13076, 1002, 5685),
                new Point(13076, 1764, 5685),
            );
            $add(
                new Point(12956, 1002, 5103),
                new Point(12956, 1002, 5685),
                new Point(12956, 1764, 5685),
                new Point(12956, 1764, 5103),
            );
            $add(
                new Point(13055, 993, 5671),
                new Point(13055, 993, 7226),
                new Point(13055, 1755, 7226),
                new Point(13055, 1755, 5671),
            );
            $add(
                new Point(13055, 1744, 6495),
                new Point(13055, 1744, 7226),
                new Point(13055, 2093, 7226),
                new Point(13055, 2093, 6495),
            );
            $add(
                new Point(11430, 879, 4430),
                new Point(11430, 879, 5108),
                new Point(11430, 1811, 5108),
                new Point(11430, 1811, 4430),
            );
            $add(
                new Point(12506, 754, 7212),
                new Point(12506, 754, 8386),
                new Point(12506, 2143, 8386),
                new Point(12506, 2143, 7212),
            );
            $add(
                new Point(12506, 2143, 8386),
                new Point(12506, 754, 8386),
                new Point(12604, 754, 8386),
                new Point(12604, 2143, 8386),
            );
            $add(
                new Point(10404, 1481, 6453),
                new Point(10404, 799, 6453),
                new Point(9896, 799, 6453),
                new Point(9896, 1481, 6453),
            );
            $add(
                new Point(9896, 1481, 6453),
                new Point(9896, 799, 6453),
                new Point(9896, 799, 6172),
                new Point(9896, 1481, 6172),
            );
        }

        // Map - Props
        if (true) {

            // blue box
            $add(
                new Point(9988, 1190, 7403),
                new Point(10382, 1190, 7403),
                new Point(10382, 812, 7403),
                new Point(9988, 812, 7403),
            );
            $add(
                new Point(10382, 1190, 7403),
                new Point(10382, 1190, 7780),
                new Point(10382, 812, 7780),
                new Point(10382, 812, 7403),
            );
            $add(
                new Point(9988, 1190, 7780),
                new Point(9988, 1190, 7403),
                new Point(9988, 812, 7403),
                new Point(9988, 812, 7780),
            );
            $add(
                new Point(10382, 1190, 7780),
                new Point(10382, 1190, 7403),
                new Point(9988, 1190, 7403),
                new Point(9988, 1190, 7780),
            );
            $add(
                new Point(10590, 979, 7789),
                new Point(10590, 979, 7581),
                new Point(10374, 979, 7581),
                new Point(10374, 979, 7789),
            );
            $add(
                new Point(10374, 979, 7581),
                new Point(10590, 979, 7581),
                new Point(10590, 819, 7581),
                new Point(10374, 819, 7581),
            );
            $add(
                new Point(10590, 979, 7581),
                new Point(10590, 979, 7789),
                new Point(10590, 819, 7789),
                new Point(10590, 819, 7581),
            );

            // box
            $add(
                new Point(13068, 1188, 7018),
                new Point(12871, 1188, 7018),
                new Point(12871, 1188, 7215),
                new Point(13068, 1188, 7215),
            );
            $add(
                new Point(12871, 1188, 7215),
                new Point(12871, 1188, 7018),
                new Point(12871, 1009, 7018),
                new Point(12871, 1009, 7215),
            );
            $add(
                new Point(12871, 1188, 7018),
                new Point(13068, 1188, 7018),
                new Point(13068, 1009, 7018),
                new Point(12871, 1009, 7018),
            );

            // box.001
            $add(
                new Point(9529, 931, 11352),
                new Point(9529, 931, 11129),
                new Point(9315, 931, 11129),
                new Point(9315, 931, 11352),
            );
            $add(
                new Point(9315, 931, 11129),
                new Point(9529, 931, 11129),
                new Point(9529, 713, 11129),
                new Point(9315, 713, 11129),
            );
            $add(
                new Point(9529, 931, 11129),
                new Point(9529, 931, 11352),
                new Point(9529, 713, 11352),
                new Point(9529, 713, 11129),
            );

            // box.002
            $add(
                new Point(10905, 1285, 11630),
                new Point(10905, 1285, 11435),
                new Point(10709, 1285, 11435),
                new Point(10709, 1285, 11630),
            );
            $add(
                new Point(10709, 1285, 11435),
                new Point(10905, 1285, 11435),
                new Point(10905, 1068, 11435),
                new Point(10709, 1068, 11435),
            );
            $add(
                new Point(10905, 1285, 11435),
                new Point(10905, 1285, 11630),
                new Point(10905, 1068, 11630),
                new Point(10905, 1068, 11435),
            );
            $add(
                new Point(10709, 1285, 11435),
                new Point(10709, 1068, 11435),
                new Point(10709, 1068, 11680),
                new Point(10709, 1285, 11680),
            );
            $add(
                new Point(10709, 1285, 11680),
                new Point(10709, 1068, 11680),
                new Point(10764, 1068, 11867),
                new Point(10764, 1285, 11867),
            );
            $add(
                new Point(10764, 1285, 11867),
                new Point(10764, 1068, 11867),
                new Point(10953, 1068, 11815),
                new Point(10953, 1285, 11815),
            );
            $add(
                new Point(10953, 1285, 11815),
                new Point(10953, 1068, 11815),
                new Point(10902, 1068, 11628),
                new Point(10902, 1285, 11628),
            );
            $add(
                new Point(10915, 1285, 11678),
                new Point(10753, 1285, 11678),
                new Point(10753, 1285, 11823),
                new Point(10915, 1285, 11823),
            );

            // box.003
            $add(
                new Point(9898, 806, 11047),
                new Point(9898, 806, 11399),
                new Point(9898, 721, 11399),
                new Point(9898, 721, 11047),
            );
            $add(
                new Point(9898, 806, 11399),
                new Point(9898, 806, 11047),
                new Point(9608, 806, 11047),
                new Point(9608, 806, 11399),
            );
            $add(
                new Point(9608, 806, 11399),
                new Point(9608, 806, 11047),
                new Point(9608, 731, 11047),
                new Point(9608, 731, 11399),
            );
            $add(
                new Point(9608, 806, 11047),
                new Point(9898, 806, 11047),
                new Point(9898, 732, 11047),
                new Point(9608, 732, 11047),
            );

            // box.004
            $add(
                new Point(11482, 1285, 11773),
                new Point(11482, 1285, 11578),
                new Point(11286, 1285, 11578),
                new Point(11286, 1285, 11773),
            );
            $add(
                new Point(11286, 1285, 11578),
                new Point(11482, 1285, 11578),
                new Point(11482, 1068, 11578),
                new Point(11286, 1068, 11578),
            );
            $add(
                new Point(11482, 1285, 11578),
                new Point(11482, 1285, 11773),
                new Point(11482, 1068, 11773),
                new Point(11482, 1068, 11578),
            );
            $add(
                new Point(11482, 1285, 11773),
                new Point(11286, 1285, 11773),
                new Point(11286, 1060, 11773),
                new Point(11482, 1060, 11773),
            );
            $add(
                new Point(11286, 1285, 11773),
                new Point(11286, 1285, 11578),
                new Point(11286, 1060, 11578),
                new Point(11286, 1060, 11773),
            );

            // box.005
            $add(
                new Point(7256, 805, 10285),
                new Point(7256, 434, 10285),
                new Point(7256, 434, 10034),
                new Point(7256, 805, 10034),
            );
            $add(
                new Point(7450, 805, 10285),
                new Point(7450, 434, 10285),
                new Point(7256, 434, 10285),
                new Point(7256, 805, 10285),
            );
            $add(
                new Point(7256, 434, 10285),
                new Point(7450, 434, 10285),
                new Point(7450, 434, 10034),
                new Point(7256, 434, 10034),
            );
            $add(
                new Point(7450, 805, 10285),
                new Point(7256, 805, 10285),
                new Point(7256, 805, 10034),
                new Point(7450, 805, 10034),
            );
            $add(
                new Point(7450, 434, 10285),
                new Point(7450, 805, 10285),
                new Point(7450, 805, 10034),
                new Point(7450, 434, 10034),
            );

            // box.006
            $add(
                new Point(7211, 908, 10854),
                new Point(7211, 701, 10854),
                new Point(7211, 701, 11061),
                new Point(7211, 908, 11061),
            );
            $add(
                new Point(7015, 908, 10854),
                new Point(7015, 701, 10854),
                new Point(7211, 701, 10854),
                new Point(7211, 908, 10854),
            );
            $add(
                new Point(7211, 701, 10854),
                new Point(7015, 701, 10854),
                new Point(7015, 701, 11061),
                new Point(7211, 701, 11061),
            );
            $add(
                new Point(7015, 908, 10854),
                new Point(7211, 908, 10854),
                new Point(7211, 908, 11061),
                new Point(7015, 908, 11061),
            );
            $add(
                new Point(7015, 701, 10854),
                new Point(7015, 908, 10854),
                new Point(7015, 908, 11061),
                new Point(7015, 701, 11061),
            );

            // box.007
            $add(
                new Point(7307, 708, 10764),
                new Point(7307, 430, 10764),
                new Point(7307, 430, 11089),
                new Point(7307, 708, 11089),
            );
            $add(
                new Point(7014, 708, 10764),
                new Point(7014, 430, 10764),
                new Point(7307, 430, 10764),
                new Point(7307, 708, 10764),
            );
            $add(
                new Point(7307, 430, 10764),
                new Point(7014, 430, 10764),
                new Point(7014, 430, 11089),
                new Point(7307, 430, 11089),
            );
            $add(
                new Point(7014, 708, 10764),
                new Point(7307, 708, 10764),
                new Point(7307, 708, 11089),
                new Point(7014, 708, 11089),
            );
            $add(
                new Point(7014, 430, 10764),
                new Point(7014, 708, 10764),
                new Point(7014, 708, 11089),
                new Point(7014, 430, 11089),
            );

            // box.008
            $add(
                new Point(5309, 853, 8293),
                new Point(5309, 853, 8082),
                new Point(5087, 853, 8082),
                new Point(5087, 853, 8293),
            );
            $add(
                new Point(5087, 853, 8082),
                new Point(5309, 853, 8082),
                new Point(5309, 470, 8082),
                new Point(5087, 470, 8082),
            );
            $add(
                new Point(5309, 853, 8082),
                new Point(5309, 853, 8293),
                new Point(5309, 470, 8293),
                new Point(5309, 470, 8082),
            );
            $add(
                new Point(5309, 853, 8293),
                new Point(5087, 853, 8293),
                new Point(5087, 456, 8293),
                new Point(5309, 456, 8293),
            );
            $add(
                new Point(5087, 853, 8293),
                new Point(5087, 853, 8082),
                new Point(5087, 456, 8082),
                new Point(5087, 456, 8293),
            );

            // box.009
            $add(
                new Point(2318, 1191, 9715),
                new Point(2318, 801, 9715),
                new Point(2123, 801, 9715),
                new Point(2123, 1191, 9715),
            );
            $add(
                new Point(2123, 1191, 9715),
                new Point(2123, 801, 9715),
                new Point(2123, 801, 9515),
                new Point(2123, 1191, 9515),
            );
            $add(
                new Point(2318, 801, 9715),
                new Point(2318, 1191, 9715),
                new Point(2318, 1191, 9488),
                new Point(2318, 801, 9488),
            );
            $add(
                new Point(2318, 1191, 9715),
                new Point(2123, 1191, 9715),
                new Point(2123, 1191, 9507),
                new Point(2318, 1191, 9507),
            );

            // box.010
            $add(
                new Point(1642, 1073, 12610),
                new Point(1411, 1073, 12610),
                new Point(1411, 1073, 12828),
                new Point(1642, 1073, 12828),
            );
            $add(
                new Point(1411, 1073, 12610),
                new Point(1642, 1073, 12610),
                new Point(1642, 869, 12610),
                new Point(1411, 869, 12610),
            );
            $add(
                new Point(1411, 1073, 12828),
                new Point(1411, 1073, 12610),
                new Point(1411, 895, 12610),
                new Point(1411, 895, 12828),
            );
            $add(
                new Point(1642, 1073, 12828),
                new Point(1411, 1073, 12828),
                new Point(1411, 907, 12828),
                new Point(1642, 907, 12828),
            );
            $add(
                new Point(1642, 1073, 12610),
                new Point(1642, 1073, 12828),
                new Point(1642, 869, 12828),
                new Point(1642, 869, 12610),
            );

            // box.011
            $add(
                new Point(1593, 1074, 11500),
                new Point(1362, 1074, 11500),
                new Point(1362, 1074, 11718),
                new Point(1593, 1074, 11718),
            );
            $add(
                new Point(1362, 1074, 11500),
                new Point(1593, 1074, 11500),
                new Point(1593, 906, 11500),
                new Point(1362, 906, 11500),
            );
            $add(
                new Point(1362, 906, 11500),
                new Point(1593, 906, 11500),
                new Point(1593, 904, 11470),
                new Point(1362, 904, 11470),
            );
            $add(
                new Point(1593, 1074, 11718),
                new Point(1362, 1074, 11718),
                new Point(1362, 907, 11718),
                new Point(1593, 907, 11718),
            );
            $add(
                new Point(1593, 1074, 11500),
                new Point(1593, 1074, 11718),
                new Point(1593, 869, 11718),
                new Point(1593, 869, 11500),
            );

            // box.012
            $add(
                new Point(4518, 641, 8748),
                new Point(4518, 641, 8524),
                new Point(4042, 641, 8524),
                new Point(4042, 641, 8748),
            );
            $add(
                new Point(4042, 641, 8524),
                new Point(4518, 641, 8524),
                new Point(4518, 449, 8524),
                new Point(4042, 449, 8524),
            );
            $add(
                new Point(4518, 641, 8524),
                new Point(4518, 641, 8748),
                new Point(4518, 449, 8748),
                new Point(4518, 449, 8524),
            );
            $add(
                new Point(4518, 641, 8748),
                new Point(4042, 641, 8748),
                new Point(4042, 442, 8748),
                new Point(4518, 442, 8748),
            );
            $add(
                new Point(4042, 641, 8748),
                new Point(4042, 641, 8524),
                new Point(4042, 442, 8524),
                new Point(4042, 442, 8748),
            );

            // door
            $add(
                new Point(6737, 965, 9057),
                new Point(6475, 965, 9171),
                new Point(6475, 431, 9171),
                new Point(6737, 431, 9057),
            );
            $add(
                new Point(6731, 962, 9011),
                new Point(6468, 962, 9135),
                new Point(6468, 431, 9135),
                new Point(6731, 431, 9011),
            );

            // door.001
            $add(
                new Point(6409, 962, 8900),
                new Point(6147, 962, 9024),
                new Point(6147, 431, 9024),
                new Point(6409, 431, 8900),
            );
            $add(
                new Point(6419, 965, 8928),
                new Point(6157, 965, 9052),
                new Point(6157, 431, 9052),
                new Point(6419, 431, 8928),
            );

            // door.002
            $add(
                new Point(9575, 1299, 6455),
                new Point(9312, 1299, 6330),
                new Point(9312, 822, 6330),
                new Point(9575, 822, 6455),
            );
            $add(
                new Point(9575, 1299, 6485),
                new Point(9312, 1299, 6361),
                new Point(9312, 822, 6361),
                new Point(9575, 822, 6485),
            );

            // door.003
            $add(
                new Point(9900, 1299, 6367),
                new Point(9637, 1299, 6243),
                new Point(9637, 822, 6243),
                new Point(9900, 822, 6367),
            );
            $add(
                new Point(9900, 1299, 6337),
                new Point(9637, 1299, 6213),
                new Point(9637, 822, 6213),
                new Point(9900, 822, 6337),
            );

            // door.004
            $add(
                new Point(9900, 1354, 5008),
                new Point(9643, 1354, 4872),
                new Point(9643, 822, 4872),
                new Point(9900, 822, 5008),
            );
            $add(
                new Point(9898, 1356, 5038),
                new Point(9642, 1356, 4902),
                new Point(9642, 822, 4902),
                new Point(9898, 822, 5038),
            );

            // door.005
            $add(
                new Point(3863, 1343, 10788),
                new Point(3742, 1343, 11054),
                new Point(3742, 811, 11054),
                new Point(3863, 811, 10788),
            );
            $add(
                new Point(3891, 1345, 10797),
                new Point(3768, 1345, 11056),
                new Point(3768, 811, 11056),
                new Point(3891, 811, 10797),
            );

            // door.006
            $add(
                new Point(3770, 1345, 10482),
                new Point(3647, 1345, 10741),
                new Point(3647, 811, 10741),
                new Point(3770, 811, 10482),
            );
            $add(
                new Point(3741, 1343, 10464),
                new Point(3620, 1343, 10730),
                new Point(3620, 811, 10730),
                new Point(3741, 811, 10464),
            );

            // door.007
            $add(
                new Point(9575, 1356, 5145),
                new Point(9319, 1356, 5008),
                new Point(9319, 822, 5008),
                new Point(9575, 822, 5145),
            );
            $add(
                new Point(9586, 1354, 5112),
                new Point(9329, 1354, 4976),
                new Point(9329, 822, 4976),
                new Point(9586, 822, 5112),
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
                new Point(6938, 811, 5989),
                new Point(6938, 2296, 5989),
            );

            // prop.005
            $add(
                new Point(3378, 2299, 5541),
                new Point(3378, 814, 5541),
                new Point(3470, 814, 5541),
                new Point(3470, 2299, 5541),
            );
            $add(
                new Point(3470, 2299, 5541),
                new Point(3470, 814, 5541),
                new Point(3470, 814, 5486),
                new Point(3470, 2299, 5486),
            );
            $add(
                new Point(3378, 814, 5541),
                new Point(3378, 2299, 5541),
                new Point(3378, 2299, 5486),
                new Point(3378, 814, 5486),
            );
            $add(
                new Point(3470, 2299, 5486),
                new Point(3470, 814, 5486),
                new Point(3385, 814, 5486),
                new Point(3385, 2299, 5486),
            );

            // prop.006
            $add(
                new Point(1947, 2299, 5542),
                new Point(1947, 814, 5542),
                new Point(2039, 814, 5542),
                new Point(2039, 2299, 5542),
            );
            $add(
                new Point(2039, 2299, 5542),
                new Point(2039, 814, 5542),
                new Point(2039, 814, 5487),
                new Point(2039, 2299, 5487),
            );
            $add(
                new Point(1947, 814, 5542),
                new Point(1947, 2299, 5542),
                new Point(1947, 2299, 5487),
                new Point(1947, 814, 5487),
            );
            $add(
                new Point(2039, 2299, 5487),
                new Point(2039, 814, 5487),
                new Point(1955, 814, 5487),
                new Point(1955, 2299, 5487),
            );

            // prop.007
            $add(
                new Point(4860, 2192, 11482),
                new Point(4860, 707, 11482),
                new Point(4951, 707, 11482),
                new Point(4951, 2192, 11482),
            );
            $add(
                new Point(4951, 2192, 11482),
                new Point(4951, 707, 11482),
                new Point(4951, 707, 11427),
                new Point(4951, 2192, 11427),
            );
            $add(
                new Point(4860, 707, 11482),
                new Point(4860, 2192, 11482),
                new Point(4860, 2192, 11427),
                new Point(4860, 707, 11427),
            );
            $add(
                new Point(4951, 2192, 11427),
                new Point(4951, 707, 11427),
                new Point(4867, 707, 11427),
                new Point(4867, 2192, 11427),
            );

            // prop.008
            $add(
                new Point(8941, 2255, 3158),
                new Point(8941, 770, 3158),
                new Point(9033, 770, 3158),
                new Point(9033, 2255, 3158),
            );
            $add(
                new Point(9033, 2255, 3158),
                new Point(9033, 770, 3158),
                new Point(9033, 770, 3103),
                new Point(9033, 2255, 3103),
            );
            $add(
                new Point(8941, 770, 3158),
                new Point(8941, 2255, 3158),
                new Point(8941, 2255, 3103),
                new Point(8941, 770, 3103),
            );
            $add(
                new Point(9033, 2255, 3103),
                new Point(9033, 770, 3103),
                new Point(8949, 770, 3103),
                new Point(8949, 2255, 3103),
            );

            // prop.009
            $add(
                new Point(9242, 1211, 11973),
                new Point(9242, 1076, 11973),
                new Point(9304, 1076, 11973),
                new Point(9304, 1211, 11973),
            );
            $add(
                new Point(9304, 1211, 11973),
                new Point(9304, 1076, 11973),
                new Point(9304, 1076, 11911),
                new Point(9304, 1211, 11911),
            );
            $add(
                new Point(9242, 1076, 11973),
                new Point(9242, 1211, 11973),
                new Point(9242, 1211, 11911),
                new Point(9242, 1076, 11911),
            );
            $add(
                new Point(9304, 1211, 11911),
                new Point(9304, 1076, 11911),
                new Point(9247, 1076, 11911),
                new Point(9247, 1211, 11911),
            );
            $add(
                new Point(9242, 1211, 11973),
                new Point(9304, 1211, 11973),
                new Point(9304, 1211, 11910),
                new Point(9242, 1211, 11910),
            );

            // prop.010
            $add(
                new Point(9315, 1211, 11850),
                new Point(9315, 1076, 11850),
                new Point(9377, 1076, 11850),
                new Point(9377, 1211, 11850),
            );
            $add(
                new Point(9377, 1211, 11850),
                new Point(9377, 1076, 11850),
                new Point(9377, 1076, 11789),
                new Point(9377, 1211, 11789),
            );
            $add(
                new Point(9315, 1076, 11850),
                new Point(9315, 1211, 11850),
                new Point(9315, 1211, 11789),
                new Point(9315, 1076, 11789),
            );
            $add(
                new Point(9377, 1211, 11789),
                new Point(9377, 1076, 11789),
                new Point(9320, 1076, 11789),
                new Point(9320, 1211, 11789),
            );
            $add(
                new Point(9315, 1211, 11850),
                new Point(9377, 1211, 11850),
                new Point(9377, 1211, 11788),
                new Point(9315, 1211, 11788),
            );

            // prop.011
            $add(
                new Point(10246, 1234, 12376),
                new Point(10246, 1099, 12376),
                new Point(10308, 1099, 12376),
                new Point(10308, 1234, 12376),
            );
            $add(
                new Point(10308, 1234, 12376),
                new Point(10308, 1099, 12376),
                new Point(10308, 1099, 12314),
                new Point(10308, 1234, 12314),
            );
            $add(
                new Point(10246, 1099, 12376),
                new Point(10246, 1234, 12376),
                new Point(10246, 1234, 12314),
                new Point(10246, 1099, 12314),
            );
            $add(
                new Point(10308, 1234, 12314),
                new Point(10308, 1099, 12314),
                new Point(10251, 1099, 12314),
                new Point(10251, 1234, 12314),
            );
            $add(
                new Point(10246, 1234, 12376),
                new Point(10308, 1234, 12376),
                new Point(10308, 1234, 12313),
                new Point(10246, 1234, 12313),
            );

            // prop.012
            $add(
                new Point(9860, 1211, 11465),
                new Point(9860, 1076, 11465),
                new Point(9923, 1076, 11465),
                new Point(9923, 1211, 11465),
            );
            $add(
                new Point(9923, 1211, 11465),
                new Point(9923, 1076, 11465),
                new Point(9923, 1076, 11403),
                new Point(9923, 1211, 11403),
            );
            $add(
                new Point(9860, 1076, 11465),
                new Point(9860, 1211, 11465),
                new Point(9860, 1211, 11403),
                new Point(9860, 1076, 11403),
            );
            $add(
                new Point(9923, 1211, 11403),
                new Point(9923, 1076, 11403),
                new Point(9866, 1076, 11403),
                new Point(9866, 1211, 11403),
            );
            $add(
                new Point(9860, 1211, 11465),
                new Point(9923, 1211, 11465),
                new Point(9923, 1211, 11402),
                new Point(9860, 1211, 11402),
            );

            // prop.013
            $add(
                new Point(9989, 1211, 11465),
                new Point(9989, 1076, 11465),
                new Point(10051, 1076, 11465),
                new Point(10051, 1211, 11465),
            );
            $add(
                new Point(10051, 1211, 11465),
                new Point(10051, 1076, 11465),
                new Point(10051, 1076, 11403),
                new Point(10051, 1211, 11403),
            );
            $add(
                new Point(9989, 1076, 11465),
                new Point(9989, 1211, 11465),
                new Point(9989, 1211, 11403),
                new Point(9989, 1076, 11403),
            );
            $add(
                new Point(10051, 1211, 11403),
                new Point(10051, 1076, 11403),
                new Point(9994, 1076, 11403),
                new Point(9994, 1211, 11403),
            );
            $add(
                new Point(9989, 1211, 11465),
                new Point(10051, 1211, 11465),
                new Point(10051, 1211, 11402),
                new Point(9989, 1211, 11402),
            );

            // prop.015
            $add(
                new Point(10294, 1234, 12279),
                new Point(10294, 1099, 12279),
                new Point(10357, 1099, 12279),
                new Point(10357, 1234, 12279),
            );
            $add(
                new Point(10357, 1234, 12279),
                new Point(10357, 1099, 12279),
                new Point(10357, 1099, 12218),
                new Point(10357, 1234, 12218),
            );
            $add(
                new Point(10294, 1099, 12279),
                new Point(10294, 1234, 12279),
                new Point(10294, 1234, 12218),
                new Point(10294, 1099, 12218),
            );
            $add(
                new Point(10357, 1234, 12218),
                new Point(10357, 1099, 12218),
                new Point(10300, 1099, 12218),
                new Point(10300, 1234, 12218),
            );
            $add(
                new Point(10294, 1234, 12279),
                new Point(10357, 1234, 12279),
                new Point(10357, 1234, 12217),
                new Point(10294, 1234, 12217),
            );

            // prop.016
            $add(
                new Point(10090, 1168, 12373),
                new Point(10090, 1034, 12373),
                new Point(10230, 1034, 12373),
                new Point(10230, 1168, 12373),
            );
            $add(
                new Point(10230, 1168, 12373),
                new Point(10230, 1034, 12373),
                new Point(10230, 1034, 12312),
                new Point(10230, 1168, 12312),
            );
            $add(
                new Point(10090, 1034, 12373),
                new Point(10090, 1168, 12373),
                new Point(10090, 1168, 12312),
                new Point(10090, 1034, 12312),
            );
            $add(
                new Point(10230, 1168, 12312),
                new Point(10230, 1034, 12312),
                new Point(10102, 1034, 12312),
                new Point(10102, 1168, 12312),
            );
            $add(
                new Point(10090, 1168, 12373),
                new Point(10230, 1168, 12373),
                new Point(10230, 1168, 12311),
                new Point(10090, 1168, 12311),
            );

            // prop.017
            $add(
                new Point(11226, 1234, 11341),
                new Point(11226, 1099, 11341),
                new Point(11288, 1099, 11341),
                new Point(11288, 1234, 11341),
            );
            $add(
                new Point(11288, 1234, 11341),
                new Point(11288, 1099, 11341),
                new Point(11288, 1099, 11279),
                new Point(11288, 1234, 11279),
            );
            $add(
                new Point(11226, 1099, 11341),
                new Point(11226, 1234, 11341),
                new Point(11226, 1234, 11279),
                new Point(11226, 1099, 11279),
            );
            $add(
                new Point(11288, 1234, 11279),
                new Point(11288, 1099, 11279),
                new Point(11231, 1099, 11279),
                new Point(11231, 1234, 11279),
            );
            $add(
                new Point(11226, 1234, 11341),
                new Point(11288, 1234, 11341),
                new Point(11288, 1234, 11278),
                new Point(11226, 1234, 11278),
            );

            // prop.018
            $add(
                new Point(11659, 1337, 12622),
                new Point(11597, 1337, 12622),
                new Point(11597, 1181, 12622),
                new Point(11659, 1181, 12622),
            );
            $add(
                new Point(11597, 1337, 12685),
                new Point(11659, 1337, 12685),
                new Point(11659, 1181, 12685),
                new Point(11597, 1181, 12685),
            );
            $add(
                new Point(11659, 1337, 12685),
                new Point(11659, 1337, 12622),
                new Point(11659, 1181, 12622),
                new Point(11659, 1181, 12685),
            );
            $add(
                new Point(11597, 1181, 12685),
                new Point(11659, 1181, 12685),
                new Point(11659, 1181, 12622),
                new Point(11597, 1181, 12622),
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
                new Point(11597, 1181, 12685),
                new Point(11597, 1181, 12622),
            );

            // prop.019
            $add(
                new Point(11727, 1337, 12699),
                new Point(11789, 1337, 12699),
                new Point(11789, 1179, 12699),
                new Point(11727, 1179, 12699),
            );
            $add(
                new Point(11789, 1337, 12699),
                new Point(11789, 1337, 12637),
                new Point(11789, 1179, 12637),
                new Point(11789, 1179, 12699),
            );
            $add(
                new Point(11727, 1337, 12637),
                new Point(11727, 1337, 12699),
                new Point(11727, 1179, 12699),
                new Point(11727, 1179, 12637),
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
                new Point(11727, 1179, 12637),
                new Point(11789, 1179, 12637),
            );

            // prop.020
            $add(
                new Point(11466, 885, 8006),
                new Point(11466, 750, 8006),
                new Point(11528, 750, 8006),
                new Point(11528, 885, 8006),
            );
            $add(
                new Point(11528, 885, 8006),
                new Point(11528, 750, 8006),
                new Point(11528, 750, 7870),
                new Point(11528, 885, 7870),
            );
            $add(
                new Point(11466, 750, 8006),
                new Point(11466, 885, 8006),
                new Point(11466, 885, 7870),
                new Point(11466, 750, 7870),
            );
            $add(
                new Point(11528, 885, 7870),
                new Point(11528, 750, 7870),
                new Point(11471, 750, 7870),
                new Point(11471, 885, 7870),
            );
            $add(
                new Point(11466, 885, 8006),
                new Point(11528, 885, 8006),
                new Point(11528, 885, 7867),
                new Point(11466, 885, 7867),
            );

            // prop.021
            $add(
                new Point(10001, 952, 6732),
                new Point(10001, 817, 6732),
                new Point(10063, 817, 6732),
                new Point(10063, 952, 6732),
            );
            $add(
                new Point(10063, 952, 6732),
                new Point(10063, 817, 6732),
                new Point(10063, 817, 6671),
                new Point(10063, 952, 6671),
            );
            $add(
                new Point(10001, 817, 6732),
                new Point(10001, 952, 6732),
                new Point(10001, 952, 6671),
                new Point(10001, 817, 6671),
            );
            $add(
                new Point(10063, 952, 6671),
                new Point(10063, 817, 6671),
                new Point(10006, 817, 6671),
                new Point(10006, 952, 6671),
            );
            $add(
                new Point(10001, 952, 6732),
                new Point(10063, 952, 6732),
                new Point(10063, 952, 6670),
                new Point(10001, 952, 6670),
            );

            // prop.022
            $add(
                new Point(10140, 952, 6564),
                new Point(10140, 817, 6564),
                new Point(10202, 817, 6564),
                new Point(10202, 952, 6564),
            );
            $add(
                new Point(10202, 952, 6564),
                new Point(10202, 817, 6564),
                new Point(10202, 817, 6503),
                new Point(10202, 952, 6503),
            );
            $add(
                new Point(10140, 817, 6564),
                new Point(10140, 952, 6564),
                new Point(10140, 952, 6503),
                new Point(10140, 817, 6503),
            );
            $add(
                new Point(10202, 952, 6503),
                new Point(10202, 817, 6503),
                new Point(10145, 817, 6503),
                new Point(10145, 952, 6503),
            );
            $add(
                new Point(10140, 952, 6564),
                new Point(10202, 952, 6564),
                new Point(10202, 952, 6502),
                new Point(10140, 952, 6502),
            );

            // prop.023
            $add(
                new Point(1556, 1343, 2158),
                new Point(1556, 1209, 2158),
                new Point(1618, 1209, 2158),
                new Point(1618, 1343, 2158),
            );
            $add(
                new Point(1618, 1343, 2158),
                new Point(1618, 1209, 2158),
                new Point(1618, 1209, 2096),
                new Point(1618, 1343, 2096),
            );
            $add(
                new Point(1556, 1209, 2158),
                new Point(1556, 1343, 2158),
                new Point(1556, 1343, 2096),
                new Point(1556, 1209, 2096),
            );
            $add(
                new Point(1618, 1343, 2096),
                new Point(1618, 1209, 2096),
                new Point(1561, 1209, 2096),
                new Point(1561, 1343, 2096),
            );
            $add(
                new Point(1556, 1343, 2158),
                new Point(1618, 1343, 2158),
                new Point(1618, 1343, 2095),
                new Point(1556, 1343, 2095),
            );

            // prop.024
            $add(
                new Point(2004, 1196, 2554),
                new Point(2004, 1062, 2554),
                new Point(2094, 1062, 2554),
                new Point(2094, 1196, 2554),
            );
            $add(
                new Point(2094, 1196, 2554),
                new Point(2094, 1062, 2554),
                new Point(2094, 1062, 2410),
                new Point(2094, 1196, 2410),
            );
            $add(
                new Point(2004, 1062, 2554),
                new Point(2004, 1196, 2554),
                new Point(2004, 1196, 2410),
                new Point(2004, 1062, 2410),
            );
            $add(
                new Point(2094, 1196, 2410),
                new Point(2094, 1062, 2410),
                new Point(2012, 1062, 2410),
                new Point(2012, 1196, 2410),
            );
            $add(
                new Point(2004, 1196, 2554),
                new Point(2094, 1196, 2554),
                new Point(2094, 1196, 2408),
                new Point(2004, 1196, 2408),
            );

            // prop.025
            $add(
                new Point(3009, 1328, 3580),
                new Point(3009, 1193, 3580),
                new Point(3071, 1193, 3580),
                new Point(3071, 1328, 3580),
            );
            $add(
                new Point(3071, 1328, 3580),
                new Point(3071, 1193, 3580),
                new Point(3071, 1193, 3518),
                new Point(3071, 1328, 3518),
            );
            $add(
                new Point(3009, 1193, 3580),
                new Point(3009, 1328, 3580),
                new Point(3009, 1328, 3518),
                new Point(3009, 1193, 3518),
            );
            $add(
                new Point(3071, 1328, 3518),
                new Point(3071, 1193, 3518),
                new Point(3014, 1193, 3518),
                new Point(3014, 1328, 3518),
            );
            $add(
                new Point(3009, 1328, 3580),
                new Point(3071, 1328, 3580),
                new Point(3071, 1328, 3517),
                new Point(3009, 1328, 3517),
            );

            // prop.026
            $add(
                new Point(2906, 1328, 3652),
                new Point(2906, 1193, 3652),
                new Point(2968, 1193, 3652),
                new Point(2968, 1328, 3652),
            );
            $add(
                new Point(2968, 1328, 3652),
                new Point(2968, 1193, 3652),
                new Point(2968, 1193, 3590),
                new Point(2968, 1328, 3590),
            );
            $add(
                new Point(2906, 1193, 3652),
                new Point(2906, 1328, 3652),
                new Point(2906, 1328, 3590),
                new Point(2906, 1193, 3590),
            );
            $add(
                new Point(2968, 1328, 3590),
                new Point(2968, 1193, 3590),
                new Point(2911, 1193, 3590),
                new Point(2911, 1328, 3590),
            );
            $add(
                new Point(2906, 1328, 3652),
                new Point(2968, 1328, 3652),
                new Point(2968, 1328, 3589),
                new Point(2906, 1328, 3589),
            );

            // prop.027
            $add(
                new Point(2258, 1006, 5680),
                new Point(1714, 1006, 5680),
                new Point(1714, 880, 5680),
                new Point(2258, 880, 5680),
            );
            $add(
                new Point(1714, 1006, 5840),
                new Point(2258, 1006, 5840),
                new Point(2258, 880, 5840),
                new Point(1714, 880, 5840),
            );
            $add(
                new Point(2258, 1006, 5840),
                new Point(2258, 1006, 5680),
                new Point(2258, 880, 5680),
                new Point(2258, 880, 5840),
            );
            $add(
                new Point(1876, 996, 5910),
                new Point(1876, 890, 5910),
                new Point(1978, 890, 5910),
                new Point(1978, 996, 5910),
            );
            $add(
                new Point(1714, 1006, 5840),
                new Point(1714, 1006, 5680),
                new Point(2258, 1006, 5680),
                new Point(2258, 1006, 5840),
            );
            $add(
                new Point(1714, 1006, 5680),
                new Point(1714, 1006, 5840),
                new Point(1714, 880, 5840),
                new Point(1714, 880, 5680),
            );
            $add(
                new Point(1876, 996, 5823),
                new Point(1978, 996, 5823),
                new Point(1978, 890, 5823),
                new Point(1876, 890, 5823),
            );
            $add(
                new Point(1876, 996, 5910),
                new Point(1978, 996, 5910),
                new Point(1978, 996, 5823),
                new Point(1876, 996, 5823),
            );
            $add(
                new Point(1978, 890, 5910),
                new Point(1876, 890, 5910),
                new Point(1876, 890, 5823),
                new Point(1978, 890, 5823),
            );
            $add(
                new Point(1876, 890, 5910),
                new Point(1876, 996, 5910),
                new Point(1876, 996, 5823),
                new Point(1876, 890, 5823),
            );
            $add(
                new Point(1978, 996, 5910),
                new Point(1978, 890, 5910),
                new Point(1978, 890, 5823),
                new Point(1978, 996, 5823),
            );

            // prop.028
            $add(
                new Point(1717, 1092, 5851),
                new Point(1717, 958, 5851),
                new Point(1815, 958, 5851),
                new Point(1815, 1092, 5851),
            );
            $add(
                new Point(1815, 1092, 5851),
                new Point(1815, 958, 5851),
                new Point(1815, 958, 5767),
                new Point(1815, 1092, 5767),
            );
            $add(
                new Point(1717, 958, 5851),
                new Point(1717, 1092, 5851),
                new Point(1717, 1092, 5767),
                new Point(1717, 958, 5767),
            );
            $add(
                new Point(1815, 1092, 5767),
                new Point(1815, 958, 5767),
                new Point(1725, 958, 5767),
                new Point(1725, 1092, 5767),
            );
            $add(
                new Point(1717, 1092, 5851),
                new Point(1815, 1092, 5851),
                new Point(1815, 1092, 5766),
                new Point(1717, 1092, 5766),
            );

            // prop.029
            $add(
                new Point(3009, 1024, 6250),
                new Point(3009, 756, 6250),
                new Point(3283, 756, 6250),
                new Point(3283, 1024, 6250),
            );
            $add(
                new Point(3283, 1024, 6250),
                new Point(3283, 756, 6250),
                new Point(3283, 756, 6084),
                new Point(3283, 1024, 6084),
            );
            $add(
                new Point(3009, 756, 6250),
                new Point(3009, 1024, 6250),
                new Point(3009, 1024, 6084),
                new Point(3009, 756, 6084),
            );
            $add(
                new Point(3283, 1024, 6084),
                new Point(3283, 756, 6084),
                new Point(3013, 756, 6084),
                new Point(3013, 1024, 6084),
            );
            $add(
                new Point(3009, 1024, 6250),
                new Point(3283, 1024, 6250),
                new Point(3283, 1024, 6081),
                new Point(3009, 1024, 6081),
            );

            // prop.030
            $add(
                new Point(2129, 1037, 12084),
                new Point(2129, 902, 12084),
                new Point(2191, 902, 12084),
                new Point(2191, 1037, 12084),
            );
            $add(
                new Point(2191, 1037, 12084),
                new Point(2191, 902, 12084),
                new Point(2191, 902, 12023),
                new Point(2191, 1037, 12023),
            );
            $add(
                new Point(2129, 902, 12084),
                new Point(2129, 1037, 12084),
                new Point(2129, 1037, 12023),
                new Point(2129, 902, 12023),
            );
            $add(
                new Point(2191, 1037, 12023),
                new Point(2191, 902, 12023),
                new Point(2134, 902, 12023),
                new Point(2134, 1037, 12023),
            );
            $add(
                new Point(2129, 1037, 12084),
                new Point(2191, 1037, 12084),
                new Point(2191, 1037, 12022),
                new Point(2129, 1037, 12022),
            );

            // prop.031
            $add(
                new Point(2210, 1037, 12168),
                new Point(2210, 902, 12168),
                new Point(2272, 902, 12168),
                new Point(2272, 1037, 12168),
            );
            $add(
                new Point(2272, 1037, 12168),
                new Point(2272, 902, 12168),
                new Point(2272, 902, 12106),
                new Point(2272, 1037, 12106),
            );
            $add(
                new Point(2210, 902, 12168),
                new Point(2210, 1037, 12168),
                new Point(2210, 1037, 12106),
                new Point(2210, 902, 12106),
            );
            $add(
                new Point(2272, 1037, 12106),
                new Point(2272, 902, 12106),
                new Point(2215, 902, 12106),
                new Point(2215, 1037, 12106),
            );
            $add(
                new Point(2210, 1037, 12168),
                new Point(2272, 1037, 12168),
                new Point(2272, 1037, 12105),
                new Point(2210, 1037, 12105),
            );

            // prop.032
            $add(
                new Point(2340, 1037, 12519),
                new Point(2340, 902, 12519),
                new Point(2402, 902, 12519),
                new Point(2402, 1037, 12519),
            );
            $add(
                new Point(2402, 1037, 12519),
                new Point(2402, 902, 12519),
                new Point(2402, 902, 12457),
                new Point(2402, 1037, 12457),
            );
            $add(
                new Point(2340, 902, 12519),
                new Point(2340, 1037, 12519),
                new Point(2340, 1037, 12457),
                new Point(2340, 902, 12457),
            );
            $add(
                new Point(2402, 1037, 12457),
                new Point(2402, 902, 12457),
                new Point(2345, 902, 12457),
                new Point(2345, 1037, 12457),
            );
            $add(
                new Point(2340, 1037, 12519),
                new Point(2402, 1037, 12519),
                new Point(2402, 1037, 12456),
                new Point(2340, 1037, 12456),
            );

            // prop.033
            $add(
                new Point(3581, 947, 11303),
                new Point(3581, 813, 11303),
                new Point(3643, 813, 11303),
                new Point(3643, 947, 11303),
            );
            $add(
                new Point(3643, 947, 11303),
                new Point(3643, 813, 11303),
                new Point(3643, 813, 11242),
                new Point(3643, 947, 11242),
            );
            $add(
                new Point(3581, 813, 11303),
                new Point(3581, 947, 11303),
                new Point(3581, 947, 11242),
                new Point(3581, 813, 11242),
            );
            $add(
                new Point(3643, 947, 11242),
                new Point(3643, 813, 11242),
                new Point(3586, 813, 11242),
                new Point(3586, 947, 11242),
            );
            $add(
                new Point(3581, 947, 11303),
                new Point(3643, 947, 11303),
                new Point(3643, 947, 11241),
                new Point(3581, 947, 11241),
            );

            // prop.034
            $add(
                new Point(3573, 947, 11193),
                new Point(3573, 813, 11193),
                new Point(3635, 813, 11193),
                new Point(3635, 947, 11193),
            );
            $add(
                new Point(3635, 947, 11193),
                new Point(3635, 813, 11193),
                new Point(3635, 813, 11131),
                new Point(3635, 947, 11131),
            );
            $add(
                new Point(3573, 813, 11193),
                new Point(3573, 947, 11193),
                new Point(3573, 947, 11131),
                new Point(3573, 813, 11131),
            );
            $add(
                new Point(3635, 947, 11131),
                new Point(3635, 813, 11131),
                new Point(3578, 813, 11131),
                new Point(3578, 947, 11131),
            );
            $add(
                new Point(3573, 947, 11193),
                new Point(3635, 947, 11193),
                new Point(3635, 947, 11130),
                new Point(3573, 947, 11130),
            );

            // prop.035
            $add(
                new Point(2124, 1092, 11436),
                new Point(2124, 1092, 11147),
                new Point(2416, 1092, 11147),
                new Point(2416, 1092, 11436),
            );
            $add(
                new Point(2124, 1092, 11147),
                new Point(2124, 1092, 11436),
                new Point(2124, 808, 11436),
                new Point(2124, 808, 11147),
            );
            $add(
                new Point(2124, 1092, 11436),
                new Point(2416, 1092, 11436),
                new Point(2416, 808, 11436),
                new Point(2124, 808, 11436),
            );
            $add(
                new Point(2416, 1092, 11436),
                new Point(2416, 1092, 11147),
                new Point(2416, 808, 11147),
                new Point(2416, 808, 11436),
            );
            $add(
                new Point(2416, 1092, 11147),
                new Point(2124, 1092, 11147),
                new Point(2124, 808, 11147),
                new Point(2416, 808, 11147),
            );

            // prop.036
            $add(
                new Point(2703, 1188, 11899),
                new Point(2703, 1188, 11674),
                new Point(2899, 1188, 11674),
                new Point(2899, 1188, 11899),
            );
            $add(
                new Point(2703, 1188, 11674),
                new Point(2703, 1188, 11899),
                new Point(2703, 814, 11899),
                new Point(2703, 814, 11674),
            );
            $add(
                new Point(2703, 1188, 11899),
                new Point(2899, 1188, 11899),
                new Point(2899, 814, 11899),
                new Point(2703, 814, 11899),
            );
            $add(
                new Point(2899, 1188, 11899),
                new Point(2899, 1188, 11674),
                new Point(2899, 814, 11674),
                new Point(2899, 814, 11899),
            );
            $add(
                new Point(2899, 1188, 11674),
                new Point(2703, 1188, 11674),
                new Point(2703, 814, 11674),
                new Point(2899, 814, 11674),
            );

            // prop.037
            $add(
                new Point(3449, 1145, 12306),
                new Point(3449, 1145, 11901),
                new Point(3851, 1145, 11901),
                new Point(3851, 1145, 12306),
            );
            $add(
                new Point(3449, 1145, 11901),
                new Point(3449, 1145, 12306),
                new Point(3449, 814, 12306),
                new Point(3449, 814, 11901),
            );
            $add(
                new Point(3449, 1145, 12306),
                new Point(3851, 1145, 12306),
                new Point(3851, 814, 12306),
                new Point(3449, 814, 12306),
            );
            $add(
                new Point(3851, 1145, 12306),
                new Point(3851, 1145, 11901),
                new Point(3851, 814, 11901),
                new Point(3851, 814, 12306),
            );
            $add(
                new Point(3851, 1145, 11901),
                new Point(3449, 1145, 11901),
                new Point(3449, 814, 11901),
                new Point(3851, 814, 11901),
            );

            // prop.038
            $add(
                new Point(3311, 1093, 12071),
                new Point(3311, 1093, 11924),
                new Point(3501, 1093, 11924),
                new Point(3501, 1093, 12071),
            );
            $add(
                new Point(3311, 1093, 11924),
                new Point(3311, 1093, 12071),
                new Point(3311, 923, 12071),
                new Point(3311, 923, 11924),
            );
            $add(
                new Point(3311, 1093, 12071),
                new Point(3501, 1093, 12071),
                new Point(3501, 923, 12071),
                new Point(3311, 923, 12071),
            );
            $add(
                new Point(3501, 1093, 12071),
                new Point(3501, 1093, 11924),
                new Point(3501, 923, 11924),
                new Point(3501, 923, 12071),
            );
            $add(
                new Point(3501, 1093, 11924),
                new Point(3311, 1093, 11924),
                new Point(3311, 923, 11924),
                new Point(3501, 923, 11924),
            );

            // prop.039
            $add(
                new Point(3244, 949, 12127),
                new Point(3244, 949, 11910),
                new Point(3456, 949, 11910),
                new Point(3456, 949, 12127),
            );
            $add(
                new Point(3244, 949, 11910),
                new Point(3244, 949, 12127),
                new Point(3244, 779, 12127),
                new Point(3244, 779, 11910),
            );
            $add(
                new Point(3244, 949, 12127),
                new Point(3456, 949, 12127),
                new Point(3456, 779, 12127),
                new Point(3244, 779, 12127),
            );
            $add(
                new Point(3456, 949, 12127),
                new Point(3456, 949, 11910),
                new Point(3456, 779, 11910),
                new Point(3456, 779, 12127),
            );
            $add(
                new Point(3456, 949, 11910),
                new Point(3244, 949, 11910),
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

            // prop.041
            $add(
                new Point(2858, 984, 12493),
                new Point(2858, 984, 12343),
                new Point(3030, 984, 12343),
                new Point(3030, 984, 12493),
            );

            // prop.042
            $add(
                new Point(2734, 981, 9271),
                new Point(2873, 981, 9409),
                new Point(2873, 815, 9409),
                new Point(2734, 815, 9271),
            );
            $add(
                new Point(2873, 981, 9409),
                new Point(3012, 981, 9269),
                new Point(3012, 815, 9269),
                new Point(2873, 815, 9409),
            );
            $add(
                new Point(3012, 981, 9269),
                new Point(2872, 981, 9132),
                new Point(2872, 815, 9132),
                new Point(3012, 815, 9269),
            );
            $add(
                new Point(2872, 981, 9132),
                new Point(2734, 981, 9271),
                new Point(2734, 815, 9271),
                new Point(2872, 815, 9132),
            );

            // prop.043
            $add(
                new Point(2809, 996, 9327),
                new Point(2809, 996, 9199),
                new Point(2945, 996, 9199),
                new Point(2945, 996, 9327),
            );

            // prop.044
            $add(
                new Point(2636, 911, 9569),
                new Point(3160, 911, 9877),
                new Point(3160, 821, 9877),
                new Point(2636, 821, 9569),
            );
            $add(
                new Point(3160, 911, 9877),
                new Point(3283, 911, 9654),
                new Point(3283, 821, 9654),
                new Point(3160, 821, 9877),
            );
            $add(
                new Point(3283, 911, 9654),
                new Point(2747, 911, 9362),
                new Point(2747, 821, 9362),
                new Point(3283, 821, 9654),
            );
            $add(
                new Point(2747, 911, 9362),
                new Point(2636, 911, 9569),
                new Point(2636, 821, 9569),
                new Point(2747, 821, 9362),
            );

            // prop.045
            $add(
                new Point(2871, 980, 9679),
                new Point(3010, 980, 9679),
                new Point(3010, 980, 9549),
                new Point(2871, 980, 9549),
            );
            $add(
                new Point(2871, 980, 9679),
                new Point(2871, 980, 9549),
                new Point(2871, 877, 9549),
                new Point(2871, 877, 9679),
            );
            $add(
                new Point(3010, 980, 9679),
                new Point(2871, 980, 9679),
                new Point(2871, 877, 9679),
                new Point(3010, 877, 9679),
            );
            $add(
                new Point(3010, 980, 9549),
                new Point(3010, 980, 9679),
                new Point(3010, 877, 9679),
                new Point(3010, 877, 9549),
            );
            $add(
                new Point(2871, 980, 9549),
                new Point(3010, 980, 9549),
                new Point(3010, 877, 9549),
                new Point(2871, 877, 9549),
            );

            // prop.046
            $add(
                new Point(3035, 919, 9780),
                new Point(3035, 919, 9625),
                new Point(3201, 919, 9625),
                new Point(3201, 919, 9780),
            );

            // prop.047
            $add(
                new Point(2717, 912, 9592),
                new Point(2717, 912, 9456),
                new Point(2862, 912, 9456),
                new Point(2862, 912, 9592),
            );

            // prop.050
            $add(
                new Point(4993, 736, 11803),
                new Point(5216, 736, 11803),
                new Point(5216, 736, 11608),
                new Point(4993, 736, 11608),
            );
            $add(
                new Point(5216, 736, 11608),
                new Point(5216, 736, 11803),
                new Point(5216, 524, 11803),
                new Point(5216, 524, 11608),
            );
            $add(
                new Point(4993, 736, 11608),
                new Point(5216, 736, 11608),
                new Point(5216, 524, 11608),
                new Point(4993, 524, 11608),
            );
            $add(
                new Point(4993, 736, 11803),
                new Point(4993, 736, 11608),
                new Point(4993, 524, 11608),
                new Point(4993, 524, 11803),
            );
            $add(
                new Point(5216, 736, 11803),
                new Point(4993, 736, 11803),
                new Point(4993, 524, 11803),
                new Point(5216, 524, 11803),
            );

            // prop.051
            $add(
                new Point(5029, 720, 11546),
                new Point(5203, 720, 11546),
                new Point(5203, 720, 11394),
                new Point(5029, 720, 11394),
            );
            $add(
                new Point(5203, 720, 11546),
                new Point(5029, 720, 11546),
                new Point(5029, 507, 11546),
                new Point(5203, 507, 11546),
            );
            $add(
                new Point(5203, 720, 11394),
                new Point(5203, 720, 11546),
                new Point(5203, 507, 11546),
                new Point(5203, 507, 11394),
            );
            $add(
                new Point(5029, 720, 11394),
                new Point(5203, 720, 11394),
                new Point(5203, 507, 11394),
                new Point(5029, 507, 11394),
            );
            $add(
                new Point(5029, 720, 11546),
                new Point(5029, 720, 11394),
                new Point(5029, 507, 11394),
                new Point(5029, 507, 11546),
            );

            // prop.052
            $add(
                new Point(4728, 831, 11732),
                new Point(4951, 831, 11732),
                new Point(4951, 831, 11537),
                new Point(4728, 831, 11537),
            );
            $add(
                new Point(4951, 831, 11732),
                new Point(4728, 831, 11732),
                new Point(4728, 717, 11732),
                new Point(4951, 717, 11732),
            );
            $add(
                new Point(4951, 831, 11537),
                new Point(4951, 831, 11732),
                new Point(4951, 717, 11732),
                new Point(4951, 717, 11537),
            );
            $add(
                new Point(4728, 831, 11537),
                new Point(4951, 831, 11537),
                new Point(4951, 717, 11537),
                new Point(4728, 717, 11537),
            );
            $add(
                new Point(4728, 831, 11732),
                new Point(4728, 831, 11537),
                new Point(4728, 717, 11537),
                new Point(4728, 717, 11732),
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
                new Point(2411, 1400, 7117),
                new Point(2411, 1400, 7216),
                new Point(2411, 905, 7216),
                new Point(2411, 905, 7117),
            );
            $add(
                new Point(2411, 1400, 7216),
                new Point(2510, 1400, 7216),
                new Point(2510, 905, 7216),
                new Point(2411, 905, 7216),
            );
            $add(
                new Point(2510, 1400, 7216),
                new Point(2510, 1400, 7117),
                new Point(2510, 905, 7117),
                new Point(2510, 905, 7216),
            );
            $add(
                new Point(2510, 1400, 7117),
                new Point(2411, 1400, 7117),
                new Point(2411, 905, 7117),
                new Point(2510, 905, 7117),
            );

            // prop.057
            $add(
                new Point(2411, 1400, 7981),
                new Point(2411, 1400, 8081),
                new Point(2411, 905, 8081),
                new Point(2411, 905, 7981),
            );
            $add(
                new Point(2411, 1400, 8081),
                new Point(2510, 1400, 8081),
                new Point(2510, 905, 8081),
                new Point(2411, 905, 8081),
            );
            $add(
                new Point(2510, 1400, 8081),
                new Point(2510, 1400, 7981),
                new Point(2510, 905, 7981),
                new Point(2510, 905, 8081),
            );
            $add(
                new Point(2510, 1400, 7981),
                new Point(2411, 1400, 7981),
                new Point(2411, 905, 7981),
                new Point(2510, 905, 7981),
            );

            // prop.058
            $add(
                new Point(3584, 1074, 7991),
                new Point(3584, 1074, 7766),
                new Point(3859, 1074, 7766),
                new Point(3859, 1074, 7991),
            );
            $add(
                new Point(3584, 1074, 7766),
                new Point(3584, 1074, 7991),
                new Point(3584, 892, 7991),
                new Point(3584, 892, 7766),
            );
            $add(
                new Point(3584, 1074, 7991),
                new Point(3859, 1074, 7991),
                new Point(3859, 892, 7991),
                new Point(3584, 892, 7991),
            );
            $add(
                new Point(3859, 1074, 7991),
                new Point(3859, 1074, 7766),
                new Point(3859, 892, 7766),
                new Point(3859, 892, 7991),
            );
            $add(
                new Point(3859, 1074, 7766),
                new Point(3584, 1074, 7766),
                new Point(3584, 892, 7766),
                new Point(3859, 892, 7766),
            );

            // prop.059
            $add(
                new Point(3851, 1123, 7991),
                new Point(3851, 1123, 7766),
                new Point(4055, 1123, 7766),
                new Point(4055, 1123, 7991),
            );
            $add(
                new Point(3851, 1123, 7766),
                new Point(3851, 1123, 7991),
                new Point(3851, 940, 7991),
                new Point(3851, 940, 7766),
            );
            $add(
                new Point(3851, 1123, 7991),
                new Point(4055, 1123, 7991),
                new Point(4055, 940, 7991),
                new Point(3851, 940, 7991),
            );
            $add(
                new Point(4055, 1123, 7991),
                new Point(4055, 1123, 7766),
                new Point(4055, 940, 7766),
                new Point(4055, 940, 7991),
            );
            $add(
                new Point(4055, 1123, 7766),
                new Point(3851, 1123, 7766),
                new Point(3851, 940, 7766),
                new Point(4055, 940, 7766),
            );

            // prop.060
            $add(
                new Point(4073, 1082, 7807),
                new Point(4073, 1082, 7726),
                new Point(4159, 1082, 7726),
                new Point(4159, 1082, 7807),
            );
            $add(
                new Point(4073, 1082, 7726),
                new Point(4073, 1082, 7807),
                new Point(4073, 946, 7807),
                new Point(4073, 946, 7726),
            );
            $add(
                new Point(4073, 1082, 7807),
                new Point(4159, 1082, 7807),
                new Point(4159, 946, 7807),
                new Point(4073, 946, 7807),
            );
            $add(
                new Point(4159, 1082, 7807),
                new Point(4159, 1082, 7726),
                new Point(4159, 946, 7726),
                new Point(4159, 946, 7807),
            );
            $add(
                new Point(4159, 1082, 7726),
                new Point(4073, 1082, 7726),
                new Point(4073, 946, 7726),
                new Point(4159, 946, 7726),
            );

            // prop.061
            $add(
                new Point(2326, 1036, 8038),
                new Point(2326, 1036, 7956),
                new Point(2412, 1036, 7956),
                new Point(2412, 1036, 8038),
            );
            $add(
                new Point(2326, 1036, 7956),
                new Point(2326, 1036, 8038),
                new Point(2326, 899, 8038),
                new Point(2326, 899, 7956),
            );
            $add(
                new Point(2326, 1036, 8038),
                new Point(2412, 1036, 8038),
                new Point(2412, 899, 8038),
                new Point(2326, 899, 8038),
            );
            $add(
                new Point(2412, 1036, 8038),
                new Point(2412, 1036, 7956),
                new Point(2412, 899, 7956),
                new Point(2412, 899, 8038),
            );
            $add(
                new Point(2412, 1036, 7956),
                new Point(2326, 1036, 7956),
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
                new Point(3171, 1095, 3690),
                new Point(3171, 1095, 4353),
                new Point(3473, 1095, 4353),
                new Point(3473, 1095, 3690),
            );
            $add(
                new Point(3171, 1095, 4353),
                new Point(3171, 1095, 3690),
                new Point(3171, 830, 3690),
                new Point(3171, 830, 4353),
            );
            $add(
                new Point(3171, 1095, 3690),
                new Point(3473, 1095, 3690),
                new Point(3473, 830, 3690),
                new Point(3171, 830, 3690),
            );
            $add(
                new Point(3473, 1095, 3690),
                new Point(3473, 1095, 4353),
                new Point(3473, 830, 4353),
                new Point(3473, 830, 3690),
            );
            $add(
                new Point(3473, 1095, 4353),
                new Point(3171, 1095, 4353),
                new Point(3171, 830, 4353),
                new Point(3473, 830, 4353),
            );

            // prop.064
            $add(
                new Point(2373, 1370, 3225),
                new Point(2373, 1370, 3705),
                new Point(2603, 1370, 3705),
                new Point(2603, 1370, 3225),
            );
            $add(
                new Point(2373, 1370, 3705),
                new Point(2373, 1370, 3225),
                new Point(2373, 1203, 3225),
                new Point(2373, 1203, 3705),
            );
            $add(
                new Point(2373, 1370, 3225),
                new Point(2603, 1370, 3225),
                new Point(2603, 1203, 3225),
                new Point(2373, 1203, 3225),
            );
            $add(
                new Point(2603, 1370, 3225),
                new Point(2603, 1370, 3705),
                new Point(2603, 1203, 3705),
                new Point(2603, 1203, 3225),
            );
            $add(
                new Point(2603, 1370, 3705),
                new Point(2373, 1370, 3705),
                new Point(2373, 1203, 3705),
                new Point(2603, 1203, 3705),
            );

            // prop.065
            $add(
                new Point(2597, 1370, 3455),
                new Point(2597, 1370, 3702),
                new Point(2856, 1370, 3702),
                new Point(2856, 1370, 3455),
            );
            $add(
                new Point(2597, 1370, 3702),
                new Point(2597, 1370, 3455),
                new Point(2597, 1203, 3455),
                new Point(2597, 1203, 3702),
            );
            $add(
                new Point(2597, 1370, 3455),
                new Point(2856, 1370, 3455),
                new Point(2856, 1203, 3455),
                new Point(2597, 1203, 3455),
            );
            $add(
                new Point(2856, 1370, 3455),
                new Point(2856, 1370, 3702),
                new Point(2856, 1203, 3702),
                new Point(2856, 1203, 3455),
            );
            $add(
                new Point(2856, 1370, 3702),
                new Point(2597, 1370, 3702),
                new Point(2597, 1203, 3702),
                new Point(2856, 1203, 3702),
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
                new Point(6156, 997, 3566),
                new Point(6156, 997, 3952),
                new Point(6348, 997, 3952),
                new Point(6348, 997, 3566),
            );
            $add(
                new Point(6156, 997, 3952),
                new Point(6156, 997, 3566),
                new Point(6156, 816, 3566),
                new Point(6156, 816, 3952),
            );
            $add(
                new Point(6156, 997, 3566),
                new Point(6348, 997, 3566),
                new Point(6348, 816, 3566),
                new Point(6156, 816, 3566),
            );
            $add(
                new Point(6348, 997, 3566),
                new Point(6348, 997, 3952),
                new Point(6348, 816, 3952),
                new Point(6348, 816, 3566),
            );
            $add(
                new Point(6348, 997, 3952),
                new Point(6156, 997, 3952),
                new Point(6156, 816, 3952),
                new Point(6348, 816, 3952),
            );

            // prop.068
            $add(
                new Point(6156, 1189, 3711),
                new Point(6156, 1189, 3904),
                new Point(6348, 1189, 3904),
                new Point(6348, 1189, 3711),
            );
            $add(
                new Point(6156, 1189, 3904),
                new Point(6156, 1189, 3711),
                new Point(6156, 979, 3711),
                new Point(6156, 979, 3904),
            );
            $add(
                new Point(6156, 1189, 3711),
                new Point(6348, 1189, 3711),
                new Point(6348, 979, 3711),
                new Point(6156, 979, 3711),
            );
            $add(
                new Point(6348, 1189, 3711),
                new Point(6348, 1189, 3904),
                new Point(6348, 979, 3904),
                new Point(6348, 979, 3711),
            );
            $add(
                new Point(6348, 1189, 3904),
                new Point(6156, 1189, 3904),
                new Point(6156, 979, 3904),
                new Point(6348, 979, 3904),
            );

            // prop.069
            $add(
                new Point(8909, 1093, 8189),
                new Point(8909, 1093, 8468),
                new Point(9194, 1093, 8468),
                new Point(9194, 1093, 8189),
            );
            $add(
                new Point(8909, 1093, 8468),
                new Point(8909, 1093, 8189),
                new Point(8909, 789, 8189),
                new Point(8909, 789, 8468),
            );
            $add(
                new Point(8909, 1093, 8189),
                new Point(9194, 1093, 8189),
                new Point(9194, 789, 8189),
                new Point(8909, 789, 8189),
            );
            $add(
                new Point(9194, 1093, 8189),
                new Point(9194, 1093, 8468),
                new Point(9194, 789, 8468),
                new Point(9194, 789, 8189),
            );
            $add(
                new Point(9194, 1093, 8468),
                new Point(8909, 1093, 8468),
                new Point(8909, 789, 8468),
                new Point(9194, 789, 8468),
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
                new Point(11719, 1029, 11409),
                new Point(11719, 1029, 11207),
                new Point(11719, 833, 11207),
                new Point(11719, 833, 11409),
            );
            $add(
                new Point(11719, 1029, 11207),
                new Point(11508, 1029, 11207),
                new Point(11508, 833, 11207),
                new Point(11719, 833, 11207),
            );
            $add(
                new Point(11508, 1029, 11409),
                new Point(11719, 1029, 11409),
                new Point(11719, 833, 11409),
                new Point(11508, 833, 11409),
            );
            $add(
                new Point(11719, 1029, 11207),
                new Point(11719, 1029, 11409),
                new Point(11508, 1029, 11409),
                new Point(11508, 1029, 11207),
            );
            $add(
                new Point(11719, 1029, 11409),
                new Point(11508, 1029, 11409),
                new Point(11508, 1080, 11409),
                new Point(11719, 1080, 11409),
            );
            $add(
                new Point(11719, 1080, 11409),
                new Point(11508, 1080, 11409),
                new Point(11508, 1024, 11217),
                new Point(11719, 1024, 11217),
            );

            // prop.072
            $add(
                new Point(8082, 1003, 3637),
                new Point(8082, 1003, 4126),
                new Point(8341, 1003, 4126),
                new Point(8341, 1003, 3637),
            );
            $add(
                new Point(8082, 1003, 4126),
                new Point(8082, 1003, 3637),
                new Point(8082, 836, 3637),
                new Point(8082, 836, 4126),
            );
            $add(
                new Point(8082, 1003, 3637),
                new Point(8341, 1003, 3637),
                new Point(8341, 836, 3637),
                new Point(8082, 836, 3637),
            );
            $add(
                new Point(8341, 1003, 3637),
                new Point(8341, 1003, 4126),
                new Point(8341, 836, 4126),
                new Point(8341, 836, 3637),
            );
            $add(
                new Point(8341, 1003, 4126),
                new Point(8082, 1003, 4126),
                new Point(8082, 836, 4126),
                new Point(8341, 836, 4126),
            );

            // prop.073
            $add(
                new Point(8082, 1202, 3877),
                new Point(8082, 1202, 4111),
                new Point(8326, 1202, 4111),
                new Point(8326, 1202, 3877),
            );
            $add(
                new Point(8082, 1202, 4111),
                new Point(8082, 1202, 3877),
                new Point(8082, 988, 3877),
                new Point(8082, 988, 4111),
            );
            $add(
                new Point(8082, 1202, 3877),
                new Point(8326, 1202, 3877),
                new Point(8326, 988, 3877),
                new Point(8082, 988, 3877),
            );
            $add(
                new Point(8326, 1202, 3877),
                new Point(8326, 1202, 4111),
                new Point(8326, 988, 4111),
                new Point(8326, 988, 3877),
            );
            $add(
                new Point(8326, 1202, 4111),
                new Point(8082, 1202, 4111),
                new Point(8082, 988, 4111),
                new Point(8326, 988, 4111),
            );

            // prop.074
            $add(
                new Point(8077, 929, 3072),
                new Point(8077, 929, 3649),
                new Point(8336, 929, 3649),
                new Point(8336, 929, 3072),
            );
            $add(
                new Point(8077, 929, 3649),
                new Point(8077, 929, 3072),
                new Point(8077, 829, 3072),
                new Point(8077, 829, 3649),
            );
            $add(
                new Point(8077, 929, 3072),
                new Point(8336, 929, 3072),
                new Point(8336, 829, 3072),
                new Point(8077, 829, 3072),
            );
            $add(
                new Point(8336, 929, 3072),
                new Point(8336, 929, 3649),
                new Point(8336, 829, 3649),
                new Point(8336, 829, 3072),
            );
            $add(
                new Point(8336, 929, 3649),
                new Point(8077, 929, 3649),
                new Point(8077, 829, 3649),
                new Point(8336, 829, 3649),
            );

            // prop.075
            $add(
                new Point(8074, 999, 3229),
                new Point(8074, 999, 3410),
                new Point(8337, 999, 3410),
                new Point(8337, 999, 3229),
            );
            $add(
                new Point(8074, 999, 3410),
                new Point(8074, 999, 3229),
                new Point(8074, 820, 3229),
                new Point(8074, 820, 3410),
            );
            $add(
                new Point(8074, 999, 3229),
                new Point(8337, 999, 3229),
                new Point(8337, 820, 3229),
                new Point(8074, 820, 3229),
            );
            $add(
                new Point(8337, 999, 3229),
                new Point(8337, 999, 3410),
                new Point(8337, 820, 3410),
                new Point(8337, 820, 3229),
            );
            $add(
                new Point(8337, 999, 3410),
                new Point(8074, 999, 3410),
                new Point(8074, 820, 3410),
                new Point(8337, 820, 3410),
            );

            // prop.076
            $add(
                new Point(9757, 932, 3785),
                new Point(9633, 932, 4232),
                new Point(9633, 833, 4232),
                new Point(9757, 833, 3785),
            );
            $add(
                new Point(9633, 932, 4232),
                new Point(9855, 932, 4292),
                new Point(9855, 833, 4292),
                new Point(9633, 833, 4232),
            );
            $add(
                new Point(9855, 932, 4292),
                new Point(9964, 932, 3839),
                new Point(9964, 833, 3839),
                new Point(9855, 833, 4292),
            );
            $add(
                new Point(9964, 932, 3839),
                new Point(9757, 932, 3785),
                new Point(9757, 833, 3785),
                new Point(9964, 833, 3839),
            );

            // prop.077
            $add(
                new Point(7580, 709, 11052),
                new Point(7786, 709, 11258),
                new Point(7786, 435, 11258),
                new Point(7580, 435, 11052),
            );
            $add(
                new Point(7786, 709, 11258),
                new Point(7994, 709, 11052),
                new Point(7994, 435, 11052),
                new Point(7786, 435, 11258),
            );
            $add(
                new Point(7994, 709, 11052),
                new Point(7785, 709, 10845),
                new Point(7785, 435, 10845),
                new Point(7994, 435, 11052),
            );
            $add(
                new Point(7785, 709, 10845),
                new Point(7580, 709, 11052),
                new Point(7580, 435, 11052),
                new Point(7785, 435, 10845),
            );

            // prop.078
            $add(
                new Point(9708, 935, 4238),
                new Point(9708, 935, 4123),
                new Point(9830, 935, 4123),
                new Point(9830, 935, 4238),
            );

            // prop.079
            $add(
                new Point(9729, 988, 4085),
                new Point(9940, 988, 4085),
                new Point(9940, 988, 3887),
                new Point(9729, 988, 3887),
            );
            $add(
                new Point(9729, 988, 3887),
                new Point(9940, 988, 3887),
                new Point(9940, 893, 3887),
                new Point(9729, 893, 3887),
            );
            $add(
                new Point(9729, 988, 4085),
                new Point(9729, 988, 3887),
                new Point(9729, 893, 3887),
                new Point(9729, 893, 4085),
            );
            $add(
                new Point(9940, 988, 4085),
                new Point(9729, 988, 4085),
                new Point(9729, 893, 4085),
                new Point(9940, 893, 4085),
            );
            $add(
                new Point(9940, 988, 3887),
                new Point(9940, 988, 4085),
                new Point(9940, 893, 4085),
                new Point(9940, 893, 3887),
            );

            // prop.080
            $add(
                new Point(7671, 710, 11159),
                new Point(7671, 710, 10962),
                new Point(7882, 710, 10962),
                new Point(7882, 710, 11159),
            );

            // stairs
            $add(
                new Point(12605, 1012, 7153),
                new Point(12838, 1012, 7153),
                new Point(12805, 1047, 7218),
                new Point(12637, 1047, 7218),
            );

            // stairs.001
            $add(
                new Point(12922, 1012, 5489),
                new Point(12922, 1012, 5257),
                new Point(12963, 1029, 5289),
                new Point(12963, 1029, 5457),
            );

            // stairs.002
            $add(
                new Point(12557, 1019, 6642),
                new Point(12557, 1019, 7038),
                new Point(12397, 837, 7112),
                new Point(12397, 837, 6569),
            );

            // stairs.003
            $add(
                new Point(2138, 909, 11441),
                new Point(1740, 909, 11441),
                new Point(1685, 807, 11312),
                new Point(2193, 807, 11312),
            );

            // stairs.005
            $add(
                new Point(3044, 902, 5763),
                new Point(2355, 902, 5763),
                new Point(2319, 811, 5667),
                new Point(3081, 811, 5667),
            );
        }

        // Map - T spawn
        if (true) {

            // spawn to long doors walls
            $add(
                new Point(9989, 1603, 3003),
                new Point(8838, 1603, 3003),
                new Point(8838, 769, 3003),
                new Point(9989, 769, 3003),
            );
            $add(
                new Point(9989, 1603, 3003),
                new Point(9989, 769, 3003),
                new Point(9989, 769, 4909),
                new Point(9989, 1603, 4909),
            );
            $add(
                new Point(9989, 1603, 4909),
                new Point(9989, 769, 4909),
                new Point(9843, 769, 4909),
                new Point(9843, 1603, 4909),
            );
            $add(
                new Point(8838, 769, 3003),
                new Point(8838, 1603, 3003),
                new Point(8838, 1603, 1473),
                new Point(8838, 769, 1473),
            );
            $add(
                new Point(8838, 769, 1473),
                new Point(8838, 1603, 1473),
                new Point(7485, 1603, 1473),
                new Point(7485, 769, 1473),
            );
            $add(
                new Point(7485, 769, 1473),
                new Point(7485, 1603, 1473),
                new Point(7293, 1603, 1259),
                new Point(7293, 769, 1259),
            );
            $add(
                new Point(7293, 769, 1259),
                new Point(7293, 1603, 1259),
                new Point(7293, 1603, 1075),
                new Point(7293, 769, 1075),
            );
            $add(
                new Point(9989, 769, 4909),
                new Point(9989, 1603, 4909),
                new Point(9989, 1603, 6023),
                new Point(9989, 769, 6023),
            );
            $add(
                new Point(9843, 1603, 4909),
                new Point(9843, 769, 4909),
                new Point(9843, 769, 5006),
                new Point(9843, 1603, 5006),
            );
            $add(
                new Point(9843, 1603, 5006),
                new Point(9843, 769, 5006),
                new Point(10002, 769, 5006),
                new Point(10002, 1603, 5006),
            );

            // spawn wall
            $add(
                new Point(7351, 1975, 1108),
                new Point(1812, 1975, 1108),
                new Point(1812, 911, 1108),
                new Point(7351, 911, 1108),
            );

            // t awp stairs
            $add(
                new Point(7270, 987, 2320),
                new Point(7341, 987, 2320),
                new Point(7341, 987, 2225),
                new Point(7270, 987, 2225),
            );
            $add(
                new Point(7411, 952, 2320),
                new Point(7482, 952, 2320),
                new Point(7482, 952, 2225),
                new Point(7411, 952, 2225),
            );
            $add(
                new Point(7482, 812, 2320),
                new Point(7482, 952, 2320),
                new Point(7411, 952, 2320),
                new Point(7411, 812, 2320),
            );
            $add(
                new Point(7693, 812, 2320),
                new Point(7693, 901, 2320),
                new Point(7622, 901, 2320),
                new Point(7622, 812, 2320),
            );
            $add(
                new Point(6355, 1238, 2225),
                new Point(6355, 1238, 2320),
                new Point(6355, 1190, 2320),
                new Point(6355, 1190, 2225),
            );
            $add(
                new Point(7270, 812, 2320),
                new Point(7270, 1005, 2320),
                new Point(7200, 1005, 2320),
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
                new Point(7552, 938, 2225),
                new Point(7482, 938, 2225),
            );
            $add(
                new Point(7059, 1051, 2320),
                new Point(7129, 1051, 2320),
                new Point(7129, 1051, 2225),
                new Point(7059, 1051, 2225),
            );
            $add(
                new Point(7622, 901, 2320),
                new Point(7693, 901, 2320),
                new Point(7693, 901, 2225),
                new Point(7622, 901, 2225),
            );
            $add(
                new Point(6918, 1089, 2320),
                new Point(6989, 1089, 2320),
                new Point(6989, 1089, 2225),
                new Point(6918, 1089, 2225),
            );
            $add(
                new Point(7341, 969, 2320),
                new Point(7411, 969, 2320),
                new Point(7411, 969, 2225),
                new Point(7341, 969, 2225),
            );
            $add(
                new Point(6989, 812, 2320),
                new Point(6989, 1089, 2320),
                new Point(6918, 1089, 2320),
                new Point(6918, 812, 2320),
            );
            $add(
                new Point(7552, 921, 2320),
                new Point(7622, 921, 2320),
                new Point(7622, 921, 2225),
                new Point(7552, 921, 2225),
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
                new Point(6284, 1256, 2225),
                new Point(6214, 1256, 2225),
            );
            $add(
                new Point(7200, 1005, 2320),
                new Point(7270, 1005, 2320),
                new Point(7270, 1005, 2225),
                new Point(7200, 1005, 2225),
            );
            $add(
                new Point(7059, 1051, 2225),
                new Point(7129, 1051, 2225),
                new Point(7129, 896, 2225),
                new Point(7059, 896, 2225),
            );
            $add(
                new Point(7341, 969, 2225),
                new Point(7411, 969, 2225),
                new Point(7411, 824, 2225),
                new Point(7341, 824, 2225),
            );
            $add(
                new Point(6707, 1153, 2225),
                new Point(6707, 1153, 2320),
                new Point(6707, 1066, 2320),
                new Point(6707, 1066, 2225),
            );
            $add(
                new Point(7482, 938, 2225),
                new Point(7552, 938, 2225),
                new Point(7552, 783, 2225),
                new Point(7482, 783, 2225),
            );
            $add(
                new Point(6214, 1256, 2225),
                new Point(6284, 1256, 2225),
                new Point(6284, 1113, 2225),
                new Point(6214, 1113, 2225),
            );
            $add(
                new Point(7200, 1005, 2225),
                new Point(7270, 1005, 2225),
                new Point(7270, 864, 2225),
                new Point(7200, 864, 2225),
            );
            $add(
                new Point(7552, 921, 2225),
                new Point(7622, 921, 2225),
                new Point(7622, 766, 2225),
                new Point(7552, 766, 2225),
            );
            $add(
                new Point(7411, 952, 2225),
                new Point(7482, 952, 2225),
                new Point(7482, 797, 2225),
                new Point(7411, 797, 2225),
            );
            $add(
                new Point(6918, 1089, 2225),
                new Point(6989, 1089, 2225),
                new Point(6989, 934, 2225),
                new Point(6918, 934, 2225),
            );
            $add(
                new Point(7622, 901, 2225),
                new Point(7693, 901, 2225),
                new Point(7693, 746, 2225),
                new Point(7622, 746, 2225),
            );
            $add(
                new Point(6989, 1089, 2225),
                new Point(6989, 1089, 2320),
                new Point(6989, 988, 2320),
                new Point(6989, 988, 2225),
            );
            $add(
                new Point(6636, 1153, 2225),
                new Point(6707, 1153, 2225),
                new Point(6707, 1012, 2225),
                new Point(6636, 1012, 2225),
            );
            $add(
                new Point(7129, 1051, 2225),
                new Point(7129, 1051, 2320),
                new Point(7129, 950, 2320),
                new Point(7129, 950, 2225),
            );
            $add(
                new Point(7411, 969, 2225),
                new Point(7411, 969, 2320),
                new Point(7411, 878, 2320),
                new Point(7411, 878, 2225),
            );
            $add(
                new Point(7693, 901, 2225),
                new Point(7693, 901, 2320),
                new Point(7693, 800, 2320),
                new Point(7693, 800, 2225),
            );
            $add(
                new Point(6848, 1123, 2225),
                new Point(6848, 1123, 2320),
                new Point(6848, 1022, 2320),
                new Point(6848, 1022, 2225),
            );
            $add(
                new Point(6284, 1256, 2225),
                new Point(6284, 1256, 2320),
                new Point(6284, 1168, 2320),
                new Point(6284, 1168, 2225),
            );
            $add(
                new Point(7482, 952, 2225),
                new Point(7482, 952, 2320),
                new Point(7482, 851, 2320),
                new Point(7482, 851, 2225),
            );
            $add(
                new Point(7622, 921, 2225),
                new Point(7622, 921, 2320),
                new Point(7622, 820, 2320),
                new Point(7622, 820, 2225),
            );
            $add(
                new Point(7270, 1005, 2225),
                new Point(7270, 1005, 2320),
                new Point(7270, 918, 2320),
                new Point(7270, 918, 2225),
            );
            $add(
                new Point(7552, 938, 2225),
                new Point(7552, 938, 2320),
                new Point(7552, 837, 2320),
                new Point(7552, 837, 2225),
            );
            $add(
                new Point(6707, 1141, 2225),
                new Point(6777, 1141, 2225),
                new Point(6777, 975, 2225),
                new Point(6707, 975, 2225),
            );
            $add(
                new Point(6707, 1141, 2320),
                new Point(6777, 1141, 2320),
                new Point(6777, 1141, 2225),
                new Point(6707, 1141, 2225),
            );
            $add(
                new Point(6777, 1123, 2320),
                new Point(6848, 1123, 2320),
                new Point(6848, 1123, 2225),
                new Point(6777, 1123, 2225),
            );
            $add(
                new Point(6777, 1123, 2225),
                new Point(6848, 1123, 2225),
                new Point(6848, 975, 2225),
                new Point(6777, 975, 2225),
            );
            $add(
                new Point(6848, 812, 2320),
                new Point(6848, 1123, 2320),
                new Point(6777, 1123, 2320),
                new Point(6777, 812, 2320),
            );
            $add(
                new Point(7270, 987, 2225),
                new Point(7341, 987, 2225),
                new Point(7341, 862, 2225),
                new Point(7270, 862, 2225),
            );
            $add(
                new Point(7341, 812, 2320),
                new Point(7341, 987, 2320),
                new Point(7270, 987, 2320),
                new Point(7270, 812, 2320),
            );
            $add(
                new Point(7129, 812, 2320),
                new Point(7129, 1051, 2320),
                new Point(7059, 1051, 2320),
                new Point(7059, 812, 2320),
            );
            $add(
                new Point(6566, 1167, 2225),
                new Point(6636, 1167, 2225),
                new Point(6636, 1012, 2225),
                new Point(6566, 1012, 2225),
            );
            $add(
                new Point(6566, 1167, 2320),
                new Point(6636, 1167, 2320),
                new Point(6636, 1167, 2225),
                new Point(6566, 1167, 2225),
            );
            $add(
                new Point(6425, 1203, 2225),
                new Point(6495, 1203, 2225),
                new Point(6495, 1048, 2225),
                new Point(6425, 1048, 2225),
            );
            $add(
                new Point(6425, 1203, 2320),
                new Point(6495, 1203, 2320),
                new Point(6495, 1203, 2225),
                new Point(6425, 1203, 2225),
            );
            $add(
                new Point(6636, 1153, 2320),
                new Point(6707, 1153, 2320),
                new Point(6707, 1153, 2225),
                new Point(6636, 1153, 2225),
            );
            $add(
                new Point(6284, 1238, 2225),
                new Point(6355, 1238, 2225),
                new Point(6355, 1083, 2225),
                new Point(6284, 1083, 2225),
            );
            $add(
                new Point(6284, 1238, 2320),
                new Point(6355, 1238, 2320),
                new Point(6355, 1238, 2225),
                new Point(6284, 1238, 2225),
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
                new Point(6144, 1269, 2225),
                new Point(6214, 1269, 2225),
                new Point(6214, 1113, 2225),
                new Point(6144, 1113, 2225),
            );
            $add(
                new Point(6144, 1269, 2320),
                new Point(6214, 1269, 2320),
                new Point(6214, 1269, 2225),
                new Point(6144, 1269, 2225),
            );
            $add(
                new Point(6425, 812, 2320),
                new Point(6425, 1222, 2320),
                new Point(6355, 1222, 2320),
                new Point(6355, 812, 2320),
            );
            $add(
                new Point(6425, 1222, 2225),
                new Point(6425, 1222, 2320),
                new Point(6425, 1122, 2320),
                new Point(6425, 1122, 2225),
            );
            $add(
                new Point(6355, 1222, 2225),
                new Point(6425, 1222, 2225),
                new Point(6425, 1083, 2225),
                new Point(6355, 1083, 2225),
            );
            $add(
                new Point(6355, 1222, 2320),
                new Point(6425, 1222, 2320),
                new Point(6425, 1222, 2225),
                new Point(6355, 1222, 2225),
            );
            $add(
                new Point(6495, 1185, 2320),
                new Point(6566, 1185, 2320),
                new Point(6566, 1185, 2225),
                new Point(6495, 1185, 2225),
            );
            $add(
                new Point(6495, 1185, 2225),
                new Point(6566, 1185, 2225),
                new Point(6566, 1048, 2225),
                new Point(6495, 1048, 2225),
            );
            $add(
                new Point(6566, 1185, 2225),
                new Point(6566, 1185, 2320),
                new Point(6566, 1102, 2320),
                new Point(6566, 1102, 2225),
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
                new Point(6918, 1104, 2225),
                new Point(6848, 1104, 2225),
            );
            $add(
                new Point(6848, 1104, 2225),
                new Point(6918, 1104, 2225),
                new Point(6918, 934, 2225),
                new Point(6848, 934, 2225),
            );
            $add(
                new Point(7059, 812, 2320),
                new Point(7059, 1069, 2320),
                new Point(6989, 1069, 2320),
                new Point(6989, 812, 2320),
            );
            $add(
                new Point(6989, 1069, 2225),
                new Point(7059, 1069, 2225),
                new Point(7059, 896, 2225),
                new Point(6989, 896, 2225),
            );
            $add(
                new Point(6989, 1069, 2320),
                new Point(7059, 1069, 2320),
                new Point(7059, 1069, 2225),
                new Point(6989, 1069, 2225),
            );
            $add(
                new Point(6495, 1203, 2225),
                new Point(6495, 1203, 2320),
                new Point(6495, 1154, 2320),
                new Point(6495, 1154, 2225),
            );
            $add(
                new Point(6636, 1167, 2225),
                new Point(6636, 1167, 2320),
                new Point(6636, 1118, 2320),
                new Point(6636, 1118, 2225),
            );
            $add(
                new Point(7411, 812, 2320),
                new Point(7411, 969, 2320),
                new Point(7341, 969, 2320),
                new Point(7341, 812, 2320),
            );
            $add(
                new Point(7200, 812, 2320),
                new Point(7200, 1025, 2320),
                new Point(7129, 1025, 2320),
                new Point(7129, 812, 2320),
            );
            $add(
                new Point(7129, 1025, 2320),
                new Point(7200, 1025, 2320),
                new Point(7200, 1025, 2225),
                new Point(7129, 1025, 2225),
            );
            $add(
                new Point(7129, 1025, 2225),
                new Point(7200, 1025, 2225),
                new Point(7200, 864, 2225),
                new Point(7129, 864, 2225),
            );
            $add(
                new Point(7341, 987, 2225),
                new Point(7341, 987, 2320),
                new Point(7341, 938, 2320),
                new Point(7341, 938, 2225),
            );
            $add(
                new Point(6918, 1104, 2225),
                new Point(6918, 1104, 2320),
                new Point(6918, 1058, 2320),
                new Point(6918, 1058, 2225),
            );
            $add(
                new Point(6777, 1141, 2225),
                new Point(6777, 1141, 2320),
                new Point(6777, 1093, 2320),
                new Point(6777, 1093, 2225),
            );
            $add(
                new Point(7059, 1069, 2225),
                new Point(7059, 1069, 2320),
                new Point(7059, 1020, 2320),
                new Point(7059, 1020, 2225),
            );
            $add(
                new Point(7200, 1025, 2225),
                new Point(7200, 1025, 2320),
                new Point(7200, 976, 2320),
                new Point(7200, 976, 2225),
            );
            $add(
                new Point(6214, 1269, 2225),
                new Point(6214, 1269, 2320),
                new Point(6214, 1220, 2320),
                new Point(6214, 1220, 2225),
            );

            // t ramp
            $add(
                new Point(2315, 806, 2221),
                new Point(2315, 1290, 2221),
                new Point(2315, 1290, 3775),
                new Point(2315, 806, 3775),
            );
            $add(
                new Point(2315, 806, 3775),
                new Point(2315, 1290, 3775),
                new Point(3526, 1290, 3775),
                new Point(3526, 806, 3775),
            );
            $add(
                new Point(3526, 1290, 3775),
                new Point(2315, 1290, 3775),
                new Point(2315, 1290, 3706),
                new Point(3526, 1290, 3706),
            );
            $add(
                new Point(2315, 1290, 3775),
                new Point(2315, 1290, 2221),
                new Point(2367, 1290, 2221),
                new Point(2367, 1290, 3775),
            );
            $add(
                new Point(2315, 1290, 2221),
                new Point(2315, 806, 2221),
                new Point(2369, 806, 2221),
                new Point(2369, 1290, 2221),
            );
            $add(
                new Point(2369, 1290, 2221),
                new Point(2369, 806, 2221),
                new Point(2369, 806, 3715),
                new Point(2369, 1290, 3715),
            );
            $add(
                new Point(2369, 1290, 3715),
                new Point(2369, 806, 3715),
                new Point(3480, 806, 3715),
                new Point(3480, 1290, 3715),
            );

            // t spawn walls
            $add(
                new Point(6152, 1928, 2223),
                new Point(5008, 1928, 2223),
                new Point(5008, 1209, 2223),
                new Point(6152, 1209, 2223),
            );
            $add(
                new Point(5008, 1209, 2223),
                new Point(5008, 1928, 2223),
                new Point(5008, 1928, 1997),
                new Point(5008, 1209, 1997),
            );
            $add(
                new Point(5008, 1209, 1997),
                new Point(5008, 1928, 1997),
                new Point(4807, 1928, 1997),
                new Point(4807, 1209, 1997),
            );
            $add(
                new Point(4807, 1209, 1997),
                new Point(4807, 1928, 1997),
                new Point(4807, 1928, 3661),
                new Point(4807, 1209, 3661),
            );
            $add(
                new Point(4807, 1209, 3661),
                new Point(4807, 1928, 3661),
                new Point(4238, 1928, 3661),
                new Point(4238, 1209, 3661),
            );
            $add(
                new Point(4238, 1209, 3661),
                new Point(4238, 1928, 3661),
                new Point(4238, 1928, 3567),
                new Point(4238, 1209, 3567),
            );
            $add(
                new Point(4238, 1209, 3567),
                new Point(4238, 1928, 3567),
                new Point(4044, 1928, 3369),
                new Point(4044, 1209, 3369),
            );
            $add(
                new Point(4044, 1209, 3369),
                new Point(4044, 1928, 3369),
                new Point(3660, 1928, 3369),
                new Point(3660, 1209, 3369),
            );
            $add(
                new Point(3660, 1209, 3369),
                new Point(3660, 1928, 3369),
                new Point(3460, 1928, 3561),
                new Point(3460, 1209, 3561),
            );
            $add(
                new Point(3460, 1209, 3561),
                new Point(3460, 1928, 3561),
                new Point(3460, 1928, 3750),
                new Point(3460, 1209, 3750),
            );
            $add(
                new Point(6152, 1928, 2223),
                new Point(6152, 1209, 2223),
                new Point(6152, 1209, 2294),
                new Point(6152, 1928, 2294),
            );
            $add(
                new Point(3460, 1209, 3750),
                new Point(3460, 1928, 3750),
                new Point(3510, 1928, 3750),
                new Point(3510, 1209, 3750),
            );

            // t spawn walls.001
            $add(
                new Point(5871, 1210, 1068),
                new Point(5871, 1210, 1232),
                new Point(5871, 1960, 1232),
                new Point(5871, 1960, 1068),
            );
            $add(
                new Point(5871, 1960, 1232),
                new Point(5871, 1210, 1232),
                new Point(5015, 1210, 1232),
                new Point(5015, 1960, 1232),
            );
            $add(
                new Point(5015, 1960, 1232),
                new Point(5015, 1210, 1232),
                new Point(5015, 1210, 1404),
                new Point(5015, 1960, 1404),
            );
            $add(
                new Point(4809, 1960, 1404),
                new Point(4809, 1210, 1404),
                new Point(4809, 1210, 1080),
                new Point(4809, 1960, 1080),
            );
            $add(
                new Point(5015, 1960, 1368),
                new Point(5015, 1780, 1368),
                new Point(5015, 1780, 2062),
                new Point(5015, 1960, 2062),
            );
            $add(
                new Point(4809, 1780, 1368),
                new Point(4809, 1960, 1368),
                new Point(4809, 1960, 2062),
                new Point(4809, 1780, 2062),
            );
            $add(
                new Point(4809, 1781, 1327),
                new Point(5015, 1781, 1327),
                new Point(5015, 1781, 2044),
                new Point(4809, 1781, 2044),
            );
            $add(
                new Point(5015, 1960, 1404),
                new Point(5015, 1210, 1404),
                new Point(4810, 1210, 1404),
                new Point(4810, 1960, 1404),
            );

            // t spawn walls.002
            $add(
                new Point(4239, 1917, 1222),
                new Point(3361, 1917, 1222),
                new Point(3361, 1226, 1222),
                new Point(4239, 1226, 1222),
            );
            $add(
                new Point(4239, 1917, 1222),
                new Point(4239, 1226, 1222),
                new Point(4239, 1226, 1063),
                new Point(4239, 1917, 1063),
            );
            $add(
                new Point(3361, 1226, 1222),
                new Point(3361, 1917, 1222),
                new Point(3361, 1917, 1072),
                new Point(3361, 1226, 1072),
            );

            // t spawn walls.003
            $add(
                new Point(1158, 1079, 1315),
                new Point(1158, 1975, 1315),
                new Point(1158, 1975, 1404),
                new Point(1158, 1079, 1404),
            );
            $add(
                new Point(1158, 1079, 2073),
                new Point(1158, 1975, 2073),
                new Point(1464, 1975, 2073),
                new Point(1464, 1079, 2073),
            );
            $add(
                new Point(1158, 1975, 1315),
                new Point(1158, 1079, 1315),
                new Point(1675, 1079, 1315),
                new Point(1675, 1975, 1315),
            );
            $add(
                new Point(1675, 1975, 1315),
                new Point(1675, 1079, 1315),
                new Point(1675, 1079, 1149),
                new Point(1675, 1975, 1149),
            );
            $add(
                new Point(1675, 1975, 1149),
                new Point(1675, 1079, 1149),
                new Point(2298, 1079, 1149),
                new Point(2298, 1975, 1149),
            );
            $add(
                new Point(2298, 1975, 1149),
                new Point(2298, 1079, 1149),
                new Point(2298, 1079, 1084),
                new Point(2298, 1975, 1084),
            );
            $add(
                new Point(1464, 1079, 2073),
                new Point(1464, 1975, 2073),
                new Point(1464, 1975, 2080),
                new Point(1464, 1079, 2080),
            );
            $add(
                new Point(1464, 1079, 2080),
                new Point(1464, 1975, 2080),
                new Point(1449, 1975, 2080),
                new Point(1449, 1079, 2080),
            );
            $add(
                new Point(1158, 1079, 1404),
                new Point(1158, 1975, 1404),
                new Point(1072, 1975, 1404),
                new Point(1072, 1079, 1404),
            );
            $add(
                new Point(1158, 1079, 1987),
                new Point(1158, 1975, 1987),
                new Point(1158, 1975, 2073),
                new Point(1158, 1079, 2073),
            );
            $add(
                new Point(1072, 1079, 1404),
                new Point(1072, 1975, 1404),
                new Point(1072, 1975, 1987),
                new Point(1072, 1079, 1987),
            );
            $add(
                new Point(1158, 1975, 1987),
                new Point(1158, 1079, 1987),
                new Point(1072, 1079, 1987),
                new Point(1072, 1975, 1987),
            );

            // t-spawn main floor
            $add(
                new Point(6178, 1196, 2283),
                new Point(6178, 1196, 1022),
                new Point(7686, 853, 1022),
                new Point(7686, 853, 2283),
            );
            $add(
                new Point(7686, 853, 2283),
                new Point(7686, 853, 1022),
                new Point(8915, 853, 1022),
                new Point(8915, 853, 2283),
            );
            $add(
                new Point(1146, 1196, 2283),
                new Point(2374, 1196, 2283),
                new Point(2374, 808, 3826),
                new Point(1146, 808, 3826),
            );
            $add(
                new Point(2315, 1196, 2283),
                new Point(4860, 1196, 2283),
                new Point(4860, 1196, 3698),
                new Point(2315, 1196, 3698),
            );
            $add(
                new Point(6178, 1196, 2283),
                new Point(1146, 1196, 2283),
                new Point(1146, 1196, 1022),
                new Point(6178, 1196, 1022),
            );
            $add(
                new Point(7686, 853, 2283),
                new Point(8915, 853, 2283),
                new Point(8837, 808, 2283),
                new Point(7686, 808, 2283),
            );
        }

        // Map - Upper Tunnel
        if (true) {

            // upper tunnel main floor
            $add(
                new Point(3858, 905, 5760),
                new Point(3858, 905, 8687),
                new Point(1031, 905, 8687),
                new Point(1031, 905, 5760),
            );
            $add(
                new Point(3955, 816, 6291),
                new Point(1477, 816, 6291),
                new Point(1477, 816, 3726),
                new Point(3955, 816, 3726),
            );
            $add(
                new Point(1551, 905, 8564),
                new Point(1936, 905, 8564),
                new Point(1936, 905, 9484),
                new Point(1551, 905, 9484),
            );
            $add(
                new Point(3858, 905, 8687),
                new Point(3858, 905, 5760),
                new Point(3858, 193, 5760),
                new Point(3858, 193, 8687),
            );
            $add(
                new Point(3858, 905, 5760),
                new Point(1031, 905, 5760),
                new Point(1031, 753, 5760),
                new Point(3858, 753, 5760),
            );

            // upper tunnel main floor.001
            $add(
                new Point(1551, 905, 9484),
                new Point(1936, 905, 9484),
                new Point(1989, 812, 9588),
                new Point(1499, 812, 9588),
            );

            // upper tunnel walls
            $add(
                new Point(1918, 1445, 8359),
                new Point(1918, 1445, 9520),
                new Point(1918, 886, 9520),
                new Point(1918, 886, 8359),
            );
            $add(
                new Point(1918, 1445, 8359),
                new Point(1918, 886, 8359),
                new Point(2699, 886, 8359),
                new Point(2699, 1445, 8359),
            );
            $add(
                new Point(2699, 1445, 8359),
                new Point(2699, 886, 8359),
                new Point(2699, 886, 8133),
                new Point(2699, 1445, 8133),
            );
            $add(
                new Point(2699, 1445, 8133),
                new Point(2699, 886, 8133),
                new Point(2747, 886, 8040),
                new Point(2747, 1445, 8040),
            );
            $add(
                new Point(2747, 1445, 8040),
                new Point(2747, 886, 8040),
                new Point(2805, 886, 7998),
                new Point(2805, 1445, 7998),
            );
            $add(
                new Point(2805, 1445, 7998),
                new Point(2805, 886, 7998),
                new Point(2890, 886, 7977),
                new Point(2890, 1445, 7977),
            );
            $add(
                new Point(2890, 1445, 7977),
                new Point(2890, 886, 7977),
                new Point(4616, 886, 7977),
                new Point(4616, 1445, 7977),
            );

            // upper tunnel walls.001
            $add(
                new Point(2877, 1477, 6249),
                new Point(2877, 889, 6249),
                new Point(3500, 889, 6249),
                new Point(3500, 1477, 6249),
            );
            $add(
                new Point(2506, 1477, 6249),
                new Point(2506, 1317, 6249),
                new Point(2897, 1317, 6249),
                new Point(2897, 1477, 6249),
            );
            $add(
                new Point(1918, 1477, 6249),
                new Point(1918, 859, 6249),
                new Point(2520, 859, 6249),
                new Point(2520, 1477, 6249),
            );
            $add(
                new Point(2520, 1477, 6249),
                new Point(2520, 859, 6249),
                new Point(2520, 859, 7032),
                new Point(2520, 1477, 7032),
            );
            $add(
                new Point(2877, 889, 6249),
                new Point(2877, 1477, 6249),
                new Point(2877, 1477, 7031),
                new Point(2877, 889, 7031),
            );
            $add(
                new Point(2897, 1317, 6249),
                new Point(2506, 1317, 6249),
                new Point(2506, 1317, 7032),
                new Point(2897, 1317, 7032),
            );
            $add(
                new Point(2877, 889, 7031),
                new Point(2877, 1477, 7031),
                new Point(3080, 1477, 7031),
                new Point(3080, 889, 7031),
            );
            $add(
                new Point(3080, 889, 7031),
                new Point(3080, 1477, 7031),
                new Point(3080, 1477, 7223),
                new Point(3080, 889, 7223),
            );
            $add(
                new Point(3080, 889, 7223),
                new Point(3080, 1477, 7223),
                new Point(3856, 1477, 7223),
                new Point(3856, 889, 7223),
            );
            $add(
                new Point(2520, 1477, 7032),
                new Point(2520, 859, 7032),
                new Point(2316, 859, 7032),
                new Point(2316, 1477, 7032),
            );
            $add(
                new Point(2316, 1477, 7032),
                new Point(2316, 859, 7032),
                new Point(2296, 859, 7118),
                new Point(2296, 1477, 7118),
            );
            $add(
                new Point(2296, 1477, 7118),
                new Point(2296, 859, 7118),
                new Point(2225, 859, 7189),
                new Point(2225, 1477, 7189),
            );
            $add(
                new Point(2225, 1477, 7189),
                new Point(2225, 859, 7189),
                new Point(2127, 859, 7215),
                new Point(2127, 1477, 7215),
            );
            $add(
                new Point(2127, 1477, 7215),
                new Point(2127, 859, 7215),
                new Point(1071, 859, 7215),
                new Point(1071, 1477, 7215),
            );
            $add(
                new Point(1071, 1477, 7215),
                new Point(1071, 859, 7215),
                new Point(1071, 859, 7978),
                new Point(1071, 1477, 7978),
            );
            $add(
                new Point(1071, 1477, 7978),
                new Point(1071, 859, 7978),
                new Point(1552, 859, 7978),
                new Point(1552, 1477, 7978),
            );
            $add(
                new Point(1552, 1477, 7978),
                new Point(1552, 859, 7978),
                new Point(1552, 859, 9520),
                new Point(1552, 1477, 9520),
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
            $this->spawnPositionAttacker[] = new Point(5631, 1196, 1775);
            $this->spawnPositionAttacker[] = new Point(5356, 1196, 1947);
            $this->spawnPositionAttacker[] = new Point(5947, 1196, 2021);
            $this->spawnPositionAttacker[] = new Point(5984, 1196, 1666);
            $this->spawnPositionAttacker[] = new Point(5621, 1196, 1467);
            $this->spawnPositionAttacker[] = new Point(5159, 1196, 1540);
            $this->spawnPositionAttacker[] = new Point(3853, 1196, 2112);
            $this->spawnPositionAttacker[] = new Point(4478, 1196, 1593);
            $this->spawnPositionAttacker[] = new Point(4332, 1196, 2032);
            $this->spawnPositionAttacker[] = new Point(3755, 1196, 1538);
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
