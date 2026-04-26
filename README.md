<div class="filament-hidden">

![Filament Action Export](https://raw.githubusercontent.com/jeffersongoncalves/filament-action-export/3.x/art/jeffersongoncalves-filament-action-export.png)

</div>

# Filament Action Export

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/filament-action-export.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/filament-action-export)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/filament-action-export/tests.yml?branch=3.x&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/filament-action-export/actions?query=workflow%3Atests+branch%3A3.x)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/filament-action-export/pint.yml?branch=3.x&label=code%20style&style=flat-square)](https://github.com/jeffersongoncalves/filament-action-export/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3A3.x)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/filament-action-export.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/filament-action-export)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/filament-action-export.svg?style=flat-square)](LICENSE.md)

Export Filament tables to **CSV**, **XLSX** and **PDF** with preview, print support, and full customization.

## Compatibility

| Package Version                                                                 | Filament Version | PHP     |
|---------------------------------------------------------------------------------|------------------|---------|
| [1.x](https://github.com/jeffersongoncalves/filament-action-export/tree/1.x)   | 3.x              | ^8.1    |
| [2.x](https://github.com/jeffersongoncalves/filament-action-export/tree/2.x)   | 4.x              | ^8.2    |
| [3.x](https://github.com/jeffersongoncalves/filament-action-export/tree/3.x)   | 5.x              | ^8.2    |

## Installation

```bash
composer require jeffersongoncalves/filament-action-export "^3.0"
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

Add the export action to your table's header actions to export all records (respecting active filters, search, and sort):

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

// Disable prefix
->disableFileNamePrefix()

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

// Add extra Filament Column objects
->withColumns([
    TextColumn::make('full_address'),
])

// Disable the column filter checkboxes in the modal
->disableFilterColumns()

// Include hidden (toggled) columns in the export
->withHiddenColumns()

// Disable table columns entirely (use only additional columns)
->disableTableColumns()
```

### Additional Columns

Add extra columns with user-fillable inputs in the export modal:

```php
->additionalColumns([
    AdditionalColumn::make('exported_at')
        ->label('Exported At')
        ->defaultValue(now()->format('d/m/Y')),
    AdditionalColumn::make('notes')
        ->label('Notes')
        ->defaultValue('N/A'),
])

// Disable additional columns
->disableAdditionalColumns()
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

### Page Orientation

```php
// Set default page orientation for PDF export
->defaultPageOrientation('landscape')  // Default: 'portrait'
```

### Preview & Print

```php
// Disable preview in the modal
->disablePreview()

// Disable print button
->disablePrint()
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

Pass additional data to the PDF/print Blade templates:

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
// Apply active table filters to export
->withFilters()

// Apply active search to export
->withSearch()

// Apply active sort to export
->withSort()

// Modify the query before export
->modifyQueryUsing(fn ($query) => $query->where('active', true))

// Multiple query modifications (they stack)
->modifyQueryUsing(fn ($query) => $query->where('active', true))
->modifyQueryUsing(fn ($query) => $query->where('role', 'admin'))
```

## Config File

All options can be set globally via the config file:

```php
// config/filament-action-export.php

return [
    'pdf_driver'                => env('FILAMENT_EXPORT_PDF_DRIVER', 'dompdf'),
    'default_format'            => env('FILAMENT_EXPORT_DEFAULT_FORMAT', 'xlsx'),
    'formats'                   => ['csv', 'xlsx', 'pdf'],
    'csv_delimiter'             => ',',
    'chunk_size'                => 1000,
    'time_format'               => 'Y-m-d_H-i',
    'pdf_options'               => [
        'paper'       => 'a4',
        'orientation' => 'portrait',
    ],
    'preview_enabled'           => true,
    'print_enabled'             => true,
    'use_snappy'                => false,
    'disable_additional_columns'=> false,
    'disable_filter_columns'    => false,
    'disable_file_name'         => false,
    'disable_file_name_prefix'  => false,
    'disable_preview'           => false,
    'icons'                     => [
        'action'  => 'heroicon-o-arrow-down-tray',
        'preview' => 'heroicon-o-eye',
        'export'  => 'heroicon-o-arrow-down-tray',
        'print'   => 'heroicon-o-printer',
        'cancel'  => 'heroicon-o-x-circle',
    ],
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

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jefferson Goncalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
