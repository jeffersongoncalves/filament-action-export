<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions\Concerns;

use Closure;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Concerns\CanFormatState;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

trait HasExportColumns
{
    /** @var array<string>|null */
    protected ?array $exportColumns = null;

    /** @var array<string> */
    protected array $excludedColumns = [];

    /** @var array<Column> */
    protected array $extraColumns = [];

    protected bool $filterColumnsDisabled = false;

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

    /** @param array<Column> $columns */
    public function withColumns(array $columns): static
    {
        $this->extraColumns = $columns;

        return $this;
    }

    /** @return array<Column> */
    public function getWithColumns(): array
    {
        return $this->extraColumns;
    }

    public function disableFilterColumns(bool $condition = true): static
    {
        $this->filterColumnsDisabled = $condition;

        return $this;
    }

    public function isFilterColumnsDisabled(): bool
    {
        return $this->filterColumnsDisabled;
    }

    public function withHiddenColumns(bool $enabled = true): static
    {
        $this->withHiddenColumns = $enabled;

        return $this;
    }

    public function shouldShowHiddenColumns(): bool
    {
        return $this->withHiddenColumns;
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
     * Get Column objects from the table.
     *
     * @return array<string, Column>
     */
    public function resolveColumnObjects(Table $table): array
    {
        if ($this->disableTableColumns) {
            $columns = [];
        } else {
            $columns = $this->withHiddenColumns
                ? $table->getColumns()
                : $table->getVisibleColumns();
        }

        if ($this->exportColumns !== null) {
            $filtered = [];
            foreach ($this->exportColumns as $name) {
                if (isset($columns[$name])) {
                    $filtered[$name] = $columns[$name];
                }
            }
            $columns = $filtered;
        }

        foreach ($this->extraColumns as $column) {
            $columns[$column->getName()] = $column;
        }

        foreach ($this->excludedColumns as $excluded) {
            unset($columns[$excluded]);
        }

        return $columns;
    }

    /**
     * Get name => label mapping from Column objects.
     *
     * @return array<string, string>
     */
    public function resolveColumns(Table $table): array
    {
        $result = [];

        foreach ($this->resolveColumnObjects($table) as $name => $column) {
            $label = $column->getLabel();
            $result[$name] = $label instanceof HtmlString
                ? strip_tags((string) $label)
                : ($label ?: $name);
        }

        return $result;
    }

    /**
     * Get formatted state for a column/record using Filament's Column formatting.
     */
    public static function getColumnState(Table $table, Column $column, Model $record, int $index, array $formatStates = []): ?string
    {
        $column->rowLoop((object) [
            'index' => $index,
            'iteration' => $index + 1,
        ]);

        $column->record($record);
        $column->table($table);

        if (array_key_exists($column->getName(), $formatStates) && $formatStates[$column->getName()] instanceof Closure) {
            $closure = $formatStates[$column->getName()];
            $dependencies = [];

            foreach ((new \ReflectionFunction($closure))->getParameters() as $parameter) {
                $dependencies[] = match ($parameter->getName()) {
                    'table' => $table,
                    'column' => $column,
                    'record' => $record,
                    'index' => $index,
                    default => null,
                };
            }

            return $closure(...$dependencies);
        }

        $state = $column->getState();

        if (in_array(CanFormatState::class, class_uses_recursive($column::class))) {
            /** @phpstan-ignore-next-line */
            $state = $column->formatState($state);
        }

        if (is_array($state)) {
            $state = implode(', ', $state);
        } elseif ($column instanceof ImageColumn) {
            $state = $column->getImageUrl();
        } elseif ($column instanceof ViewColumn) {
            $rendered = $column->render();
            $html = $rendered instanceof HtmlString ? $rendered->toHtml() : $rendered->render();
            $state = trim(preg_replace('/\s+/', ' ', strip_tags($html)));
        }

        return (string) ($state ?? '');
    }
}
