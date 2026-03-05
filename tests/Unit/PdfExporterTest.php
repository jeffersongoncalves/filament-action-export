<?php

declare(strict_types=1);

use JeffersonGoncalves\FilamentExportAction\Exporters\PdfExporter;
use Symfony\Component\HttpFoundation\StreamedResponse;

it('exports records to pdf with correct mime type', function () {
    $records = collect([
        ['name' => 'Alice', 'email' => 'alice@example.com'],
        ['name' => 'Bob', 'email' => 'bob@example.com'],
    ]);
    $columns = ['name' => 'Nome', 'email' => 'E-mail'];
    $exporter = new PdfExporter();
    $response = $exporter->export($records, $columns, 'test');

    expect($response)->toBeInstanceOf(StreamedResponse::class);
    expect($response->headers->get('Content-Type'))->toContain('application/pdf');
});

it('handles empty collection gracefully', function () {
    $records = collect([]);
    $columns = ['name' => 'Nome'];
    $exporter = new PdfExporter();
    $response = $exporter->export($records, $columns, 'test');

    expect($response)->toBeInstanceOf(StreamedResponse::class);
});

it('accepts extra view data', function () {
    $records = collect([
        ['name' => 'Alice'],
    ]);
    $columns = ['name' => 'Nome'];
    $exporter = (new PdfExporter())->extraViewData(['companyName' => 'Acme Corp']);
    $response = $exporter->export($records, $columns, 'test');

    expect($response)->toBeInstanceOf(StreamedResponse::class);
});

it('accepts custom pdf options', function () {
    $records = collect([['name' => 'Alice']]);
    $columns = ['name' => 'Nome'];
    $exporter = (new PdfExporter())->pdfOptions(['paper' => 'letter', 'orientation' => 'landscape']);
    $response = $exporter->export($records, $columns, 'test');

    expect($response)->toBeInstanceOf(StreamedResponse::class);
});
