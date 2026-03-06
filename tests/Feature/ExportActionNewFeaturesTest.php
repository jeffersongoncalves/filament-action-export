<?php

declare(strict_types=1);

use JeffersonGoncalves\FilamentExportAction\Actions\ExportAction;
use JeffersonGoncalves\FilamentExportAction\Enums\ExportFormat;

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
