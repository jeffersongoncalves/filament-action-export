<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use JeffersonGoncalves\FilamentExportAction\Exporters\CsvExporter;

it('exports csv with custom delimiter', function () {
    $records = new Collection([
        ['name' => 'John', 'email' => 'john@example.com'],
        ['name' => 'Jane', 'email' => 'jane@example.com'],
    ]);

    $columns = ['name' => 'Name', 'email' => 'Email'];

    $exporter = (new CsvExporter)->delimiter(';');

    $response = $exporter->export($records, $columns, 'test-export');

    expect($response->getStatusCode())->toBe(200);
    expect($response->headers->get('Content-Type'))->toBe('text/csv');

    ob_start();
    $response->sendContent();
    $content = ob_get_clean();

    expect($content)->toContain('Name;Email');
    expect($content)->toContain('John;john@example.com');
});
