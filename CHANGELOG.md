# Changelog

All notable changes to this project will be documented in this file.

## v1.2.0 - 2026-03-06

### What's Changed

#### New Features

- **`disableTableColumns()`** — Ignore table columns entirely and use only additional columns for fully custom exports
- **`disableFileNamePrefix()`** — Control the filename prefix independently (disable prefix without hiding the filename input)
- **`withFilters()` / `withSearch()` / `withSort()` logic** — Now actually connected to the table's filtered/sorted query (previously declared but non-functional)
- **Configurable icons via config** — Set action, preview, export, print, and cancel icons globally in config
- **`use_snappy` global config** — Enable Snappy PDF driver globally without calling `->snappy()` on each action
- Explicit `pdfDriver()` / `snappy()` takes priority over global config

**Full Changelog**: https://github.com/jeffersongoncalves/filament-action-export/compare/v1.1.0...v1.2.0

## v1.1.0 - 2026-03-06

### What's New

#### New Features

- **File Name Control**: Custom file name, prefix, time format, and closure support
- **Direct Download**: Skip the modal and download with default settings
- **CSV Delimiter**: Configurable CSV separator (default: comma)
- **Format States**: Per-column value formatting with closures
- **Writer Callbacks**: Modify Excel/PDF writers before generation
- **With Hidden Columns**: Include toggled columns in export
- **Page Orientation**: Reactive PDF orientation selector (portrait/landscape)

#### Improvements

- 7 new translation files: Spanish, French, German, Italian, Dutch, Arabic, Turkish
- Added \ to config file
- Reactive form fields (format selector shows orientation only for PDF)

#### Compatibility

- Filament v3
- PHP ^8.1
- Laravel ^10.0 | ^11.0

## v1.0.0 - 2026-03-06

### Filament Action Export v1.0.0

Initial release for **Filament v3**.

#### Features

- Export Filament tables to **CSV**, **XLSX** and **PDF**
- Bulk action and header action support
- Column selection and exclusion
- Additional columns with default values
- PDF support with DomPDF and Snappy drivers
- Custom PDF options (paper, orientation)
- Extra view data (static or closure)
- Preview component with print support
- English and Brazilian Portuguese translations

#### Requirements

- PHP ^8.1
- Laravel ^10.0 | ^11.0
- Filament ^3.0
- Livewire ^3.0

## [Unreleased]

## [1.0.0] - 2026-03-05

### Added

- Initial release for Filament v3
- CSV, XLSX, and PDF export support
- BulkAction and HeaderAction for table export
- Column selection with user-facing modal
- Additional columns with default values
- PDF support via dompdf and Snappy drivers
- Export preview with Livewire component
- Print support
- English and Brazilian Portuguese translations
- Customizable views and configuration
