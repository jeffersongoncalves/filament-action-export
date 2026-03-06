<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions\Concerns;

trait HasCsvDelimiter
{
    protected ?string $csvDelimiter = null;

    public function csvDelimiter(string $delimiter): static
    {
        $this->csvDelimiter = $delimiter;

        return $this;
    }

    public function getCsvDelimiter(): string
    {
        return $this->csvDelimiter ?? config('filament-action-export.csv_delimiter', ',');
    }
}
