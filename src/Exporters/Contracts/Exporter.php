<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Exporters\Contracts;

use Closure;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface Exporter
{
    /** @param array<string, string> $columns key = model field, value = column label */
    public function export(Collection $records, array $columns, string $filename): StreamedResponse;

    public function modifyWriter(Closure $callback): static;
}
