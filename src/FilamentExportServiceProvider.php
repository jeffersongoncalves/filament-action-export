<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;
use JeffersonGoncalves\FilamentExportAction\Livewire\ExportPreview;
use Livewire\Livewire;

class FilamentExportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/filament-action-export.php', 'filament-action-export');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-action-export');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'filament-action-export');

        FilamentAsset::register([
            Css::make('filament-action-export', __DIR__ . '/../resources/dist/filament-action-export.css'),
        ], 'jeffersongoncalves/filament-action-export');

        Livewire::component('filament-action-export.export-preview', ExportPreview::class);

        $this->publishes([
            __DIR__ . '/../config/filament-action-export.php' => config_path('filament-action-export.php'),
        ], 'filament-action-export-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-action-export'),
        ], 'filament-action-export-views');

        $this->publishes([
            __DIR__ . '/../resources/lang' => lang_path('vendor/filament-action-export'),
        ], 'filament-action-export-lang');
    }
}
