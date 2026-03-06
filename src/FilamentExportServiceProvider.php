<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use JeffersonGoncalves\FilamentExportAction\Livewire\ExportPreview;
use Livewire\Livewire;
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
            Css::make(static::$name, __DIR__ . '/../resources/dist/filament-action-export.css'),
        ], 'jeffersongoncalves/filament-action-export');

        Livewire::component('filament-action-export.export-preview', ExportPreview::class);
    }
}
