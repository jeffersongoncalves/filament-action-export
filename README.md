<div class="filament-hidden">

![Filament Action Export](https://raw.githubusercontent.com/jeffersongoncalves/filament-action-export/2.x/art/jeffersongoncalves-filament-action-export.png)

</div>

# Filament Action Export

[![Tests](https://github.com/jeffersongoncalves/filament-action-export/actions/workflows/tests.yml/badge.svg?branch=2.x)](https://github.com/jeffersongoncalves/filament-action-export/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/filament-action-export.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/filament-action-export)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/filament-action-export.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/filament-action-export)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/filament-action-export.svg?style=flat-square)](LICENSE.md)

Export Filament tables to **CSV**, **XLSX** and **PDF** with preview and print support.

## Version Compatibility

| Package | Filament | PHP    | Laravel          | Livewire |
|---------|----------|--------|------------------|----------|
| ^1.0    | ^3.0     | ^8.1   | ^10.0 \| ^11.0   | ^3.0     |
| ^2.0    | ^4.0     | ^8.2   | ^11.0            | ^3.0     |
| ^3.0    | ^5.0     | ^8.2   | ^11.28           | ^4.0     |

> This is the **`2.x`** branch, compatible with **Filament v4**.

## Upgrading from v1.x

The `2.x` branch introduces a unified `ExportAction` replacing the separate `FilamentExportBulkAction` and `FilamentExportHeaderAction`. See the [migration section](#migrating-from-1x) below.

## Installation

```bash
composer require jeffersongoncalves/filament-action-export "^2.0"
```

### Publish config (optional)

```bash
php artisan vendor:publish --tag=filament-action-export-config
```

### Publish views (optional)

```bash
php artisan vendor:publish --tag=filament-action-export-views
```

### Publish translations (optional)

```bash
php artisan vendor:publish --tag=filament-action-export-lang
```

## Usage

### As Bulk Action

```php
use JeffersonGoncalves\FilamentExportAction\Actions\ExportAction;
use JeffersonGoncalves\FilamentExportAction\Enums\ExportFormat;
use JeffersonGoncalves\FilamentExportAction\ValueObjects\AdditionalColumn;

public function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('email'),
        ])
        ->toolbarActions([
            Actions\BulkActionGroup::make([
                ExportAction::make('export')
                    ->formats([ExportFormat::Csv, ExportFormat::Xlsx, ExportFormat::Pdf])
                    ->defaultFormat(ExportFormat::Xlsx)
                    ->userCanSelectColumns()
                    ->excludeColumns(['password', 'remember_token'])
                    ->additionalColumns([
                        AdditionalColumn::make('exported_at')
                            ->defaultValue(now()->format('d/m/Y')),
                    ])
                    ->extraViewData(['companyName' => 'Acme Corp']),
            ]),
        ]);
}
```

### As Header Action

```php
use JeffersonGoncalves\FilamentExportAction\Actions\ExportAction;
use JeffersonGoncalves\FilamentExportAction\Enums\ExportFormat;

public function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('email'),
        ])
        ->headerActions([
            ExportAction::make('export')
                ->formats([ExportFormat::Csv, ExportFormat::Xlsx, ExportFormat::Pdf])
                ->defaultFormat(ExportFormat::Xlsx)
                ->withFilters()
                ->withSearch()
                ->withSort()
                ->snappy()
                ->extraViewData(['companyName' => 'Acme Corp']),
        ]);
}
```

## Configuration Options

### Formats

```php
->formats([ExportFormat::Csv, ExportFormat::Xlsx, ExportFormat::Pdf])
->defaultFormat(ExportFormat::Xlsx)
```

### File Name

```php
// Custom file name
->fileName('my-report')

// File name prefix (prepended to the name)
->fileNamePrefix('users')

// Custom time format for the filename suffix
->timeFormat('d_m_Y-H_i')

// Disable file name input in the modal
->disableFileName()

// Full control via closure
->fileNameUsing(fn ($action) => 'custom-' . now()->format('Y-m-d'))
```

### Direct Download

Skip the modal form and download immediately with default settings:

```php
->directDownload()
```

### Columns

```php
// Use specific columns
->columns(['id', 'name', 'email'])

// Exclude columns
->excludeColumns(['password', 'remember_token'])

// Let users choose columns in the modal
->userCanSelectColumns()

// Include hidden (toggled) columns in the export
->withHiddenColumns()
```

### Additional Columns

```php
->additionalColumns([
    AdditionalColumn::make('exported_at')
        ->label('Exported At')
        ->defaultValue(now()->format('d/m/Y')),
    AdditionalColumn::make('notes')
        ->label('Notes')
        ->defaultValue('N/A'),
])
```

### Format States

Custom formatting for column values:

```php
->formatStates([
    'name' => fn ($value, $record) => strtoupper($value),
    'created_at' => fn ($value) => Carbon::parse($value)->format('d/m/Y'),
    'status' => fn ($value) => match ($value) {
        'active' => 'Active',
        'inactive' => 'Inactive',
        default => $value,
    },
])
```

### CSV Delimiter

```php
->csvDelimiter(';')  // Default: ','
```

### PDF Driver

By default, the package uses [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf). You can switch to [barryvdh/laravel-snappy](https://github.com/barryvdh/laravel-snappy):

```bash
composer require barryvdh/laravel-snappy
```

```php
// Use Snappy
->snappy()

// Or set driver explicitly
->pdfDriver('snappy')

// Custom PDF options
->pdfOptions(['paper' => 'a4', 'orientation' => 'landscape'])
```

### Writer Callbacks

Customize the Excel or PDF writer before the file is generated:

```php
// Modify the SimpleExcelWriter (CSV/XLSX)
->modifyExcelWriter(fn (SimpleExcelWriter $writer) => $writer)

// Modify the PDF instance (DomPDF or Snappy)
->modifyPdfWriter(fn ($pdf) => $pdf->setWarnings(false))
```

### Extra View Data

```php
// Static array
->extraViewData(['companyName' => 'Acme Corp'])

// Dynamic closure
->extraViewData(fn ($action) => [
    'recordCount' => $action->getRecords()->count(),
])
```

### Header Action Specific Options

```php
->withFilters()   // Apply active table filters to export
->withSearch()    // Apply active search to export
->withSort()      // Apply active sort to export
```

## Preview Component

```blade
<livewire:filament-action-export.export-preview
    :records="$records"
    :columns="$columns"
    :extra-data="$extraData"
/>
```

## Config File

```php
// config/filament-action-export.php

return [
    'pdf_driver'      => env('FILAMENT_EXPORT_PDF_DRIVER', 'dompdf'),
    'default_format'  => env('FILAMENT_EXPORT_DEFAULT_FORMAT', 'xlsx'),
    'formats'         => ['csv', 'xlsx', 'pdf'],
    'csv_delimiter'   => ',',
    'chunk_size'      => 1000,
    'pdf_options'     => [
        'paper'       => 'a4',
        'orientation' => 'portrait',
    ],
    'preview_enabled' => true,
    'print_enabled'   => true,
];
```

## Customizing Views

After publishing the views, you can customize them:

- `resources/views/vendor/filament-action-export/pdf.blade.php` - PDF template
- `resources/views/vendor/filament-action-export/print.blade.php` - Print template
- `resources/views/vendor/filament-action-export/components/table-view.blade.php` - Preview table

## Translations

The package includes translations for: English, Brazilian Portuguese, Spanish, French, German, Italian, Dutch, Arabic, and Turkish.

After publishing, add your own translations in `lang/vendor/filament-action-export/`.

## Migrating from 1.x

### Action Classes

```php
// Before (v1.x - Filament v3)
use JeffersonGoncalves\FilamentExportAction\Actions\FilamentExportBulkAction;
use JeffersonGoncalves\FilamentExportAction\Actions\FilamentExportHeaderAction;

$table->bulkActions([
    FilamentExportBulkAction::make('export'),
]);
$table->headerActions([
    FilamentExportHeaderAction::make('export'),
]);

// After (v2.x - Filament v4)
use JeffersonGoncalves\FilamentExportAction\Actions\ExportAction;

$table->toolbarActions([
    Actions\BulkActionGroup::make([
        ExportAction::make('export'),
    ]),
]);
$table->headerActions([
    ExportAction::make('export'),
]);
```

### Filament v4 Table Method Changes

```php
// v3
->actions([...])
->bulkActions([...])

// v4
->recordActions([...])
->toolbarActions([...])
```

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email security@jeffersongoncalves.com instead of using the issue tracker.

## Credits

- [Jefferson Goncalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
