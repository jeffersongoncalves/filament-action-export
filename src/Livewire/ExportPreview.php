<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Livewire;

use Illuminate\Support\Collection;
use Livewire\Component;

class ExportPreview extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $records = [];

    /** @var array<string, string> */
    public array $columns = [];

    /** @var array<string, mixed> */
    public array $extraData = [];

    public function mount(Collection $records, array $columns, array $extraData = []): void
    {
        $this->records = $records->toArray();
        $this->columns = $columns;
        $this->extraData = $extraData;
    }

    public function print(): void
    {
        $this->dispatch('print-table');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('filament-action-export::components.table-view');
    }
}
