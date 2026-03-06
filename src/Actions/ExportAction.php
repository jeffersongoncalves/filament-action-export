<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions;

use Filament\Actions\Action;
use Illuminate\Support\Collection;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasAdditionalColumns;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasExportColumns;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasExportFormats;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasExtraViewData;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasPdfDriver;
use JeffersonGoncalves\FilamentExportAction\Actions\Concerns\HasTableDataExport;
use JeffersonGoncalves\FilamentExportAction\Enums\ExportFormat;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportAction extends Action
{
    use HasAdditionalColumns;
    use HasExportColumns;
    use HasExportFormats;
    use HasExtraViewData;
    use HasPdfDriver;
    use HasTableDataExport;

    protected bool $withFilters = false;

    protected bool $withSearch = false;

    protected bool $withSort = false;

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

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-action-export::filament-action-export.actions.export'))
            ->icon('heroicon-o-arrow-down-tray')
            ->form(fn () => $this->buildFormSchema())
            ->action(function (array $data, ?Collection $records = null): StreamedResponse {
                $format = ExportFormat::from($data['format']);

                if ($this->canSelectColumns && isset($data['columns'])) {
                    $allTableColumns = $this->resolveColumns($this->getTable());
                    $columns = array_intersect_key($allTableColumns, array_flip($data['columns']));
                } else {
                    $columns = $this->resolveColumns($this->getTable());
                }

                $allColumns = array_merge($columns, $this->getAdditionalColumnsAsArray());

                if ($records === null || $records->isEmpty()) {
                    $records = $this->getTable()->getRecords();
                }

                return $this->performExport($records, $allColumns, $format);
            });
    }
}
