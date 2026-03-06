<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions\Concerns;

use Closure;

trait HasWriterCallbacks
{
    protected ?Closure $modifyExcelWriter = null;

    protected ?Closure $modifyPdfWriter = null;

    public function modifyExcelWriter(Closure $callback): static
    {
        $this->modifyExcelWriter = $callback;

        return $this;
    }

    public function modifyPdfWriter(Closure $callback): static
    {
        $this->modifyPdfWriter = $callback;

        return $this;
    }

    public function getModifyExcelWriter(): ?Closure
    {
        return $this->modifyExcelWriter;
    }

    public function getModifyPdfWriter(): ?Closure
    {
        return $this->modifyPdfWriter;
    }
}
