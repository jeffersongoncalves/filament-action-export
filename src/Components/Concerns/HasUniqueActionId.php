<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Components\Concerns;

trait HasUniqueActionId
{
    protected string $uniqueActionId = '';

    public function uniqueActionId(string $uniqueActionId): static
    {
        $this->uniqueActionId = $uniqueActionId;

        return $this;
    }

    public function getUniqueActionId(): string
    {
        return $this->uniqueActionId;
    }
}
