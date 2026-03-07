<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions\Concerns;

trait CanRefreshTable
{
    public function shouldRefreshTableView(): bool
    {
        if (request()->has('components')) {
            /** @var array<int, array{calls: array<int, array{method: string}>, updates: array<string, mixed>}> $components */
            $components = request('components');

            foreach ($components as $component) {
                $callMethods = array_unique(array_column($component['calls'], 'method'));

                if (in_array('gotoPage', $callMethods)
                    || in_array('previousPage', $callMethods)
                    || in_array('nextPage', $callMethods)) {
                    return true;
                }

                if (array_key_exists('tableRecordsPerPage', $component['updates'])) {
                    return true;
                }
            }
        }

        return false;
    }
}
