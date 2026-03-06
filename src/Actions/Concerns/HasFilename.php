<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions\Concerns;

use Closure;

trait HasFilename
{
    protected ?string $fileName = null;

    protected ?string $fileNamePrefix = null;

    protected bool $fileNameEnabled = true;

    protected string $timeFormat = 'Y-m-d_H-i';

    protected ?Closure $fileNameUsing = null;

    public function fileName(string $name): static
    {
        $this->fileName = $name;

        return $this;
    }

    public function fileNamePrefix(string $prefix): static
    {
        $this->fileNamePrefix = $prefix;

        return $this;
    }

    public function disableFileName(bool $condition = true): static
    {
        $this->fileNameEnabled = ! $condition;

        return $this;
    }

    public function timeFormat(string $format): static
    {
        $this->timeFormat = $format;

        return $this;
    }

    public function fileNameUsing(Closure $callback): static
    {
        $this->fileNameUsing = $callback;

        return $this;
    }

    public function isFileNameEnabled(): bool
    {
        return $this->fileNameEnabled;
    }

    public function getTimeFormat(): string
    {
        return $this->timeFormat;
    }

    public function resolveFileName(?string $userInput = null): string
    {
        if ($this->fileNameUsing !== null) {
            return ($this->fileNameUsing)($this);
        }

        $parts = [];

        if ($this->fileNamePrefix !== null) {
            $parts[] = $this->fileNamePrefix;
        }

        if ($userInput !== null && $userInput !== '') {
            $parts[] = $userInput;
        } elseif ($this->fileName !== null) {
            $parts[] = $this->fileName;
        } else {
            $parts[] = 'export';
        }

        $parts[] = now()->format($this->timeFormat);

        return implode('-', $parts);
    }

    public function getDefaultFileName(): string
    {
        return $this->fileName ?? 'export';
    }
}
