<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions\Concerns;

trait HasPageOrientation
{
    protected ?string $defaultPageOrientation = null;

    public function defaultPageOrientation(string $orientation): static
    {
        $this->defaultPageOrientation = $orientation;

        return $this;
    }

    public function getDefaultPageOrientation(): string
    {
        return $this->defaultPageOrientation
            ?? config('filament-action-export.pdf_options.orientation', 'portrait');
    }
}
