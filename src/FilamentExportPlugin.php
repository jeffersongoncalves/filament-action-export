<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentExportPlugin implements Plugin
{
    protected string $pdfDriver = 'dompdf';

    protected string $defaultFormat = 'xlsx';

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'filament-action-export';
    }

    public function pdfDriver(string $driver): static
    {
        $this->pdfDriver = $driver;

        return $this;
    }

    public function defaultFormat(string $format): static
    {
        $this->defaultFormat = $format;

        return $this;
    }

    public function getPdfDriver(): string
    {
        return $this->pdfDriver;
    }

    public function getDefaultFormat(): string
    {
        return $this->defaultFormat;
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
