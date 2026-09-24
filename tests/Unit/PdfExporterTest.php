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
    $exporter = new PdfExporter;
    $response = $exporter->export($records, $columns, 'test');

    expect($response)->toBeInstanceOf(StreamedResponse::class);
    expect($response->headers->get('Content-Type'))->toContain('application/pdf');
});

it('handles empty collection gracefully', function () {
    $records = collect([]);
    $columns = ['name' => 'Nome'];
    $exporter = new PdfExporter;
    $response = $exporter->export($records, $columns, 'test');

    expect($response)->toBeInstanceOf(StreamedResponse::class);
});

it('accepts extra view data', function () {
    $records = collect([
        ['name' => 'Alice'],
    ]);
    $columns = ['name' => 'Nome'];
    $exporter = (new PdfExporter)->extraViewData(['companyName' => 'Acme Corp']);
    $response = $exporter->export($records, $columns, 'test');

    expect($response)->toBeInstanceOf(StreamedResponse::class);
});

function renderedPdfTable(array $columns, array $row): array
{
    $left = INF;
    $right = 0;
    $pageWidth = null;

    (new PdfExporter)
        ->modifyWriter(function ($pdf) use (&$left, &$right, &$pageWidth) {
            $pdf->getDomPDF()->setCallbacks([[
                'event' => 'end_frame',
                'f' => function ($frame, $canvas) use (&$left, &$right, &$pageWidth) {
                    $pageWidth = $canvas->get_width();

                    if (in_array($frame->get_node()->nodeName, ['th', 'td'], true)) {
                        [$x, , $width] = $frame->get_border_box();
                        $left = min($left, $x);
                        $right = max($right, $x + $width);
                    }
                },
            ]]);
        })
        ->export(collect([$row]), $columns, 'test');

    // Content must stay within the printable area (page width minus the symmetric side margin).
    return [$right, $pageWidth, $pageWidth - $left];
}

it('keeps long unbreakable values inside the page width', function () {
    $columns = ['hash' => 'Hash do IP', 'reason' => 'Motivo', 'value' => 'Valor correspondido', 'count' => 'Ocorrências'];
    $row = ['hash' => hash('sha256', 'ip'), 'reason' => 'Lista de Bloqueio de ASN', 'value' => 'AS16276', 'count' => 1];

    [$contentRight, , $printableRight] = renderedPdfTable($columns, $row);

    expect($contentRight)->toBeLessThanOrEqual($printableRight);
});

it('switches to landscape when there are many columns', function () {
    $columns = collect(range(1, 8))->mapWithKeys(fn ($i) => ["c{$i}" => "Column {$i}"])->all();
    $row = collect(range(1, 8))->mapWithKeys(fn ($i) => ["c{$i}" => "value {$i}"])->all();

    [, $pageWidth] = renderedPdfTable($columns, $row);

    expect($pageWidth)->toBeGreaterThan(800); // A4 landscape = 841.89pt
});

it('keeps portrait when auto landscape is disabled', function () {
    config()->set('filament-action-export.pdf_options.auto_landscape_columns', null);
    $columns = collect(range(1, 8))->mapWithKeys(fn ($i) => ["c{$i}" => "Column {$i}"])->all();
    $row = collect(range(1, 8))->mapWithKeys(fn ($i) => ["c{$i}" => "value {$i}"])->all();

    [, $pageWidth] = renderedPdfTable($columns, $row);

    expect($pageWidth)->toBeLessThan(600); // A4 portrait = 595.28pt
});

it('accepts custom pdf options', function () {
    $records = collect([['name' => 'Alice']]);
    $columns = ['name' => 'Nome'];
    $exporter = (new PdfExporter)->pdfOptions(['paper' => 'letter', 'orientation' => 'landscape']);
    $response = $exporter->export($records, $columns, 'test');

    expect($response)->toBeInstanceOf(StreamedResponse::class);
});
