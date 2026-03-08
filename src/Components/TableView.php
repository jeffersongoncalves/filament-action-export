<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Components;

use Filament\Schemas\Components\Component;
use Illuminate\Pagination\LengthAwarePaginator;
use JeffersonGoncalves\FilamentExportAction\Components\Concerns\HasUniqueActionId;

class TableView extends Component
{
    use HasUniqueActionId;

    protected ?string $statePath = 'table_view';

    /** @var array<string, string> */
    protected array $filteredColumns = [];

    /** @var array<int, array<string, string>> */
    protected array $rows = [];

    protected ?LengthAwarePaginator $paginator = null;

    protected bool $shouldRefresh = false;

    protected string $printHTML = '';

    protected function setUp(): void
    {
        parent::setUp();

        /** @var view-string $viewName */
        $viewName = 'filament-action-export::components.table-view';
        $this->view($viewName);
    }

    public static function make(string $name = 'table_view'): static
    {
        $static = app(static::class);
        $static->statePath($name);
        $static->configure();

        return $static;
    }

    /** @param array<string, string> $exportColumns */
    public function exportColumns(array $exportColumns): static
    {
        $this->filteredColumns = $exportColumns;

        return $this;
    }

    /** @return array<string, string> */
    public function getExportColumns(): array
    {
        return $this->filteredColumns;
    }

    /** @param array<int, array<string, string>> $rows */
    public function rows(array $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    /** @return array<int, array<string, string>> */
    public function getRows(): array
    {
        return $this->rows;
    }

    public function paginator(?LengthAwarePaginator $paginator): static
    {
        $this->paginator = $paginator;

        return $this;
    }

    public function getPaginator(): ?LengthAwarePaginator
    {
        return $this->paginator;
    }

    public function refresh(bool $condition): static
    {
        $this->shouldRefresh = $condition;

        return $this;
    }

    public function shouldRefresh(): bool
    {
        return $this->shouldRefresh;
    }

    public function printHTML(string $printHTML): static
    {
        $this->printHTML = $printHTML;

        return $this;
    }

    public function getPrintHTML(): string
    {
        return base64_encode($this->printHTML);
    }

    public function getPreviewModalHeading(): string
    {
        return __('filament-action-export::filament-action-export.actions.preview');
    }
}
