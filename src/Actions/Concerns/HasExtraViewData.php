<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions\Concerns;

use Closure;

trait HasExtraViewData
{
    /** @var array<string, mixed>|Closure|null */
    protected $extraViewData = null;

    /** @param array<string, mixed>|Closure $data */
    public function extraViewData($data): static
    {
        $this->extraViewData = $data;

        return $this;
    }

    /** @return array<string, mixed> */
    public function resolveExtraViewData(): array
    {
        if ($this->extraViewData === null) {
            return [];
        }

        if ($this->extraViewData instanceof Closure) {
            return ($this->extraViewData)($this);
        }

        return $this->extraViewData;
    }
}
