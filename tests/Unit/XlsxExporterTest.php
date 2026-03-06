<?php

declare(strict_types=1);

use JeffersonGoncalves\FilamentExportAction\Exporters\XlsxExporter;
use Symfony\Component\HttpFoundation\StreamedResponse;

it('exports records to xlsx with correct mime type', function () {
    $records = collect([
        ['name' => 'Alice', 'email' => 'alice@example.com'],
        ['name' => 'Bob', 'email' => 'bob@example.com'],
    ]);
    $columns = ['name' => 'Nome', 'email' => 'E-mail'];
    $exporter = new XlsxExporter;
    $response = $exporter->export($records, $columns, 'test');

    expect($response)->toBeInstanceOf(StreamedResponse::class);
    expect($response->headers->get('Content-Type'))->toContain('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('handles empty collection gracefully', function () {
    $records = collect([]);
    $columns = ['name' => 'Nome'];
    $exporter = new XlsxExporter;
    $response = $exporter->export($records, $columns, 'test');

    expect($response)->toBeInstanceOf(StreamedResponse::class);
});
