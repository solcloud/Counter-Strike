<?php

use cs\Core\GameException;

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
    private string $activeObject = 'NONE';
    private string $data = '';

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
            default => $this->error($type),
        };

        $this->lineNumber++;
    }

    public function activeCategory(string $line): void
    {
        if ($this->activeCategory !== null) {
            $this->append("}\n");
        }

        $this->append("\n        // {$line}\n");
        $this->append("if (true) { // @phpstan-ignore if.alwaysTrue\n");
        $this->activeCategory = $line;
    }

    public function activeObject(string $line): void
    {
        $this->activeObject = $line;
        $this->append("\n        // {$line}\n");
    }

    public function createPolygons(string $line): void
    {
        assert($this->activeGroup === 'map');

        $polygonPoints = [];
        foreach (explode('|', $line) as $xyzTriplet) {
            // blender is right-handed with Z up (-Y view)
            [$x, $z, $y] = explode(',', $xyzTriplet);
            $polygonPoints[] = [
                (int)round(floatval($x)),
                (int)round(floatval($y)),
                (int)round(floatval($z)),
            ];
        }

        if (count($polygonPoints) === 3) {
            $polygonPoints[] = null;
        }
        if (count($polygonPoints) !== 4) {
            $this->error();
        }

        $polygon = '$this->addPlanes($b->create(' . "\n";
        foreach ($polygonPoints as $xyz) {
            $polygon .= $xyz === null ? 'null' : "new Point({$xyz[0]}, {$xyz[1]}, {$xyz[2]})";
            $polygon .= ",\n";
        }
        $polygon .= "1.0,\n";
        $polygon .= "), " . (($this->activeObject[0] === 'f') ? 'true' : 'false') . ");\n";
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

use cs\Core\Floor;
use cs\Core\Plane;
use cs\Core\PlaneBuilder;
use cs\Core\Point;
use cs\Core\Wall;

final class {_CLASSNAME_} extends DebugMap
{
    /** @var Wall[] */
    public array $walls = [];
    /** @var Floor[] */
    public array $floors = [];

    public function __construct()
    {
        $b = new PlaneBuilder();

/** START DATA */
{_DATA_CONSTRUCTOR_}
/** END DATA */
    }

    /** @param list<Plane> $planes */
    private function addPlanes(array $planes, bool $penetrable): void
    {
        foreach ($planes as $plane) {
            $plane->setPenetrable($penetrable);
            if ($plane instanceof Wall) {
                $this->walls[] = $plane;
            } elseif ($plane instanceof Floor) {
                $this->floors[] = $plane;
            }
        }
    }

}

PHP_CLASS_TEMPLATE
));
echo "Map generated to " . realpath($output) . PHP_EOL;
