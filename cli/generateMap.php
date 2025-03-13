<?php

use cs\Core\Box;
use cs\Core\Floor;
use cs\Core\GameException;
use cs\Core\PlaneBuilder;
use cs\Core\Point;

require __DIR__ . '/../vendor/autoload.php';

if ($argc !== 3) {
    echo "Usage: {$argv[0]} map.input MapName" . PHP_EOL;
    exit(1);
}
/////
$input = $argv[1];
$mapName = $argv[2] . 'Map';
/////

class Parser
{

    private int $lineNumber = 1;
    private string $activeGroup = 'NONE';
    private ?string $activeCategory = null;
    private string $data = '';

    public int $quadCount = 0;
    public int $triangleCount = 0;

    public function __construct(private readonly PlaneBuilder $planeBuilder = new PlaneBuilder())
    {
    }

    public function parseLine(string $line): void
    {
        if (strlen($line) < 1) {
            $this->error();
        }

        $type = $line[0] ?? $this->error();
        $data = trim(substr($line, 1));

        match ($type) {
            'G' => $this->activeGroup = $data,
            'C' => $this->activeCategory($data),
            'O' => $this->activeObject($data),
            'P' => $this->createPolygons($data),
            'B' => $this->createBox($data),
            'A' => $this->createAnchor($data),
            default => $this->error($type),
        };

        $this->lineNumber++;
    }

    public function activeCategory(string $line): void
    {
        if ($this->activeCategory !== null) {
            $this->append("}\n");
        }

        $this->append("\n        // {$this->activeGroup} - {$line}\n");
        $this->append("if (true) {\n");
        $this->activeCategory = $line;
    }

    public function activeObject(string $line): void
    {
        $this->append("\n        // {$line}\n");
    }

    public function createAnchor(string $line): void
    {
        $point = $this->parsePoints($line);
        assert(count($point) === 1);
        $point = $point[0];

        if ($this->activeGroup === 'Spawn') {
            if ($this->activeCategory === 'Attackers') {
                $this->append('$this->spawnPositionAttacker[] = ' . $this->constructPoint($point) . ";\n");
            } elseif ($this->activeCategory === 'Defenders') {
                $this->append('$this->spawnPositionDefender[] = ' . $this->constructPoint($point) . ";\n");
            }
        }
    }

    public function createBox(string $line): void
    {
        [$data, $vertexes] = explode('#', $line);
        [$name, $height] = explode('|', $data);
        assert(is_numeric($height));
        $points = $this->parsePoints($vertexes);
        assert(count($points) === 4);

        $floor = $this->planeBuilder->fromQuad(...$points);
        if (false === (count($floor) === 1 && $floor[0] instanceof Floor)) {
            $this->error();
        }
        $floor = $floor[0];
        $box = new Box($floor->getStart(), $floor->width, (int)$height, $floor->depth);

        if ($this->activeGroup === 'Plant') {
            $this->setupPlants($box);
        } elseif ($this->activeGroup === 'Store') {
            assert(str_starts_with($name, 'attackers') || str_starts_with($name, 'defenders'));
            $this->setupStore($box, str_starts_with($name, 'attackers'));
        }
    }

    private function constructPoint(Point $point): string
    {
        return "new Point({$point->x}, {$point->y}, {$point->z})";
    }

    private function constructBox(Box $box): string
    {
        return sprintf(
            'new Box(%s, %d, %d, %d)',
            $this->constructPoint($box->getBase()),
            $box->widthX,
            $box->heightY,
            $box->depthZ,
        );
    }

    private function setupPlants(Box $box): void
    {
        $this->append('$this->plantArea->add(' . $this->constructBox($box) . ");\n");
    }

    private function setupStore(Box $box, bool $forAttackers): void
    {
        if ($forAttackers) {
            $this->append('$this->buyAreaAttackers->add(' . $this->constructBox($box) . ");\n");
            return;
        }
        $this->append('$this->buyAreaDefenders->add(' . $this->constructBox($box) . ");\n");
    }

    /**
     * @param non-empty-string $separator
     * @return list<Point>
     */
    private function parsePoints(string $line, string $separator = '|'): array
    {
        $points = [];
        foreach (explode($separator, $line) as $xyzTriplet) {
            // blender is right-handed with Z up (-Y view)
            [$x, $z, $y] = explode(',', $xyzTriplet);
            $points[] = new Point(
                (int)round(floatval($x)),
                (int)round(floatval($y)),
                (int)round(floatval($z)),
            );
        }

        return $points;
    }

    public function createPolygons(string $line): void
    {
        assert($this->activeGroup === 'Map');
        $supportNavmesh = true;
        $penetrable = true;
        if ($this->activeCategory === 'Boundary') {
            $penetrable = false;
            $supportNavmesh = false;
        }

        $polygonPoints = $this->parsePoints($line);
        if (count($polygonPoints) === 3) {
            $polygonPoints[] = null;
            $this->triangleCount++;
        } else {
            $this->quadCount++;
        }
        if (count($polygonPoints) !== 4) {
            $this->error();
        }

        $polygon = '$add(' . "\n";
        foreach ($polygonPoints as $point) {
            $polygon .= $point === null ? 'null' : $this->constructPoint($point);
            $polygon .= ",\n";
        }
        if (!$penetrable) {
            $polygon .= "penetrable: false,\n";
        }
        if (!$supportNavmesh) {
            $polygon .= "navmesh: false,\n";
        }
        $polygon .= ");\n";
        $this->append($polygon);
    }

    public function flush(): string
    {
        $this->append("}\n");
        return $this->data;
    }

    private function append(string $string): void
    {
        $this->data .= $string;
    }

    private function error(string $msg = ''): never
    {
        throw new GameException(sprintf('Error on line "%d" %s', $this->lineNumber, $msg));
    }
}

assert(is_readable($input));
$fileContent = file_get_contents($input);
assert(is_string($fileContent));

$parser = new Parser();
foreach (explode("\n", trim($fileContent)) as $lineIndex => $line) {
    $parser->parseLine($line);
}

$output = __DIR__ . "/../server/src/Map/{$mapName}.php";
file_put_contents($output, str_replace(
    ['{_CLASSNAME_}', '{_DATA_CONSTRUCTOR_}'],
    [$mapName, $parser->flush()],
    <<<'PHP_CLASS_TEMPLATE'
<?php

namespace cs\Map;

use cs\Core\Box;
use cs\Core\BoxGroup;
use cs\Core\Floor;
use cs\Core\Plane;
use cs\Core\PlaneBuilder;
use cs\Core\Point;
use cs\Core\Wall;

final class {_CLASSNAME_} extends Map
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
{_DATA_CONSTRUCTOR_}
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

PHP_CLASS_TEMPLATE
));
printf(
    "Map (Quads: %d; Triangles: %d) generated to '%s'%s",
    $parser->quadCount,
    $parser->triangleCount,
    realpath($output),
    PHP_EOL,
);
