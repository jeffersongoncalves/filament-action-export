<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions\Concerns;

trait HasDirectDownload
{
    protected bool $directDownload = false;

    public function directDownload(bool $condition = true): static
    {
        $this->directDownload = $condition;

        return $this;
    }

    public function isDirectDownload(): bool
    {
        return $this->directDownload;
    }
}
