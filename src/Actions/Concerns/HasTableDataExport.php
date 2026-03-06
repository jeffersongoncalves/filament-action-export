<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions\Concerns;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Collection;
use JeffersonGoncalves\FilamentExportAction\Enums\ExportFormat;
use JeffersonGoncalves\FilamentExportAction\Exporters\Contracts\Exporter;
use JeffersonGoncalves\FilamentExportAction\Exporters\CsvExporter;
use JeffersonGoncalves\FilamentExportAction\Exporters\PdfExporter;
use JeffersonGoncalves\FilamentExportAction\Exporters\XlsxExporter;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait HasTableDataExport
{
    public function getExporter(ExportFormat $format): Exporter
    {
        return match ($format) {
            ExportFormat::Csv => new CsvExporter,
            ExportFormat::Xlsx => new XlsxExporter,
            ExportFormat::Pdf => (new PdfExporter)
                ->driver($this->getPdfDriver())
                ->pdfOptions($this->getPdfOptions())
                ->extraViewData($this->resolveExtraViewData()),
        };
    }

    public function buildFilename(ExportFormat $format): string
    {
        return 'export-'.now()->format('Y-m-d');
    }

    public function performExport(Collection $records, array $columns, ExportFormat $format): StreamedResponse
    {
        $exporter = $this->getExporter($format);
        $filename = $this->buildFilename($format);

        // Inject additional column default values into records
        $additionalColumns = $this->getAdditionalColumns();
        if (! empty($additionalColumns)) {
            $records = $records->map(function ($record) use ($additionalColumns) {
                $data = $record instanceof \Illuminate\Database\Eloquent\Model
                    ? $record->toArray()
                    : (array) $record;

                foreach ($additionalColumns as $column) {
                    $data[$column->getName()] = $column->getDefaultValue();
                }

                return $data;
            });
        }

        return $exporter->export($records, $columns, $filename);
    }

    /** @return array<\Filament\Forms\Components\Component> */
    public function buildFormSchema(): array
    {
        $schema = [];

        $formatOptions = collect($this->getEnabledFormats())
            ->mapWithKeys(fn (ExportFormat $format) => [$format->value => $format->label()])
            ->all();

        $schema[] = Select::make('format')
            ->label(__('filament-action-export::filament-action-export.fields.format'))
            ->options($formatOptions)
            ->default($this->getDefaultFormat()->value)
            ->required();

        if ($this->canSelectColumns) {
            $table = $this->getTable();
            $allColumns = $this->resolveColumns($table);

            $schema[] = CheckboxList::make('columns')
                ->label(__('filament-action-export::filament-action-export.fields.columns'))
                ->options($allColumns)
                ->default(array_keys($allColumns))
                ->columns(2);
        }

        foreach ($this->getAdditionalColumns() as $column) {
            $schema[] = TextInput::make("additional_{$column->getName()}")
                ->label($column->getLabel())
                ->default($column->getDefaultValue());
        }

        return $schema;
    }
}
