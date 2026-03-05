<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Exporters;

use Illuminate\Support\Collection;
use JeffersonGoncalves\FilamentExportAction\Exporters\Contracts\Exporter;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class XlsxExporter implements Exporter
{
    /** @param array<string, string> $columns */
    public function export(Collection $records, array $columns, string $filename): StreamedResponse
    {
        $columnKeys = array_keys($columns);
        $columnLabels = array_values($columns);

        $tempFile = tempnam(sys_get_temp_dir(), 'export') . '.xlsx';

        $writer = SimpleExcelWriter::create($tempFile);
        $writer->noHeaderRow();
        $writer->addHeader($columnLabels);

        foreach ($records as $record) {
            $row = [];
            foreach ($columnKeys as $key) {
                $row[] = data_get($record, $key, '');
            }
            $writer->addRow($row);
        }

        $writer->close();

        return response()->streamDownload(function () use ($tempFile) {
            readfile($tempFile);
            @unlink($tempFile);
        }, "{$filename}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
