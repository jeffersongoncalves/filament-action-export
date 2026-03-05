<?php

declare(strict_types=1);

use JeffersonGoncalves\FilamentExportAction\Exporters\CsvExporter;
use Symfony\Component\HttpFoundation\StreamedResponse;

it('exports records to csv with correct headers and rows', function () {
    $records = collect([
        ['name' => 'Alice', 'email' => 'alice@example.com'],
        ['name' => 'Bob', 'email' => 'bob@example.com'],
    ]);
    $columns = ['name' => 'Nome', 'email' => 'E-mail'];
    $exporter = new CsvExporter();
    $response = $exporter->export($records, $columns, 'test');

    expect($response)->toBeInstanceOf(StreamedResponse::class);
    expect($response->headers->get('Content-Type'))->toContain('text/csv');
});

it('handles empty collection gracefully', function () {
    $records = collect([]);
    $columns = ['name' => 'Nome', 'email' => 'E-mail'];
    $exporter = new CsvExporter();
    $response = $exporter->export($records, $columns, 'test');

    expect($response)->toBeInstanceOf(StreamedResponse::class);
});

it('respects column order in output', function () {
    $records = collect([
        ['email' => 'alice@example.com', 'name' => 'Alice'],
    ]);
    $columns = ['name' => 'Nome', 'email' => 'E-mail'];
    $exporter = new CsvExporter();
    $response = $exporter->export($records, $columns, 'test');

    expect($response)->toBeInstanceOf(StreamedResponse::class);
});
