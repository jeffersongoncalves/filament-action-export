@php
    $uniqueActionId = $getUniqueActionId();
    $statePath = $getStatePath();
    $shouldRefresh = $shouldRefresh();

    $stateValue = $getState();
    $shouldPrint = $stateValue === 'print-' . $uniqueActionId;

    $printContent = $shouldPrint ? $getPrintHTML() : '';
    $columns = $getExportColumns();
    $rows = $getRows();
    $paginator = $getPaginator();
@endphp

<input id="{{ $statePath }}" type="hidden" {{ $applyStateBindingModifiers('wire:model') }}="{{ $statePath }}">

<x-filament::modal id="preview-modal-{{ $uniqueActionId }}" width="7xl" display-classes="block"
    x-init="
        $wire.$on('open-preview-modal-{{ $uniqueActionId }}', function() {
            triggerInputEvent('{{ $statePath }}', '{{ uniqid() }}');
            isOpen = true;
        });

        $wire.$on('close-preview-modal-{{ $uniqueActionId }}', () => { isOpen = false; });

        if ({{ $shouldRefresh ? 'true' : 'false' }}) {
            $wire.dispatch('close-preview-modal-{{ $uniqueActionId }}');
            triggerInputEvent('{{ $statePath }}', '{{ uniqid() }}');
            $wire.dispatch('open-preview-modal-{{ $uniqueActionId }}');
        }

        if ({{ $shouldPrint ? 'true' : 'false' }}) {
            window.printHTML(`{!! $printContent !!}`, '{{ $statePath }}', '{{ $uniqueActionId }}');
        }
    "
    x-on:keydown.window.escape.capture="isOpen = false"
    :heading="$getPreviewModalHeading()">

    <div class="fi-export-preview-wrapper space-y-4">
        <table class="fi-export-table"
            x-init="$wire.$on('print-table-{{ $uniqueActionId }}', function() {
                triggerInputEvent('{{ $statePath }}', 'print-{{ $uniqueActionId }}')
            })">
            <thead>
                <tr>
                    @foreach ($columns as $name => $label)
                        <th>{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        @foreach (array_keys($columns) as $key)
                            <td>{{ $row[$key] ?? '' }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" class="fi-export-table-empty">
                            {{ __('filament-action-export::filament-action-export.messages.no_records') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($paginator && $paginator->hasPages())
            <div class="fi-export-pagination px-3 py-3">
                {{ $paginator->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

    <x-slot name="footer">
        <div class="flex gap-x-3">
            <x-filament::button
                type="button"
                color="gray"
                icon="heroicon-o-printer"
                x-on:click="triggerInputEvent('{{ $statePath }}', 'print-{{ $uniqueActionId }}')"
            >
                {{ __('filament-action-export::filament-action-export.actions.print') }}
            </x-filament::button>

            <x-filament::button
                type="button"
                color="gray"
                x-on:click="isOpen = false"
            >
                {{ __('filament-action-export::filament-action-export.actions.close') }}
            </x-filament::button>
        </div>
    </x-slot>
</x-filament::modal>
