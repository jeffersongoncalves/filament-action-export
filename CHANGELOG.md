# Changelog

All notable changes to this project will be documented in this file.

## v1.5.8 - 2026-03-08

### Fixed

- Fix print showing empty data: use correct `$get()` paths without `../` prefix (resolved to wrong state path)
- Use Livewire dispatch event to trigger print in browser instead of relying on Alpine x-init re-execution

## v1.5.7 - 2026-03-08

### Fixed

- Fix empty print output - replaced `htmlentities()` with `base64_encode()` in PHP and `atob()` in JS to correctly pass HTML through the template literal without breaking the iframe rendering

## v1.5.6 - 2026-03-08

### Fixed

- Refactor `afterStateUpdated` callback to use `Get $get('../field')` instead of Livewire form methods for reading sibling form field values.

## v1.5.5 - 2026-03-08

### Fixed

- Fix potential `shouldRefresh is not defined` Alpine.js error - inlines PHP boolean values directly in `x-init` instead of relying on `x-data` which may be overridden by Filament's modal component.

## v1.5.4 - 2026-03-08

### Fixed

- Simplified print state detection in table-view blade - replaced direct Livewire property access with component `getState()` method

## v1.5.3 - 2026-03-07

### What's Changed

- **Separate preview modal**: Preview data now opens in a dedicated modal with pagination instead of inline static preview
- **Reactive column filters**: Column filter checkboxes now reflect in Print output on both export and preview modals
- **Hidden field mechanism**: Added reactive communication between JS and Livewire via `table_view` hidden input
- **Print on both screens**: Print via hidden iframe works from both the export modal and the preview modal
- **JS asset registration**: Added `filament-export.js` with `triggerInputEvent()` and `printHTML()` functions
- Removed `ExportPreview` Livewire component (replaced by `TableView` form component)
- Removed `preview-section.blade.php` (replaced by modal in `table-view.blade.php`)

**Full Changelog**: https://github.com/jeffersongoncalves/filament-action-export/compare/v1.5.2...v1.5.3

## v1.5.2 - 2026-03-07

### Improvement

- Refactor: use hidden iframe for print instead of opening a new browser tab. Print dialog now appears inline without navigating away.

## v1.5.1 - 2026-03-07

### Bug Fix

- Fix: wrap print JS in IIFE to fix `Uncaught SyntaxError: Unexpected token 'var'` caused by Livewire's `js()` evaluating via Alpine.js `new Function()`

## v1.5.0 - 2026-03-07

### New Feature

- **Print button**: Added a Print button to the export modal that opens table data in a new browser window and triggers `window.print()`. Works for both HeaderAction and BulkAction. Respects `isPrintEnabled()` and `isDirectDownload()` settings.

## v1.4.0 - 2026-03-07

### Fixed

- Fix tests: replace removed `userCanSelectColumns()` with `disableFilterColumns()`
- Fix filename resolution assertions for new `resolveFileName()` behavior
- Fix disabled prefix assertions to match exact resolved filename

### Added

- Comprehensive `NewTraitFeaturesTest` covering HasPreview, HasPageOrientation, HasAdditionalColumns, HasExportColumns, HasExportFormats, HasFilename, HasTableDataExport (fillDefaultData), config-driven defaults, withColumns, modifyQueryUsing

**Full Changelog**: https://github.com/jeffersongoncalves/filament-action-export/compare/v1.3.0...v1.4.0

## v1.3.0 - 2026-03-06

### What's Changed

- Add `getTableQuery()`, `getRecords()`, `modifyQueryUsing()` to `FilamentExportHeaderAction`
- Add `withFilters()`, `withSearch()`, `withSort()` flags for table state filtering
- Add tests for `modifyQueryUsing` (single, multiple, null callback)

**Full Changelog**: https://github.com/jeffersongoncalves/filament-action-export/compare/v1.2.0...v1.3.0

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
