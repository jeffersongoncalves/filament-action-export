<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions\Concerns;

trait HasPreview
{
    protected ?bool $previewEnabled = null;

    protected ?bool $printEnabled = null;

    public function disablePreview(bool $condition = true): static
    {
        $this->previewEnabled = ! $condition;

        return $this;
    }

    public function isPreviewEnabled(): bool
    {
        return $this->previewEnabled ?? config('filament-action-export.preview_enabled', true);
    }

    public function disablePrint(bool $condition = true): static
    {
        $this->printEnabled = ! $condition;

        return $this;
    }

    public function isPrintEnabled(): bool
    {
        return $this->printEnabled ?? config('filament-action-export.print_enabled', true);
    }
}
