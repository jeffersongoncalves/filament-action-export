<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions;

use Closure;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\InteractsWithExportAction;
use JeffersonGoncalves\FilamentExportAction\Enums\ExportFormat;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FilamentExportHeaderAction extends Action
{
    use InteractsWithExportAction;

    protected bool $withFilters = false;

    protected bool $withSearch = false;

    protected bool $withSort = false;

    /** @var array<int, Closure> */
    protected array $modifyQueryCallbacks = [];

    public static function getDefaultName(): ?string
    {
        return 'export';
    }

    public function withFilters(bool $enabled = true): static
    {
        $this->withFilters = $enabled;

        return $this;
    }

    public function isWithFilters(): bool
    {
        return $this->withFilters;
    }

    public function withSearch(bool $enabled = true): static
    {
        $this->withSearch = $enabled;

        return $this;
    }

    public function isWithSearch(): bool
    {
        return $this->withSearch;
    }

    public function withSort(bool $enabled = true): static
    {
        $this->withSort = $enabled;

        return $this;
    }

    public function isWithSort(): bool
    {
        return $this->withSort;
    }

    public function modifyQueryUsing(?Closure $callback): static
    {
        if ($callback) {
            $this->modifyQueryCallbacks[] = $callback;
        }

        return $this;
    }

    /** @return array<int, Closure> */
    public function getModifyQueryCallbacks(): array
    {
        return $this->modifyQueryCallbacks;
    }

    public function getTableQuery(): Builder
    {
        $livewire = $this->getLivewire();
        $table = $this->getTable();
        $query = $table->getQuery();

        if (! $this->withFilters && ! $this->withSearch && ! $this->withSort) {
            return $this->applyModifyQueryCallbacks($query);
        }

        if ($this->withSort && method_exists($livewire, 'getFilteredSortedTableQuery')) {
            return $this->applyModifyQueryCallbacks($livewire->getFilteredSortedTableQuery());
        }

        if (($this->withFilters || $this->withSearch) && method_exists($livewire, 'getFilteredTableQuery')) {
            return $this->applyModifyQueryCallbacks($livewire->getFilteredTableQuery());
        }

        return $this->applyModifyQueryCallbacks($query);
    }

    public function getRecords(): Collection
    {
        return $this->getTableQuery()->get();
    }

    protected function applyModifyQueryCallbacks(Builder $query): Builder
    {
        foreach ($this->modifyQueryCallbacks as $callback) {
            $result = $callback($query);

            if ($result instanceof Builder) {
                $query = $result;
            }
        }

        return $query;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->uniqueActionId('header-action');

        $this->applyExportConfigDefaults();

        $this->label(__('filament-action-export::filament-action-export.actions.export'))
            ->modalHeading(__('filament-action-export::filament-action-export.modal.heading'))
            ->icon(config('filament-action-export.icons.action', 'heroicon-o-arrow-down-tray'))
            ->schema(function (): array {
                if ($this->isDirectDownload()) {
                    return [];
                }

                // Create paginator for preview
                $livewire = $this->getLivewire();
                $perPage = $livewire->tableRecordsPerPage === 'all'
                    ? $this->getTableQuery()->count()
                    : $livewire->tableRecordsPerPage;

                $paginator = $this->getTableQuery()->paginate(
                    perPage: (int) $perPage,
                    pageName: 'exportPage'
                );

                $this->paginator($paginator);

                return $this->buildFormSchema($paginator);
            })
            ->action(function (array $data): StreamedResponse {
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

                $records = $this->getRecords();
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

                return $this->getExportModalActions();
            });
    }
}
