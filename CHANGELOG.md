# Changelog

All notable changes to this project will be documented in this file.

## v3.0.0 - 2026-03-06

### Filament Action Export v3.0.0

Release for **Filament v5** with **Livewire v4** support.

#### Changes from v2.x

- Upgraded to Filament v5 and Livewire v4
- Self-closing `<livewire:>` tags (Livewire v4 requirement)
- LivewireServiceProvider registered in test environment
- API remains identical to v2.x — no code changes needed

#### Features

- Export Filament tables to **CSV**, **XLSX** and **PDF**
- Unified `ExportAction` for bulk and header actions
- `FilamentExportPlugin` for panel registration
- Column selection and exclusion
- Additional columns with default values
- PDF support with DomPDF and Snappy drivers
- Extra view data (static or closure)
- Preview component with print support
- Table filters, search and sort support for header actions
- Pint and Larastan tooling (level 5)

#### Requirements

- PHP ^8.2
- Laravel ^11.0
- Filament ^5.0
- Livewire ^4.0

## [Unreleased]
