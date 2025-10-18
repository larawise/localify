<?php

namespace Larawise\Localify;

use Larawise\Packagify\Packagify;
use Larawise\Packagify\PackagifyProvider;

/**
 * Srylius - The ultimate symphony for technology architecture!
 *
 * @package     Larawise
 * @subpackage  Localify
 * @version     v1.0.0
 * @author      Selçuk Çukur <hk@selcukcukur.com.tr>
 * @copyright   Srylius Teknoloji Limited Şirketi
 *
 * @see https://docs.larawise.com/ Larawise : Docs
 */
class LocalifyProvider extends PackagifyProvider
{
    /**
     * Configure the packagify package.
     *
     * @param Packagify $package
     *
     * @return void
     */
    public function configure(Packagify $package)
    {
        // Set package name
        $package->name('localify');

        // Set package description
        $package->description('Localify - ');

        $package->hasConfigurations();
        $package->hasSingletons('localify', fn ($app) => new LocalifyManager($app));
        $package->hasCommands([
            Console\CurrencyGenerateCommand::class,
            Console\CountryGenerateCommand::class,
            Console\TimezoneGenerateCommand::class,
        ]);
    }
}
