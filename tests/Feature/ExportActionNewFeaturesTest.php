<?php

declare(strict_types=1);

use JeffersonGoncalves\FilamentExportAction\Actions\FilamentExportBulkAction;
use JeffersonGoncalves\FilamentExportAction\Actions\FilamentExportHeaderAction;

it('supports direct download flag', function () {
    $action = FilamentExportBulkAction::make('export')
        ->directDownload();

    expect($action->isDirectDownload())->toBeTrue();
});

it('supports custom file name', function () {
    $action = FilamentExportBulkAction::make('export')
        ->fileName('my-report');

    expect($action->getDefaultFileName())->toBe('my-report');
});

it('supports file name prefix', function () {
    $action = FilamentExportBulkAction::make('export')
        ->fileNamePrefix('users');

    $resolved = $action->resolveFileName(null);

    expect($resolved)->toStartWith('users-export-');
});

it('supports disabling file name input', function () {
    $action = FilamentExportBulkAction::make('export')
        ->disableFileName();

    expect($action->isFileNameEnabled())->toBeFalse();
});

it('supports custom time format', function () {
    $action = FilamentExportBulkAction::make('export')
        ->timeFormat('d_m_Y');

    expect($action->getTimeFormat())->toBe('d_m_Y');
});

it('supports file name using closure', function () {
    $action = FilamentExportBulkAction::make('export')
        ->fileNameUsing(fn () => 'custom-name');

    expect($action->resolveFileName())->toBe('custom-name');
});

it('supports csv delimiter', function () {
    $action = FilamentExportBulkAction::make('export')
        ->csvDelimiter(';');

    expect($action->getCsvDelimiter())->toBe(';');
});

it('supports format states', function () {
    $action = FilamentExportBulkAction::make('export')
        ->formatStates([
            'name' => fn ($value) => strtoupper($value),
        ]);

    expect($action->getFormatStates())->toHaveCount(1);
    expect($action->getFormatStates()['name']('test'))->toBe('TEST');
});

it('supports modify excel writer callback', function () {
    $callback = fn ($writer) => $writer;

    $action = FilamentExportBulkAction::make('export')
        ->modifyExcelWriter($callback);

    expect($action->getModifyExcelWriter())->toBe($callback);
});

it('supports modify pdf writer callback', function () {
    $callback = fn ($writer) => $writer;

    $action = FilamentExportBulkAction::make('export')
        ->modifyPdfWriter($callback);

    expect($action->getModifyPdfWriter())->toBe($callback);
});

it('supports with hidden columns flag', function () {
    $action = FilamentExportBulkAction::make('export')
        ->withHiddenColumns();

    expect($action)->toBeInstanceOf(FilamentExportBulkAction::class);
});

it('resolves filename with user input', function () {
    $action = FilamentExportBulkAction::make('export')
        ->fileNamePrefix('report');

    $resolved = $action->resolveFileName('quarterly');

    expect($resolved)->toStartWith('report-quarterly-');
});

it('header action supports new features', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->directDownload()
        ->fileName('header-report')
        ->csvDelimiter(';')
        ->formatStates(['name' => fn ($v) => strtoupper($v)]);

    expect($action->isDirectDownload())->toBeTrue();
    expect($action->getDefaultFileName())->toBe('header-report');
    expect($action->getCsvDelimiter())->toBe(';');
    expect($action->getFormatStates())->toHaveCount(1);
});
