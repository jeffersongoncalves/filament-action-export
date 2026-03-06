<?php

declare(strict_types=1);

use JeffersonGoncalves\FilamentExportAction\Actions\ExportAction;

it('supports direct download flag', function () {
    $action = ExportAction::make('export')
        ->directDownload();

    expect($action->isDirectDownload())->toBeTrue();
});

it('supports custom file name', function () {
    $action = ExportAction::make('export')
        ->fileName('my-report');

    expect($action->getDefaultFileName())->toBe('my-report');
});

it('supports file name prefix', function () {
    $action = ExportAction::make('export')
        ->fileNamePrefix('users');

    $resolved = $action->resolveFileName(null);

    expect($resolved)->toStartWith('users-export-');
});

it('supports disabling file name input', function () {
    $action = ExportAction::make('export')
        ->disableFileName();

    expect($action->isFileNameEnabled())->toBeFalse();
});

it('supports custom time format', function () {
    $action = ExportAction::make('export')
        ->timeFormat('d_m_Y');

    expect($action->getTimeFormat())->toBe('d_m_Y');
});

it('supports file name using closure', function () {
    $action = ExportAction::make('export')
        ->fileNameUsing(fn () => 'custom-name');

    expect($action->resolveFileName())->toBe('custom-name');
});

it('supports csv delimiter', function () {
    $action = ExportAction::make('export')
        ->csvDelimiter(';');

    expect($action->getCsvDelimiter())->toBe(';');
});

it('supports format states', function () {
    $action = ExportAction::make('export')
        ->formatStates([
            'name' => fn ($value) => strtoupper($value),
        ]);

    expect($action->getFormatStates())->toHaveCount(1);
    expect($action->getFormatStates()['name']('test'))->toBe('TEST');
});

it('supports modify excel writer callback', function () {
    $callback = fn ($writer) => $writer;

    $action = ExportAction::make('export')
        ->modifyExcelWriter($callback);

    expect($action->getModifyExcelWriter())->toBe($callback);
});

it('supports modify pdf writer callback', function () {
    $callback = fn ($writer) => $writer;

    $action = ExportAction::make('export')
        ->modifyPdfWriter($callback);

    expect($action->getModifyPdfWriter())->toBe($callback);
});

it('supports with hidden columns flag', function () {
    $action = ExportAction::make('export')
        ->withHiddenColumns();

    expect($action)->toBeInstanceOf(ExportAction::class);
});

it('resolves filename with user input', function () {
    $action = ExportAction::make('export')
        ->fileNamePrefix('report');

    $resolved = $action->resolveFileName('quarterly');

    expect($resolved)->toStartWith('report-quarterly-');
});

it('supports disabling table columns', function () {
    $action = ExportAction::make('export')
        ->disableTableColumns();

    expect($action->isTableColumnsDisabled())->toBeTrue();
});

it('supports disabling file name prefix', function () {
    $action = ExportAction::make('export')
        ->fileNamePrefix('prefix')
        ->disableFileNamePrefix();

    expect($action->isFileNamePrefixEnabled())->toBeFalse();

    $resolved = $action->resolveFileName('report');

    expect($resolved)->toStartWith('report-')
        ->not->toStartWith('prefix-');
});

it('supports with filters flag', function () {
    $action = ExportAction::make('export')
        ->withFilters();

    expect($action)->toBeInstanceOf(ExportAction::class);
});

it('supports with search flag', function () {
    $action = ExportAction::make('export')
        ->withSearch();

    expect($action)->toBeInstanceOf(ExportAction::class);
});

it('supports with sort flag', function () {
    $action = ExportAction::make('export')
        ->withSort();

    expect($action)->toBeInstanceOf(ExportAction::class);
});

it('supports chaining all table state flags', function () {
    $action = ExportAction::make('export')
        ->withFilters()
        ->withSearch()
        ->withSort();

    expect($action)->toBeInstanceOf(ExportAction::class);
});

it('reads action icon from config', function () {
    config()->set('filament-action-export.icons.action', 'heroicon-o-document-arrow-down');

    $icon = config('filament-action-export.icons.action');

    expect($icon)->toBe('heroicon-o-document-arrow-down');
});

it('reads use_snappy from config', function () {
    config()->set('filament-action-export.use_snappy', true);

    $action = ExportAction::make('export');

    expect($action->getPdfDriver())->toBe('snappy');
});

it('prefers explicit snappy over config', function () {
    config()->set('filament-action-export.use_snappy', false);

    $action = ExportAction::make('export')->snappy();

    expect($action->getPdfDriver())->toBe('snappy');
});

it('prefers explicit pdfDriver over use_snappy config', function () {
    config()->set('filament-action-export.use_snappy', true);

    $action = ExportAction::make('export')->pdfDriver('dompdf');

    expect($action->getPdfDriver())->toBe('dompdf');
});
