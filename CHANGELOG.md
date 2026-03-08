# Changelog

All notable changes to this project will be documented in this file.

## v2.6.4 - 2026-03-08

### Fixed

- Fix `Method TableView::name does not exist` error - use `statePath()` instead of `name()` in `TableView::make()` for Filament v4 compatibility

## v2.6.3 - 2026-03-07

### What's Changed

- **Separate preview modal**: Preview data now opens in a dedicated modal with pagination instead of inline static preview
- **Reactive column filters**: Column filter checkboxes now reflect in Print output on both export and preview modals
- **Hidden field mechanism**: Added reactive communication between JS and Livewire via `table_view` hidden input
- **Print on both screens**: Print via hidden iframe works from both the export modal and the preview modal
- **JS asset registration**: Added `filament-export.js` with `triggerInputEvent()` and `printHTML()` functions
- Removed `ExportPreview` Livewire component (replaced by `TableView` form component)
- Removed `preview-section.blade.php` (replaced by modal in `table-view.blade.php`)

**Full Changelog**: https://github.com/jeffersongoncalves/filament-action-export/compare/v2.6.2...v2.6.3

## v2.6.2 - 2026-03-07

### Improvement

- Refactor: use hidden iframe for print instead of opening a new browser tab. Print dialog now appears inline without navigating away.

## v2.6.1 - 2026-03-07

### Bug Fix

- Fix: wrap print JS in IIFE to fix `Uncaught SyntaxError: Unexpected token 'var'` caused by Livewire's `js()` evaluating via Alpine.js `new Function()`

## v2.6.0 - 2026-03-07

### New Feature

- **Print button**: Added a Print button to the export modal that opens table data in a new browser window and triggers `window.print()`. Works for both HeaderAction and BulkAction. Respects `isPrintEnabled()` and `isDirectDownload()` settings.

## v2.5.1 - 2026-03-07

### Bug Fix

- Fix: correct `Filament\Forms\Components\Component` docblock namespace to `Filament\Schemas\Components\Component` (Filament v4 compatible)

## v2.5.0 - 2026-03-07

### What's Changed

#### Bug Fixes

- Fixed tests for renamed/removed methods (disableFilterColumns replaces userCanSelectColumns)
- Fixed filename resolution assertions to match new behavior

#### New

- Added comprehensive NewTraitFeaturesTest with 52 tests covering all traits
- Tests for HasPreview, HasPageOrientation, HasAdditionalColumns, HasExportColumns, HasExportFormats, HasFilename, fillDefaultData, config-driven defaults, modifyQueryUsing, and table state flags

**Full Changelog**: https://github.com/jeffersongoncalves/filament-action-export/compare/v2.4.0...v2.5.0

## v2.4.0 - 2026-03-06

### What's Changed

- Remove `ExportAction` class (use `FilamentExportHeaderAction` or `FilamentExportBulkAction` instead)
- Replace deprecated `form()` with `schema()` for Filament v4 compatibility
- Update all tests to use `FilamentExportBulkAction`/`FilamentExportHeaderAction`

**Full Changelog**: https://github.com/jeffersongoncalves/filament-action-export/compare/v2.3.0...v2.4.0

## v2.3.0 - 2026-03-06

### What's Changed

#### New Features

- **FilamentExportBulkAction**: Table bulk action for exporting selected records
- **FilamentExportHeaderAction**: Table header action with `withFilters()`, `withSearch()`, and `withSort()` support

#### Full Changelog

https://github.com/jeffersongoncalves/filament-action-export/compare/v2.2.0...v2.3.0

## v2.2.0 - 2026-03-06

### What's Changed

#### New Features

- **`disableTableColumns()`** — Ignore table columns entirely and use only additional columns for fully custom exports
- **`disableFileNamePrefix()`** — Control the filename prefix independently (disable prefix without hiding the filename input)
- **`withFilters()` / `withSearch()` / `withSort()` logic** — Now actually connected to the table's filtered/sorted query (previously declared but non-functional)
- **Configurable icons via config** — Set action, preview, export, print, and cancel icons globally in config
- **`use_snappy` global config** — Enable Snappy PDF driver globally without calling `->snappy()` on each action
- Explicit `pdfDriver()` / `snappy()` takes priority over global config

**Full Changelog**: https://github.com/jeffersongoncalves/filament-action-export/compare/v2.1.0...v2.2.0

## v2.1.0 - 2026-03-06

### What's New

#### New Features

- **File Name Control**: Custom file name, prefix, time format, and closure support
- **Direct Download**: Skip the modal and download with default settings
- **CSV Delimiter**: Configurable CSV separator (default: comma)
- **Format States**: Per-column value formatting with closures
- **Writer Callbacks**: Modify Excel/PDF writers before generation
- **With Hidden Columns**: Include toggled columns in export
- **Page Orientation**: Reactive PDF orientation selector (portrait/landscape)
- **Removed FilamentExportPlugin**: Actions are configured per-resource, no plugin needed

#### Improvements

- 7 new translation files: Spanish, French, German, Italian, Dutch, Arabic, Turkish
- Added csv_delimiter to config file
- Reactive form fields (format selector shows orientation only for PDF)

#### Compatibility

- Filament v4
- PHP ^8.2
- Laravel ^11.0

## v2.0.0 - 2026-03-06

### Filament Action Export v2.0.0

Release for **Filament v4**.

#### Changes from v1.x

- Unified `ExportAction` replacing `FilamentExportBulkAction` and `FilamentExportHeaderAction`
- `FilamentExportPlugin` for panel registration
- Refactored to use `PackageServiceProvider` (spatie/laravel-package-tools)
- Compiled CSS with `fi-export-*` classes via PostCSS
- Pint and Larastan tooling

#### Features

- Export Filament tables to **CSV**, **XLSX** and **PDF**
- Bulk action and header action support
- Column selection and exclusion
- Additional columns with default values
- PDF support with DomPDF and Snappy drivers
- Extra view data (static or closure)
- Preview component with print support
- Table filters, search and sort support for header actions

#### Requirements

- PHP ^8.2
- Laravel ^11.0
- Filament ^4.0
- Livewire ^3.0

## [Unreleased]
