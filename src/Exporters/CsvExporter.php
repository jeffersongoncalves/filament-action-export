<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Exporters;

use Closure;
use Illuminate\Support\Collection;
use JeffersonGoncalves\FilamentExportAction\Exporters\Contracts\Exporter;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporter implements Exporter
{
    protected string $delimiter = ',';

    protected ?Closure $writerCallback = null;

    public function delimiter(string $delimiter): static
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    public function modifyWriter(Closure $callback): static
    {
        $this->writerCallback = $callback;

        return $this;
    }

    /** @param array<string, string> $columns */
    public function export(Collection $records, array $columns, string $filename): StreamedResponse
    {
        $columnKeys = array_keys($columns);
        $columnLabels = array_values($columns);

        $tempFile = tempnam(sys_get_temp_dir(), 'export').'.csv';

        $writer = SimpleExcelWriter::create($tempFile, delimiter: $this->delimiter);
        $writer->noHeaderRow();
        $writer->addHeader($columnLabels);

        foreach ($records as $record) {
            $row = [];
            foreach ($columnKeys as $key) {
                $row[] = is_array($record) ? ($record[$key] ?? '') : data_get($record, $key, '');
            }
            $writer->addRow($row);
        }

        if ($this->writerCallback !== null) {
            ($this->writerCallback)($writer);
        }

        $writer->close();

        return response()->streamDownload(function () use ($tempFile) {
            readfile($tempFile);
            @unlink($tempFile);
        }, "{$filename}.csv", [
            'Content-Type' => 'text/csv',
        ]);
    }
}
