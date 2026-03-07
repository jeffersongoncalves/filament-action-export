<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions;

use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasAdditionalColumns;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasCsvDelimiter;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasDirectDownload;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasExportColumns;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasExportFormats;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasExtraViewData;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasFilename;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasFormatStates;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasPageOrientation;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasPdfDriver;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasPreview;
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
    use HasPageOrientation;
    use HasPdfDriver;
    use HasPreview;
    use HasTableDataExport;
    use HasWriterCallbacks;

    public static function getDefaultName(): ?string
    {
        return 'export';
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Apply config defaults
        $this->timeFormat(config('filament-action-export.time_format', 'Y-m-d_H-i'));
        $this->disableFileName(config('filament-action-export.disable_file_name', false));
        $this->disableFileNamePrefix(config('filament-action-export.disable_file_name_prefix', false));
        $this->disableAdditionalColumns(config('filament-action-export.disable_additional_columns', false));
        $this->disableFilterColumns(config('filament-action-export.disable_filter_columns', false));

        if (config('filament-action-export.use_snappy', false)) {
            $this->snappy();
        }

        $this->label(__('filament-action-export::filament-action-export.actions.export'))
            ->modalHeading(__('filament-action-export::filament-action-export.modal.heading'))
            ->modalSubmitActionLabel(__('filament-action-export::filament-action-export.modal.submit'))
            ->icon(config('filament-action-export.icons.action', 'heroicon-o-arrow-down-tray'))
            ->form(function (Collection $records): array {
                if ($this->isDirectDownload()) {
                    return [];
                }

                return $this->buildFormSchema($records);
            })
            ->action(function (array $data, Collection $records): StreamedResponse {
                $this->fillDefaultData($data);

                $format = ExportFormat::from($data['format']);

                // Resolve columns based on filter selection
                if (! $this->isFilterColumnsDisabled() && isset($data['filter_columns'])) {
                    $allTableColumns = $this->resolveColumns($this->getTable());
                    $columns = array_intersect_key($allTableColumns, array_flip($data['filter_columns']));
                } else {
                    $columns = $this->resolveColumns($this->getTable());
                }

                // Merge predefined additional column headers
                foreach ($this->getAdditionalColumns() as $column) {
                    $columns[$column->getName()] = $column->getLabel();
                }

                $userFileName = $data['file_name'] ?? null;
                $pageOrientation = $data['page_orientation'] ?? $this->getDefaultPageOrientation();
                $userAdditionalColumns = $data['additional_columns'] ?? [];

                return $this->performExport(
                    $records,
                    $columns,
                    $format,
                    $userFileName,
                    $pageOrientation,
                    $userAdditionalColumns,
                );
            })
            ->extraModalFooterActions(function (): array {
                if (! $this->isPrintEnabled() || $this->isDirectDownload()) {
                    return [];
                }

                $parentAction = $this;

                return [
                    \Filament\Actions\Action::make('print')
                        ->label(__('filament-action-export::filament-action-export.actions.print'))
                        ->icon(config('filament-action-export.icons.print', 'heroicon-o-printer'))
                        ->color('gray')
                        ->action(function (array $data) use ($parentAction): void {
                            $records = $parentAction->getSelectedRecords();
                            $html = $parentAction->renderPrintHtml(collect($records), $data);
                            $parentAction->getLivewire()->js(
                                '(() => { let f=document.createElement("iframe");f.style.cssText="position:fixed;left:-9999px;width:0;height:0;";document.body.appendChild(f);f.contentDocument.open();f.contentDocument.write('.json_encode($html).');f.contentDocument.close();setTimeout(() => { f.contentWindow.focus();f.contentWindow.print();setTimeout(() => f.remove(), 100); }, 250); })()'
                            );
                        }),
                ];
            });
    }
}
