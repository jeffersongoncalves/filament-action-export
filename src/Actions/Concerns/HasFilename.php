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

    protected bool $fileNamePrefixEnabled = true;

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

    public function hasCustomFileNamePrefix(): bool
    {
        return $this->fileNamePrefix !== null;
    }

    public function disableFileName(bool $condition = true): static
    {
        $this->fileNameEnabled = ! $condition;

        return $this;
    }

    public function disableFileNamePrefix(bool $condition = true): static
    {
        $this->fileNamePrefixEnabled = ! $condition;

        return $this;
    }

    public function isFileNamePrefixEnabled(): bool
    {
        return $this->fileNamePrefixEnabled;
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

        // Auto-set prefix from table heading if not manually set
        if ($this->fileNamePrefixEnabled && $this->fileNamePrefix === null) {
            try {
                $heading = $this->getTable()->getHeading();
                if ($heading) {
                    $this->fileNamePrefix = $heading;
                }
            } catch (\Throwable) {
                // Table may not be available
            }
        }

        $parts = [];

        if ($this->fileNamePrefixEnabled && $this->fileNamePrefix !== null) {
            $parts[] = $this->fileNamePrefix;
        }

        if ($userInput !== null && $userInput !== '') {
            $parts[] = $userInput;
        } elseif ($this->fileName !== null) {
            $parts[] = $this->fileName;
        } else {
            $parts[] = now()->translatedFormat($this->timeFormat);
        }

        return implode('-', $parts);
    }

    public function getDefaultFileName(): string
    {
        return $this->fileName ?? now()->translatedFormat($this->timeFormat);
    }
}
