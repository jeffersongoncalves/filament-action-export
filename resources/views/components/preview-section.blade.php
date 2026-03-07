<div class="fi-export-preview">
    <div class="fi-export-table-wrapper" style="max-height: 300px; overflow-y: auto; border: 1px solid var(--gray-200); border-radius: 0.5rem;">
        <table class="fi-export-table" style="width: 100%; border-collapse: collapse; font-size: 0.8125rem;">
            <thead>
                <tr>
                    @foreach ($columns as $label)
                        <th style="position: sticky; top: 0; background-color: var(--gray-50, #f9fafb); border-bottom: 1px solid var(--gray-200, #e5e7eb); padding: 0.5rem 0.75rem; text-align: left; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; white-space: nowrap;">
                            {{ $label }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        @foreach (array_keys($columns) as $key)
                            <td style="border-bottom: 1px solid var(--gray-100, #f3f4f6); padding: 0.375rem 0.75rem; white-space: nowrap;">
                                {{ $row[$key] ?? '' }}
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" style="text-align: center; padding: 1rem; color: var(--gray-500, #6b7280);">
                            {{ __('filament-action-export::filament-action-export.messages.no_records') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if (count($rows) >= 10)
        <p style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--gray-500, #6b7280); text-align: center;">
            {{ __('filament-action-export::filament-action-export.messages.preview_limited') }}
        </p>
    @endif
</div>
