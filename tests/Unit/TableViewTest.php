<?php

declare(strict_types=1);

use JeffersonGoncalves\FilamentExportAction\Components\TableView;

it('makes a table view with the given state path', function () {
    expect(TableView::make()->getStatePath(isAbsolute: false))->toBe('table_view')
        ->and(TableView::make('custom')->getStatePath(isAbsolute: false))->toBe('custom');
});
