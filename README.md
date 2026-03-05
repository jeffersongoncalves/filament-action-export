# Filament Action Export

[![Tests](https://github.com/jeffersongoncalves/filament-action-export/actions/workflows/tests.yml/badge.svg?branch=1.x)](https://github.com/jeffersongoncalves/filament-action-export/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/filament-action-export.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/filament-action-export)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/filament-action-export.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/filament-action-export)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/filament-action-export.svg?style=flat-square)](LICENSE.md)

Export Filament tables to **CSV**, **XLSX** and **PDF** with preview and print support.

## Version Compatibility

| Package | Filament | PHP    | Laravel          | Livewire |
|---------|----------|--------|------------------|----------|
| ^1.0    | ^3.0     | ^8.1   | ^10.0 \| ^11.0   | ^3.0     |
| ^2.0    | ^4.0     | ^8.2   | ^11.0            | ^3.0     |
| ^3.0    | ^5.0     | ^8.2   | ^11.0            | ^4.0     |

> This is the **`1.x`** branch, compatible with **Filament v3**.

## Installation

```bash
composer require jeffersongoncalves/filament-action-export "^1.0"
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

### Bulk Action

Add the export action to your table's bulk actions to allow users to export selected records:

```php
use JeffersonGoncalves\FilamentExportAction\Actions\FilamentExportBulkAction;
use JeffersonGoncalves\FilamentExportAction\Enums\ExportFormat;
use JeffersonGoncalves\FilamentExportAction\ValueObjects\AdditionalColumn;

public function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('email'),
        ])
        ->bulkActions([
            FilamentExportBulkAction::make('export')
                ->formats([ExportFormat::Csv, ExportFormat::Xlsx, ExportFormat::Pdf])
                ->defaultFormat(ExportFormat::Xlsx)
                ->userCanSelectColumns()
                ->excludeColumns(['password', 'remember_token'])
                ->additionalColumns([
                    AdditionalColumn::make('exported_at')
                        ->defaultValue(now()->format('d/m/Y')),
                ])
                ->extraViewData(['companyName' => 'Acme Corp']),
        ]);
}
```

### Header Action

Add the export action to your table's header actions to export all records (respecting active filters):

```php
use JeffersonGoncalves\FilamentExportAction\Actions\FilamentExportHeaderAction;
use JeffersonGoncalves\FilamentExportAction\Enums\ExportFormat;

public function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('email'),
        ])
        ->headerActions([
            FilamentExportHeaderAction::make('export')
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

Control which export formats are available:

```php
->formats([ExportFormat::Csv, ExportFormat::Xlsx, ExportFormat::Pdf])
->defaultFormat(ExportFormat::Xlsx)
```

### Columns

```php
// Use specific columns
->columns(['id', 'name', 'email'])

// Exclude columns
->excludeColumns(['password', 'remember_token'])

// Let users choose columns in the modal
->userCanSelectColumns()
```

### Additional Columns

Add extra columns that don't exist in the table:

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

### Extra View Data

Pass additional data to the PDF/print views:

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

The package includes a Livewire component for table preview:

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

The package includes English and Brazilian Portuguese translations. After publishing, add your own translations in `lang/vendor/filament-action-export/`.

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
