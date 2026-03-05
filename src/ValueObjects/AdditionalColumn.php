<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\ValueObjects;

use Closure;
use Illuminate\Support\Str;

class AdditionalColumn
{
    protected string $name;

    protected ?string $label = null;

    /** @var string|Closure|null */
    protected $defaultValue = null;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /** @param string|Closure $value */
    public function defaultValue($value): static
    {
        $this->defaultValue = $value;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label ?? Str::title(str_replace('_', ' ', $this->name));
    }

    public function getDefaultValue(): ?string
    {
        if ($this->defaultValue instanceof Closure) {
            return ($this->defaultValue)();
        }

        return $this->defaultValue;
    }
}
