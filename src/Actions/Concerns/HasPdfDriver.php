<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions\Concerns;

trait HasPdfDriver
{
    protected ?string $pdfDriverName = null;

    /** @var array<string, mixed> */
    protected array $pdfOptions = [];

    public function snappy(): static
    {
        $this->pdfDriverName = 'snappy';

        return $this;
    }

    public function pdfDriver(string $driver): static
    {
        $this->pdfDriverName = $driver;

        return $this;
    }

    /** @param array<string, mixed> $options */
    public function pdfOptions(array $options): static
    {
        $this->pdfOptions = $options;

        return $this;
    }

    public function getPdfDriver(): string
    {
        return $this->pdfDriverName ?? config('filament-action-export.pdf_driver', 'dompdf');
    }

    /** @return array<string, mixed> */
    public function getPdfOptions(): array
    {
        return array_merge(
            config('filament-action-export.pdf_options', []),
            $this->pdfOptions,
        );
    }
}
