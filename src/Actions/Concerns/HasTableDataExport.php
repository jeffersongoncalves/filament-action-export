<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions\Concerns;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
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
            ExportFormat::Csv => (new CsvExporter)
                ->delimiter($this->getCsvDelimiter()),
            ExportFormat::Xlsx => new XlsxExporter,
            ExportFormat::Pdf => (new PdfExporter)
                ->driver($this->getPdfDriver())
                ->pdfOptions($this->getPdfOptions())
                ->extraViewData($this->resolveExtraViewData()),
        };
    }

    public function performExport(
        Collection $records,
        array $columns,
        ExportFormat $format,
        ?string $userFileName = null,
        ?string $pageOrientation = null,
    ): StreamedResponse {
        $exporter = $this->getExporter($format);
        $filename = $this->resolveFileName($userFileName);

        // Apply page orientation if provided
        if ($pageOrientation !== null && $exporter instanceof PdfExporter) {
            $exporter->pdfOptions(array_merge(
                $this->getPdfOptions(),
                ['orientation' => $pageOrientation],
            ));
        }

        // Apply writer callbacks
        $writerCallback = match ($format) {
            ExportFormat::Csv, ExportFormat::Xlsx => $this->getModifyExcelWriter(),
            ExportFormat::Pdf => $this->getModifyPdfWriter(),
        };

        if ($writerCallback !== null) {
            $exporter->modifyWriter($writerCallback);
        }

        // Apply format states to records
        $formatStates = $this->getFormatStates();

        // Inject additional column default values into records
        $additionalColumns = $this->getAdditionalColumns();

        if (! empty($additionalColumns) || ! empty($formatStates)) {
            $records = $records->map(function ($record) use ($additionalColumns, $formatStates) {
                $data = $record instanceof \Illuminate\Database\Eloquent\Model
                    ? $record->toArray()
                    : (array) $record;

                foreach ($additionalColumns as $column) {
                    $data[$column->getName()] = $column->getDefaultValue();
                }

                foreach ($formatStates as $key => $formatter) {
                    if (array_key_exists($key, $data)) {
                        $data[$key] = $formatter($data[$key], $record);
                    }
                }

                return $data;
            });
        }

        return $exporter->export($records, $columns, $filename);
    }

    /** @return array<\Filament\Forms\Components\Select|\Filament\Forms\Components\CheckboxList|\Filament\Forms\Components\TextInput> */
    public function buildFormSchema(): array
    {
        $schema = [];

        if ($this->isFileNameEnabled()) {
            $schema[] = TextInput::make('file_name')
                ->label(__('filament-action-export::filament-action-export.fields.file_name'))
                ->default($this->getDefaultFileName())
                ->required();
        }

        $formatOptions = collect($this->getEnabledFormats())
            ->mapWithKeys(fn (ExportFormat $format) => [$format->value => $format->label()])
            ->all();

        $schema[] = Select::make('format')
            ->label(__('filament-action-export::filament-action-export.fields.format'))
            ->options($formatOptions)
            ->default($this->getDefaultFormat()->value)
            ->required()
            ->live();

        $schema[] = Select::make('page_orientation')
            ->label(__('filament-action-export::filament-action-export.fields.page_orientation'))
            ->options([
                'portrait' => __('filament-action-export::filament-action-export.fields.orientation_portrait'),
                'landscape' => __('filament-action-export::filament-action-export.fields.orientation_landscape'),
            ])
            ->default(config('filament-action-export.pdf_options.orientation', 'portrait'))
            ->visible(fn (Get $get): bool => $get('format') === ExportFormat::Pdf->value);

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
