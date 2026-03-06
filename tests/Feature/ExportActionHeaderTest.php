<?php

declare(strict_types=1);

use JeffersonGoncalves\FilamentExportAction\Actions\ExportAction;
use JeffersonGoncalves\FilamentExportAction\Enums\ExportFormat;

it('can be instantiated with default name', function () {
    $action = ExportAction::make('export');

    expect($action)->toBeInstanceOf(ExportAction::class);
    expect($action::getDefaultName())->toBe('export');
});

it('accepts format configuration', function () {
    $action = ExportAction::make('export')
        ->formats([ExportFormat::Csv, ExportFormat::Pdf]);

    expect($action->getEnabledFormats())->toHaveCount(2);
});

it('supports withFilters flag', function () {
    $action = ExportAction::make('export')
        ->withFilters();

    expect($action)->toBeInstanceOf(ExportAction::class);
});

it('supports withSearch flag', function () {
    $action = ExportAction::make('export')
        ->withSearch();

    expect($action)->toBeInstanceOf(ExportAction::class);
});

it('supports withSort flag', function () {
    $action = ExportAction::make('export')
        ->withSort();

    expect($action)->toBeInstanceOf(ExportAction::class);
});

it('supports snappy pdf driver', function () {
    $action = ExportAction::make('export')
        ->snappy();

    expect($action->getPdfDriver())->toBe('snappy');
});

it('supports custom pdf driver', function () {
    $action = ExportAction::make('export')
        ->pdfDriver('dompdf');

    expect($action->getPdfDriver())->toBe('dompdf');
});

it('supports pdf options', function () {
    $action = ExportAction::make('export')
        ->pdfOptions(['paper' => 'letter', 'orientation' => 'landscape']);

    $options = $action->getPdfOptions();

    expect($options['paper'])->toBe('letter');
    expect($options['orientation'])->toBe('landscape');
});

it('chains all configuration methods fluently', function () {
    $action = ExportAction::make('export')
        ->formats([ExportFormat::Csv])
        ->defaultFormat(ExportFormat::Csv)
        ->withFilters()
        ->withSearch()
        ->withSort()
        ->snappy()
        ->userCanSelectColumns()
        ->excludeColumns(['password'])
        ->extraViewData(['company' => 'Test']);

    expect($action)->toBeInstanceOf(ExportAction::class);
    expect($action->getEnabledFormats())->toEqual([ExportFormat::Csv]);
    expect($action->getPdfDriver())->toBe('snappy');
    expect($action->resolveExtraViewData())->toBe(['company' => 'Test']);
});

it('supports modifyQueryUsing callback', function () {
    $action = ExportAction::make('export')
        ->modifyQueryUsing(fn ($query) => $query->where('active', true));

    expect($action)->toBeInstanceOf(ExportAction::class);
});

it('supports multiple modifyQueryUsing callbacks', function () {
    $action = ExportAction::make('export')
        ->modifyQueryUsing(fn ($query) => $query->where('active', true))
        ->modifyQueryUsing(fn ($query) => $query->where('role', 'admin'));

    expect($action)->toBeInstanceOf(ExportAction::class);
});

it('supports modifyQueryUsing with null callback', function () {
    $action = ExportAction::make('export')
        ->modifyQueryUsing(null);

    expect($action)->toBeInstanceOf(ExportAction::class);
});
