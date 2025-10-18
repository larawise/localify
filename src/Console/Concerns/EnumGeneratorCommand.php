<?php

namespace Larawise\Localify\Console\Concerns;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RuntimeException;

trait EnumGeneratorCommand
{
    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * Enum type: string, int, etc.
     *
     * @var string
     */
    protected $type = 'string';

    /**
     * Reserved names that cannot be used for generation.
     *
     * @var string[]
     */
    protected $reservedNames = [
        '__halt_compiler',
        'abstract',
        'and',
        'array',
        'as',
        'break',
        'callable',
        'case',
        'catch',
        'class',
        'clone',
        'const',
        'continue',
        'declare',
        'default',
        'die',
        'do',
        'echo',
        'else',
        'elseif',
        'empty',
        'enddeclare',
        'endfor',
        'endforeach',
        'endif',
        'endswitch',
        'endwhile',
        'enum',
        'eval',
        'exit',
        'extends',
        'false',
        'final',
        'finally',
        'fn',
        'for',
        'foreach',
        'function',
        'global',
        'goto',
        'if',
        'implements',
        'include',
        'include_once',
        'instanceof',
        'insteadof',
        'interface',
        'isset',
        'list',
        'match',
        'namespace',
        'new',
        'or',
        'parent',
        'print',
        'private',
        'protected',
        'public',
        'readonly',
        'require',
        'require_once',
        'return',
        'self',
        'static',
        'switch',
        'throw',
        'trait',
        'true',
        'try',
        'unset',
        'use',
        'var',
        'while',
        'xor',
        'yield',
        '__CLASS__',
        '__DIR__',
        '__FILE__',
        '__FUNCTION__',
        '__LINE__',
        '__METHOD__',
        '__NAMESPACE__',
        '__TRAIT__',
    ];

    /**
     * Create a new enum generator command instance.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Injects enum metadata and method mappings into the stub template.
     *
     * Replaces core placeholders like {{ name }}, {{ namespace }}, {{ type }}, and {{ cases }}
     * with actual values, then dynamically replaces each column-specific placeholder
     * (e.g. {{ label }}, {{ name }}, {{ utc }}) with its corresponding match block.
     *
     * @param string $stub
     * @param string $name
     * @param string $namespace
     * @param array $parts
     *
     * @return string
     */
    protected function injectIntoStub(string $stub, string $name, string $namespace, array $parts): string
    {
        $output = str_replace(
            ['{{ name }}', '{{ namespace }}', '{{ type }}', '{{ cases }}'],
            [$name, $namespace, $this->type, $parts['cases']],
            $stub
        );

        foreach ($this->columns() as $column) {
            $placeholder = '{{ ' . Str::plural(Str::camel($column)) . ' }}';
            $lines = $parts['matches'][$column] ?? [];
            $output = str_replace($placeholder, implode("\n", $lines), $output);
        }

        return $output;
    }

    /**
     * Generates enum case definitions and column-based match blocks from JSON data.
     *
     * @param array $data
     *
     * @return array{
     *     cases: string,
     *     matches: array<string, string[]>
     * }
     */
    protected function generateEnumParts(array $data): array
    {
        $columns = $this->columns();
        $identifierKey = $this->identifier();
        $cases = [];
        $matches = [];

        foreach ($columns as $column) {
            $matches[$column] = [];
        }

        foreach ($data as $key => $item) {
            $value = is_array($item) ? ($item[$identifierKey] ?? $key) : $key;
            if (!is_string($value)) continue;

            $caseKey = $this->qualifyKey($value);
            $doc = $item['label'] ?? $item['name'] ?? null;

            if ($doc) {
                $cases[] = "    /**";
                $cases[] = "     * {$doc}";
                $cases[] = "     */";
            }

            $cases[] = "    case {$caseKey} = '{$value}';\n";

            foreach ($columns as $column) {
                $columnValue = $this->extractValue($item, $column);
                if ($columnValue !== null) {
                    $matches[$column][] = "            self::{$caseKey} => '" . str_replace('"', '\"', (string) $columnValue) . "',";
                }
            }
        }

        return [
            'cases' => implode("\n", $cases),
            'matches' => $matches,
        ];
    }

    /**
     * Resolves nested keys like "symbol.grapheme" from array.
     */
    protected function extractValue(array $item, string $key): mixed
    {
        foreach (explode('.', $key) as $segment) {
            if (!is_array($item)) return null;
            $item = $item[$segment] ?? null;
            if ($item === null) return null;
        }
        return $item;
    }


    /**
     * Load the stub file content used for enum generation.
     *
     * @return string
     * @throws RuntimeException
     */
    protected function loadStub()
    {
        $path = $this->stub();

        if (! $this->files->exists($path)) {
            throw new RuntimeException("Stub file not found: {$path}");
        }

        return $this->files->get($path);
    }

    /**
     * Load and decode the JSON file containing enum source data.
     *
     * @throws RuntimeException
     *
     * @return array
     */
    protected function loadJson()
    {
        $path = $this->path();

        if (! $this->files->exists($path)) {
            throw new RuntimeException("JSON file not found: {$path}");
        }

        return json_decode($this->files->get($path), true);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    abstract protected function stub();

    /**
     * Column names to generate methods for.
     *
     * @return array
     */
    abstract protected function columns();

    /**
     * JSON file path to load data from.
     *
     * @return string
     */
    abstract protected function path();

    /**
     * Which key in JSON represents the enum value.
     *
     * @return string
     */
    abstract protected function identifier();

    /**
     * Normalize a raw enum value into a valid enum case name.
     *
     * @param string $value
     *
     * @return string
     */
    protected function qualifyKey($value)
    {
        return strtoupper(Str::of($value)->replace(['/', '-'], '_')->upper()->toString());
    }

    /**
     * Get the default namespace for the class.
     *
     * @return string
     */
    protected function namespace()
    {
        return 'Larawise\Localify\Enums';
    }
}
