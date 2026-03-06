<?php

declare(strict_types=1);

use JeffersonGoncalves\FilamentExportAction\Actions\ExportAction;
use JeffersonGoncalves\FilamentExportAction\Enums\ExportFormat;
use JeffersonGoncalves\FilamentExportAction\ValueObjects\AdditionalColumn;

it('can be instantiated with default name', function () {
    $action = ExportAction::make('export');

    expect($action)->toBeInstanceOf(ExportAction::class);
    expect($action::getDefaultName())->toBe('export');
});

it('accepts format configuration', function () {
    $action = ExportAction::make('export')
        ->formats([ExportFormat::Csv, ExportFormat::Xlsx]);

    expect($action->getEnabledFormats())->toHaveCount(2);
    expect($action->getEnabledFormats())->toEqual([ExportFormat::Csv, ExportFormat::Xlsx]);
});

it('accepts default format', function () {
    $action = ExportAction::make('export')
        ->defaultFormat(ExportFormat::Pdf);

    expect($action->getDefaultFormat())->toBe(ExportFormat::Pdf);
});

it('accepts column exclusion', function () {
    $action = ExportAction::make('export')
        ->excludeColumns(['password', 'remember_token']);

    expect($action)->toBeInstanceOf(ExportAction::class);
});

it('accepts additional columns with default values', function () {
    $action = ExportAction::make('export')
        ->additionalColumns([
            AdditionalColumn::make('exported_at')->defaultValue('2026-03-05'),
            AdditionalColumn::make('notes')->label('Notas')->defaultValue('N/A'),
        ]);

    $additionalArray = $action->getAdditionalColumnsAsArray();

    expect($additionalArray)->toHaveCount(2);
    expect($additionalArray['exported_at'])->toBe('Exported At');
    expect($additionalArray['notes'])->toBe('Notas');
});

it('accepts extra view data as array', function () {
    $action = ExportAction::make('export')
        ->extraViewData(['company' => 'Acme Corp']);

    expect($action->resolveExtraViewData())->toBe(['company' => 'Acme Corp']);
});

it('accepts extra view data as closure', function () {
    $action = ExportAction::make('export')
        ->extraViewData(fn () => ['dynamic' => 'value']);

    expect($action->resolveExtraViewData())->toBe(['dynamic' => 'value']);
});

it('supports user column selection flag', function () {
    $action = ExportAction::make('export')
        ->userCanSelectColumns();

    expect($action)->toBeInstanceOf(ExportAction::class);
});
