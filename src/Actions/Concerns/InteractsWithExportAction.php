<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions\Concerns;

/**
 * Aggregates every export-action concern and the shared config defaults.
 *
 * Both FilamentExportHeaderAction and FilamentExportBulkAction compose the
 * exact same 16 concerns and apply the same package config defaults in
 * setUp(); this trait keeps that single-sourced. Each action still defines
 * its own getRecords()/schema/action/footer behaviour.
 */
trait InteractsWithExportAction
{
    use CanRefreshTable;
    use HasAdditionalColumns;
    use HasCsvDelimiter;
    use HasDirectDownload;
    use HasExportColumns;
    use HasExportFormats;
    use HasExtraViewData;
    use HasFilename;
    use HasFormatStates;
    use HasPageOrientation;
    use HasPaginator;
    use HasPdfDriver;
    use HasPreview;
    use HasTableDataExport;
    use HasUniqueActionId;
    use HasWriterCallbacks;

    /**
     * Apply the package config defaults shared by every export action.
     */
    protected function applyExportConfigDefaults(): void
    {
        $this->timeFormat(config('filament-action-export.time_format', 'Y-m-d_H-i'));
        $this->disableFileName(config('filament-action-export.disable_file_name', false));
        $this->disableFileNamePrefix(config('filament-action-export.disable_file_name_prefix', false));
        $this->disableAdditionalColumns(config('filament-action-export.disable_additional_columns', false));
        $this->disableFilterColumns(config('filament-action-export.disable_filter_columns', false));

        if (config('filament-action-export.use_snappy', false)) {
            $this->snappy();
        }
    }
}
