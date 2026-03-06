<div class="filament-hidden">

![Filament Action Export](https://raw.githubusercontent.com/jeffersongoncalves/filament-action-export/3.x/art/jeffersongoncalves-filament-action-export.png)

</div>

# Filament Action Export

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/filament-action-export.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/filament-action-export)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/filament-action-export/tests.yml?branch=3.x&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/filament-action-export/actions?query=workflow%3Atests+branch%3A3.x)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/filament-action-export/pint.yml?branch=3.x&label=code%20style&style=flat-square)](https://github.com/jeffersongoncalves/filament-action-export/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3A3.x)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/filament-action-export.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/filament-action-export)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/filament-action-export.svg?style=flat-square)](LICENSE.md)

Export Filament tables to **CSV**, **XLSX** and **PDF** with preview and print support.

## Compatibility

| Package Version                                                                 | Filament Version |
|---------------------------------------------------------------------------------|------------------|
| [1.x](https://github.com/jeffersongoncalves/filament-action-export/tree/1.x)   | 3.x              |
| [2.x](https://github.com/jeffersongoncalves/filament-action-export/tree/2.x)   | 4.x              |
| [3.x](https://github.com/jeffersongoncalves/filament-action-export/tree/3.x)   | 5.x              |

## Installation

You can install the package via composer:

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
