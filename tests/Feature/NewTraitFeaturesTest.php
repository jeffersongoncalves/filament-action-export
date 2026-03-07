<?php

declare(strict_types=1);

use JeffersonGoncalves\FilamentExportAction\Actions\FilamentExportBulkAction;
use JeffersonGoncalves\FilamentExportAction\Actions\FilamentExportHeaderAction;
use JeffersonGoncalves\FilamentExportAction\Enums\ExportFormat;
use JeffersonGoncalves\FilamentExportAction\ValueObjects\AdditionalColumn;

// --- HasPreview ---

it('preview is enabled by default', function () {
    $action = FilamentExportHeaderAction::make('export');

    expect($action->isPreviewEnabled())->toBeTrue();
});

it('can disable preview', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->disablePreview();

    expect($action->isPreviewEnabled())->toBeFalse();
});

it('can re-enable preview after disabling', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->disablePreview()
        ->disablePreview(false);

    expect($action->isPreviewEnabled())->toBeTrue();
});

it('reads preview_enabled from config', function () {
    config()->set('filament-action-export.preview_enabled', false);

    $action = FilamentExportHeaderAction::make('export');

    expect($action->isPreviewEnabled())->toBeFalse();
});

it('explicit disablePreview overrides config', function () {
    config()->set('filament-action-export.preview_enabled', true);

    $action = FilamentExportHeaderAction::make('export')
        ->disablePreview();

    expect($action->isPreviewEnabled())->toBeFalse();
});

it('print is enabled by default', function () {
    $action = FilamentExportHeaderAction::make('export');

    expect($action->isPrintEnabled())->toBeTrue();
});

it('can disable print', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->disablePrint();

    expect($action->isPrintEnabled())->toBeFalse();
});

// --- HasPageOrientation ---

it('default page orientation is portrait', function () {
    $action = FilamentExportHeaderAction::make('export');

    expect($action->getDefaultPageOrientation())->toBe('portrait');
});

it('can set default page orientation', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->defaultPageOrientation('landscape');

    expect($action->getDefaultPageOrientation())->toBe('landscape');
});

it('reads page orientation from config', function () {
    config()->set('filament-action-export.pdf_options.orientation', 'landscape');

    $action = FilamentExportHeaderAction::make('export');

    expect($action->getDefaultPageOrientation())->toBe('landscape');
});

it('explicit page orientation overrides config', function () {
    config()->set('filament-action-export.pdf_options.orientation', 'landscape');

    $action = FilamentExportHeaderAction::make('export')
        ->defaultPageOrientation('portrait');

    expect($action->getDefaultPageOrientation())->toBe('portrait');
});

// --- HasAdditionalColumns ---

it('has no additional columns by default', function () {
    $action = FilamentExportHeaderAction::make('export');

    expect($action->getAdditionalColumns())->toBeEmpty();
});

it('can set additional columns', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->additionalColumns([
            AdditionalColumn::make('notes')->label('Notas'),
        ]);

    expect($action->getAdditionalColumns())->toHaveCount(1);
});

it('additional columns are not disabled by default', function () {
    $action = FilamentExportHeaderAction::make('export');

    expect($action->isAdditionalColumnsDisabled())->toBeFalse();
});

it('can disable additional columns', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->disableAdditionalColumns();

    expect($action->isAdditionalColumnsDisabled())->toBeTrue();
});

it('reads disable_additional_columns from config', function () {
    config()->set('filament-action-export.disable_additional_columns', true);

    $action = FilamentExportHeaderAction::make('export');

    expect($action->isAdditionalColumnsDisabled())->toBeTrue();
});

// --- HasExportColumns ---

it('can set specific export columns', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->columns(['name', 'email']);

    expect($action)->toBeInstanceOf(FilamentExportHeaderAction::class);
});

it('can exclude specific columns', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->excludeColumns(['password', 'remember_token']);

    expect($action)->toBeInstanceOf(FilamentExportHeaderAction::class);
});

it('hidden columns are not shown by default', function () {
    $action = FilamentExportHeaderAction::make('export');

    expect($action->shouldShowHiddenColumns())->toBeFalse();
});

it('can enable hidden columns', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->withHiddenColumns();

    expect($action->shouldShowHiddenColumns())->toBeTrue();
});

it('table columns are not disabled by default', function () {
    $action = FilamentExportHeaderAction::make('export');

    expect($action->isTableColumnsDisabled())->toBeFalse();
});

it('can disable table columns', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->disableTableColumns();

    expect($action->isTableColumnsDisabled())->toBeTrue();
});

// --- HasExportFormats ---

it('can disable format selector', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->disableFormats();

    expect($action->isFormatsDisabled())->toBeTrue();
});

it('format selector is enabled by default', function () {
    $action = FilamentExportHeaderAction::make('export');

    expect($action->isFormatsDisabled())->toBeFalse();
});

it('default format comes from config', function () {
    config()->set('filament-action-export.default_format', 'csv');

    $action = FilamentExportHeaderAction::make('export');

    expect($action->getDefaultFormat())->toBe(ExportFormat::Csv);
});

it('explicit default format overrides config', function () {
    config()->set('filament-action-export.default_format', 'csv');

    $action = FilamentExportHeaderAction::make('export')
        ->defaultFormat(ExportFormat::Pdf);

    expect($action->getDefaultFormat())->toBe(ExportFormat::Pdf);
});

it('enabled formats come from config', function () {
    config()->set('filament-action-export.formats', ['csv', 'pdf']);

    $action = FilamentExportHeaderAction::make('export');

    $formats = $action->getEnabledFormats();

    expect($formats)->toHaveCount(2);
    expect($formats[0])->toBe(ExportFormat::Csv);
    expect($formats[1])->toBe(ExportFormat::Pdf);
});

// --- HasFilename ---

it('resolves filename with prefix and custom fileName', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->fileNamePrefix('users')
        ->fileName('export-2026');

    expect($action->resolveFileName())->toBe('users-export-2026');
});

it('resolves filename with only fileName', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->disableFileNamePrefix()
        ->fileName('my-report');

    expect($action->resolveFileName())->toBe('my-report');
});

it('resolves filename with only prefix uses timestamp', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->fileNamePrefix('users');

    $resolved = $action->resolveFileName();

    expect($resolved)->toStartWith('users-');
    // Should have a timestamp part after the prefix
    $parts = explode('-', $resolved, 2);
    expect($parts[0])->toBe('users');
    expect(strlen($parts[1]))->toBeGreaterThan(0);
});

it('hasCustomFileNamePrefix returns false by default', function () {
    $action = FilamentExportHeaderAction::make('export');

    expect($action->hasCustomFileNamePrefix())->toBeFalse();
});

it('hasCustomFileNamePrefix returns true after setting prefix', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->fileNamePrefix('users');

    expect($action->hasCustomFileNamePrefix())->toBeTrue();
});

it('reads time_format from config', function () {
    config()->set('filament-action-export.time_format', 'd/m/Y');

    $action = FilamentExportHeaderAction::make('export');

    expect($action->getTimeFormat())->toBe('d/m/Y');
});

it('reads disable_file_name from config', function () {
    config()->set('filament-action-export.disable_file_name', true);

    $action = FilamentExportHeaderAction::make('export');

    expect($action->isFileNameEnabled())->toBeFalse();
});

it('reads disable_file_name_prefix from config', function () {
    config()->set('filament-action-export.disable_file_name_prefix', true);

    $action = FilamentExportHeaderAction::make('export');

    expect($action->isFileNamePrefixEnabled())->toBeFalse();
});

// --- HasTableDataExport fillDefaultData ---

it('fillDefaultData sets format when missing', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->defaultFormat(ExportFormat::Csv)
        ->disableFilterColumns();

    $data = [];
    $action->fillDefaultData($data);

    expect($data['format'])->toBe('csv');
});

it('fillDefaultData sets page_orientation when missing', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->defaultPageOrientation('landscape')
        ->disableFilterColumns();

    $data = [];
    $action->fillDefaultData($data);

    expect($data['page_orientation'])->toBe('landscape');
});

it('fillDefaultData sets file_name when missing', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->fileName('report')
        ->disableFilterColumns();

    $data = [];
    $action->fillDefaultData($data);

    expect($data['file_name'])->toBe('report');
});

it('fillDefaultData preserves existing values', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->defaultFormat(ExportFormat::Csv)
        ->disableFilterColumns();

    $data = ['format' => 'xlsx'];
    $action->fillDefaultData($data);

    expect($data['format'])->toBe('xlsx');
});

it('fillDefaultData initializes additional_columns as empty array', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->disableFilterColumns();

    $data = [];
    $action->fillDefaultData($data);

    expect($data['additional_columns'])->toBe([]);
});

// --- Config-driven defaults for BulkAction ---

it('bulk action reads disable_filter_columns from config', function () {
    config()->set('filament-action-export.disable_filter_columns', true);

    $action = FilamentExportBulkAction::make('export');

    expect($action->isFilterColumnsDisabled())->toBeTrue();
});

it('bulk action reads disable_additional_columns from config', function () {
    config()->set('filament-action-export.disable_additional_columns', true);

    $action = FilamentExportBulkAction::make('export');

    expect($action->isAdditionalColumnsDisabled())->toBeTrue();
});

it('bulk action reads disable_file_name from config', function () {
    config()->set('filament-action-export.disable_file_name', true);

    $action = FilamentExportBulkAction::make('export');

    expect($action->isFileNameEnabled())->toBeFalse();
});

// --- withColumns (extra Column objects) ---

it('can add extra columns via withColumns', function () {
    $column = \Filament\Tables\Columns\TextColumn::make('custom_field')
        ->label('Custom');

    $action = FilamentExportHeaderAction::make('export')
        ->withColumns([$column]);

    expect($action->getWithColumns())->toHaveCount(1);
    expect($action->getWithColumns()[0]->getName())->toBe('custom_field');
});

// --- modifyQueryUsing (HeaderAction) ---

it('stores multiple modifyQuery callbacks', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->modifyQueryUsing(fn ($query) => $query->where('a', 1))
        ->modifyQueryUsing(fn ($query) => $query->where('b', 2));

    expect($action->getModifyQueryCallbacks())->toHaveCount(2);
});

it('ignores null modifyQuery callbacks', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->modifyQueryUsing(null);

    expect($action->getModifyQueryCallbacks())->toBeEmpty();
});

// --- isWithFilters / isWithSearch / isWithSort ---

it('withFilters is false by default', function () {
    $action = FilamentExportHeaderAction::make('export');

    expect($action->isWithFilters())->toBeFalse();
});

it('withSearch is false by default', function () {
    $action = FilamentExportHeaderAction::make('export');

    expect($action->isWithSearch())->toBeFalse();
});

it('withSort is false by default', function () {
    $action = FilamentExportHeaderAction::make('export');

    expect($action->isWithSort())->toBeFalse();
});

it('withFilters returns true after enabling', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->withFilters();

    expect($action->isWithFilters())->toBeTrue();
});

it('withSearch returns true after enabling', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->withSearch();

    expect($action->isWithSearch())->toBeTrue();
});

it('withSort returns true after enabling', function () {
    $action = FilamentExportHeaderAction::make('export')
        ->withSort();

    expect($action->isWithSort())->toBeTrue();
});
