<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions;

use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\InteractsWithExportAction;
use JeffersonGoncalves\FilamentExportAction\Enums\ExportFormat;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FilamentExportBulkAction extends BulkAction
{
    use InteractsWithExportAction;

    public static function getDefaultName(): ?string
    {
        return 'export';
    }

    public function getRecords(): \Illuminate\Support\Collection
    {
        /** @var HasTable&Component $livewire */
        $livewire = $this->getLivewire();

        return collect($livewire->getSelectedTableRecords());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->uniqueActionId('bulk-action');

        $this->applyExportConfigDefaults();

        $this->label(__('filament-action-export::filament-action-export.actions.export'))
            ->modalHeading(__('filament-action-export::filament-action-export.modal.heading'))
            ->icon(config('filament-action-export.icons.action', 'heroicon-o-arrow-down-tray'))
            ->form(function (Collection $records): array {
                if ($this->isDirectDownload()) {
                    return [];
                }

                // Create manual paginator for bulk action records
                $livewire = $this->getLivewire();
                $currentPage = LengthAwarePaginator::resolveCurrentPage('exportPage');
                /** @phpstan-ignore property.notFound */
                $perPage = $livewire->tableRecordsPerPage === 'all'
                    ? $records->count()
                    : (int) $livewire->tableRecordsPerPage;

                $paginator = new LengthAwarePaginator(
                    $records->forPage($currentPage, $perPage),
                    $records->count(),
                    $perPage,
                    $currentPage,
                    ['pageName' => 'exportPage']
                );

                $this->paginator($paginator);

                return $this->buildFormSchema($paginator);
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
            ->modalFooterActions(function (): array {
                if ($this->isDirectDownload()) {
                    return [];
                }

                return $this->getBulkExportModalActions();
            });
    }
}
