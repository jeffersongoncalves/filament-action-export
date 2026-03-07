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
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasPageOrientation;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasPdfDriver;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasPreview;
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
    use HasPageOrientation;
    use HasPdfDriver;
    use HasPreview;
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

        if ($this->withSort) {
            return $this->applyModifyQueryCallbacks($livewire->getFilteredSortedTableQuery());
        }

        if ($this->withFilters || $this->withSearch) {
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
            ->form(function (): array {
                if ($this->isDirectDownload()) {
                    return [];
                }

                $previewRecords = $this->isPreviewEnabled()
                    ? collect($this->getTableQuery()->limit(10)->get())
                    : null;

                return $this->buildFormSchema($previewRecords);
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
            ->extraModalFooterActions(function (): array {
                if (! $this->isPrintEnabled() || $this->isDirectDownload()) {
                    return [];
                }

                $parentAction = $this;

                return [
                    Action::make('print')
                        ->label(__('filament-action-export::filament-action-export.actions.print'))
                        ->icon(config('filament-action-export.icons.print', 'heroicon-o-printer'))
                        ->color('gray')
                        ->action(function (array $data) use ($parentAction): void {
                            $records = $parentAction->getRecords();
                            $html = $parentAction->renderPrintHtml($records, $data);
                            $parentAction->getLivewire()->js(
                                '(() => { let w=window.open("","_blank");w.document.write('.json_encode($html).');w.document.close(); })()'
                            );
                        }),
                ];
            });
    }
}
