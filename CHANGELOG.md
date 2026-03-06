# Changelog

All notable changes to this project will be documented in this file.

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
