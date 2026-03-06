<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions\Concerns;

use Closure;

trait HasFormatStates
{
    /** @var array<string, Closure> */
    protected array $formatStates = [];

    /** @param array<string, Closure> $formatters */
    public function formatStates(array $formatters): static
    {
        $this->formatStates = $formatters;

        return $this;
    }

    /** @return array<string, Closure> */
    public function getFormatStates(): array
    {
        return $this->formatStates;
    }
}
