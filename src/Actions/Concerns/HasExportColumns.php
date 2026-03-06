<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions\Concerns;

use Filament\Tables\Table;

trait HasExportColumns
{
    /** @var array<string>|null */
    protected ?array $exportColumns = null;

    /** @var array<string> */
    protected array $excludedColumns = [];

    protected bool $canSelectColumns = false;

    protected bool $withHiddenColumns = false;

    protected bool $disableTableColumns = false;

    /** @param array<string> $columns */
    public function columns(array $columns): static
    {
        $this->exportColumns = $columns;

        return $this;
    }

    /** @param array<string> $columns */
    public function excludeColumns(array $columns): static
    {
        $this->excludedColumns = $columns;

        return $this;
    }

    public function userCanSelectColumns(bool $enabled = true): static
    {
        $this->canSelectColumns = $enabled;

        return $this;
    }

    public function withHiddenColumns(bool $enabled = true): static
    {
        $this->withHiddenColumns = $enabled;

        return $this;
    }

    public function disableTableColumns(bool $condition = true): static
    {
        $this->disableTableColumns = $condition;

        return $this;
    }

    public function isTableColumnsDisabled(): bool
    {
        return $this->disableTableColumns;
    }

    /**
     * @return array<string, string> key = field name, value = column label
     */
    public function resolveColumns(Table $table): array
    {
        if ($this->disableTableColumns) {
            return [];
        }

        if ($this->exportColumns !== null) {
            $tableColumns = $table->getColumns();
            $columns = [];

            foreach ($this->exportColumns as $columnName) {
                if (isset($tableColumns[$columnName])) {
                    $columns[$columnName] = $tableColumns[$columnName]->getLabel() ?: $columnName;
                } else {
                    $columns[$columnName] = $columnName;
                }
            }
        } else {
            $columns = [];
            $tableColumns = $this->withHiddenColumns
                ? $table->getColumns()
                : $table->getVisibleColumns();

            foreach ($tableColumns as $column) {
                $name = $column->getName();
                $columns[$name] = $column->getLabel() ?: $name;
            }
        }

        foreach ($this->excludedColumns as $excluded) {
            unset($columns[$excluded]);
        }

        return $columns;
    }
}
