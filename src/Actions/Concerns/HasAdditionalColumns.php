<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions\Concerns;

use JeffersonGoncalves\FilamentExportAction\ValueObjects\AdditionalColumn;

trait HasAdditionalColumns
{
    /** @var array<AdditionalColumn> */
    protected array $additionalColumns = [];

    protected bool $additionalColumnsDisabled = false;

    /** @param array<AdditionalColumn> $columns */
    public function additionalColumns(array $columns): static
    {
        $this->additionalColumns = $columns;

        return $this;
    }

    /** @return array<AdditionalColumn> */
    public function getAdditionalColumns(): array
    {
        return $this->additionalColumns;
    }

    public function disableAdditionalColumns(bool $condition = true): static
    {
        $this->additionalColumnsDisabled = $condition;

        return $this;
    }

    public function isAdditionalColumnsDisabled(): bool
    {
        return $this->additionalColumnsDisabled;
    }

    /** @return array<string, string> name => label */
    public function getAdditionalColumnsAsArray(): array
    {
        $result = [];

        foreach ($this->additionalColumns as $column) {
            $result[$column->getName()] = $column->getLabel();
        }

        return $result;
    }
}
