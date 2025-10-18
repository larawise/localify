<?php

namespace Larawise\Localify\Console;

use Illuminate\Console\Command;

class CurrencyGenerateCommand extends Command
{
    use Concerns\EnumGeneratorCommand;

    protected $signature = 'enum:generate:currency {name=Currency} {--namespace=Larawise\\Localify\\Enums}';

    protected $type = 'string';

    public function handle()
    {
        $name = $this->argument('name');
        $namespace = $this->option('namespace');

        $data = $this->loadJson();
        $parts = $this->generateEnumParts($data);
        $stub = $this->loadStub();
        $output = $this->injectIntoStub($stub, $name, $namespace, $parts);

        $target = base_path("system/localify/src/Enums/{$name}.php");
        $this->files->ensureDirectoryExists(dirname($target));
        $this->files->put($target, $output);

        $this->info("Enum {$name} generated at: {$target}");
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function stub()
    {
        return base_path('system/localify/src/Console/stubs/currency.enum.stub');
    }

    /**
     * Column names to generate methods for.
     *
     * @return array
     */
    protected function columns()
    {
        return [
            'name',
            'fractionSize',
            'symbol.grapheme',
            'symbol.template',
            'symbol.rtl',
            'uniqSymbol.grapheme',
            'uniqSymbol.template',
            'uniqSymbol.rtl',
        ];
    }

    /**
     * JSON file path to load data from.
     *
     * @return string
     */
    protected function path()
    {
        return base_path('system/localify/resources/collections/localization/currency.json');
    }

    /**
     * Which key in JSON represents the enum value.
     *
     * @return string
     */
    protected function identifier()
    {
        return 'code';
    }
}
