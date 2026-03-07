<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentExportServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-action-export';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations();
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make(static::$name, __DIR__.'/../resources/dist/filament-action-export.css'),
            Js::make(static::$name, __DIR__.'/../resources/js/filament-export.js'),
        ], 'jeffersongoncalves/filament-action-export');
    }
}
