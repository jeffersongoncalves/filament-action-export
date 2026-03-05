<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions\Concerns;

use JeffersonGoncalves\FilamentExportAction\Enums\ExportFormat;

trait HasExportFormats
{
    /** @var array<ExportFormat>|null */
    protected ?array $formats = null;

    protected ?ExportFormat $defaultFormat = null;

    /** @param array<ExportFormat> $formats */
    public function formats(array $formats): static
    {
        $this->formats = $formats;

        return $this;
    }

    public function defaultFormat(ExportFormat $format): static
    {
        $this->defaultFormat = $format;

        return $this;
    }

    /** @return array<ExportFormat> */
    public function getEnabledFormats(): array
    {
        if ($this->formats !== null) {
            return $this->formats;
        }

        $configFormats = config('filament-action-export.formats', ['csv', 'xlsx', 'pdf']);

        return array_map(
            fn (string $format) => ExportFormat::from($format),
            $configFormats,
        );
    }

    public function getDefaultFormat(): ExportFormat
    {
        if ($this->defaultFormat !== null) {
            return $this->defaultFormat;
        }

        return ExportFormat::from(config('filament-action-export.default_format', 'xlsx'));
    }
}
