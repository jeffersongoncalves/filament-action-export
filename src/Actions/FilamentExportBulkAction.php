<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions;

use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasAdditionalColumns;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasCsvDelimiter;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasDirectDownload;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasExportColumns;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasExportFormats;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasExtraViewData;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasFilename;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasFormatStates;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasPdfDriver;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasTableDataExport;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasWriterCallbacks;
use JeffersonGoncalves\FilamentExportAction\Enums\ExportFormat;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FilamentExportBulkAction extends BulkAction
{
    use HasAdditionalColumns;
    use HasCsvDelimiter;
    use HasDirectDownload;
    use HasExportColumns;
    use HasExportFormats;
    use HasExtraViewData;
    use HasFilename;
    use HasFormatStates;
    use HasPdfDriver;
    use HasTableDataExport;
    use HasWriterCallbacks;

    public static function getDefaultName(): ?string
    {
        return 'export';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-action-export::filament-action-export.actions.export'))
            ->icon('heroicon-o-arrow-down-tray')
            ->form(fn () => $this->isDirectDownload() ? [] : $this->buildFormSchema())
            ->action(function (array $data, Collection $records): StreamedResponse {
                $format = $this->isDirectDownload()
                    ? $this->getDefaultFormat()
                    : ExportFormat::from($data['format']);

                if (! $this->isDirectDownload() && $this->canSelectColumns && isset($data['columns'])) {
                    $allTableColumns = $this->resolveColumns($this->getTable());
                    $columns = array_intersect_key($allTableColumns, array_flip($data['columns']));
                } else {
                    $columns = $this->resolveColumns($this->getTable());
                }

                $allColumns = array_merge($columns, $this->getAdditionalColumnsAsArray());

                $userFileName = $data['file_name'] ?? null;
                $pageOrientation = $data['page_orientation'] ?? null;

                return $this->performExport($records, $allColumns, $format, $userFileName, $pageOrientation);
            });
    }
}
