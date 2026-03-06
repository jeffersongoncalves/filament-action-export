<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions;

use Closure;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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

class FilamentExportHeaderAction extends Action
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

    public function withSearch(bool $enabled = true): static
    {
        $this->withSearch = $enabled;

        return $this;
    }

    public function withSort(bool $enabled = true): static
    {
        $this->withSort = $enabled;

        return $this;
    }

    public function modifyQueryUsing(?Closure $callback): static
    {
        if ($callback) {
            $this->modifyQueryCallbacks[] = $callback;
        }

        return $this;
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

        $this->label(__('filament-action-export::filament-action-export.actions.export'))
            ->icon(config('filament-action-export.icons.action', 'heroicon-o-arrow-down-tray'))
            ->schema(fn () => $this->isDirectDownload() ? [] : $this->buildFormSchema())
            ->action(function (array $data): StreamedResponse {
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
                $records = $this->getRecords();

                $userFileName = $data['file_name'] ?? null;
                $pageOrientation = $data['page_orientation'] ?? null;

                return $this->performExport($records, $allColumns, $format, $userFileName, $pageOrientation);
            });
    }
}
