<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Actions\Concerns;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;
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
            ExportFormat::Csv => (new CsvExporter)->delimiter($this->getCsvDelimiter()),
            ExportFormat::Xlsx => new XlsxExporter,
            ExportFormat::Pdf => (new PdfExporter)
                ->driver($this->getPdfDriver())
                ->pdfOptions($this->getPdfOptions())
                ->extraViewData($this->resolveExtraViewData()),
        };
    }

    /**
     * Fill default values into form data when fields are disabled or using direct download.
     */
    public function fillDefaultData(array &$data): void
    {
        $data['format'] ??= $this->getDefaultFormat()->value;
        $data['page_orientation'] ??= $this->getDefaultPageOrientation();
        $data['file_name'] ??= $this->getDefaultFileName();

        if (! isset($data['filter_columns']) && ! $this->isFilterColumnsDisabled()) {
            $data['filter_columns'] = array_keys($this->resolveColumns($this->getTable()));
        }

        if (! isset($data['additional_columns'])) {
            $data['additional_columns'] = [];
        }
    }

    /**
     * Get formatted rows using Filament Column objects for proper state formatting.
     *
     * @return array<int, array<string, string>>
     */
    public function getFormattedRows(Collection $records, ?array $filteredColumnNames = null): array
    {
        $table = $this->getTable();
        $columnObjects = $this->resolveColumnObjects($table);

        if ($filteredColumnNames !== null) {
            $columnObjects = array_intersect_key($columnObjects, array_flip($filteredColumnNames));
        }

        $formatStates = $this->getFormatStates();
        $additionalColumns = $this->getAdditionalColumns();
        $rows = [];

        foreach ($records->values() as $index => $record) {
            $row = [];

            if ($record instanceof Model) {
                foreach ($columnObjects as $name => $column) {
                    $row[$name] = static::getColumnState($table, $column, $record, $index, $formatStates);
                }
            } else {
                $data = (array) $record;
                foreach ($columnObjects as $name => $column) {
                    $value = $data[$name] ?? '';
                    if (isset($formatStates[$name])) {
                        $value = $formatStates[$name]($value, $record);
                    }
                    $row[$name] = (string) $value;
                }
            }

            foreach ($additionalColumns as $column) {
                $row[$column->getName()] = (string) ($column->getDefaultValue() ?? '');
            }

            $rows[] = $row;
        }

        return $rows;
    }

    public function performExport(
        Collection $records,
        array $columns,
        ExportFormat $format,
        ?string $userFileName = null,
        ?string $pageOrientation = null,
        array $userAdditionalColumns = [],
    ): StreamedResponse {
        $exporter = $this->getExporter($format);
        $filename = $this->resolveFileName($userFileName);

        if ($pageOrientation !== null && $exporter instanceof PdfExporter) {
            $exporter->pdfOptions(array_merge(
                $this->getPdfOptions(),
                ['orientation' => $pageOrientation],
            ));
        }

        $writerCallback = match ($format) {
            ExportFormat::Csv, ExportFormat::Xlsx => $this->getModifyExcelWriter(),
            ExportFormat::Pdf => $this->getModifyPdfWriter(),
        };

        if ($writerCallback !== null) {
            $exporter->modifyWriter($writerCallback);
        }

        // Merge user-defined additional columns into headers
        foreach ($userAdditionalColumns as $key => $value) {
            $columns[$key] = $key;
        }

        // Format data using Filament Column objects
        $formattedRecords = collect($this->getFormattedRows($records, array_keys($columns)));

        // Apply user-defined additional column values
        if (! empty($userAdditionalColumns)) {
            $formattedRecords = $formattedRecords->map(function (array $row) use ($userAdditionalColumns): array {
                return array_merge($row, $userAdditionalColumns);
            });
        }

        return $exporter->export($formattedRecords, $columns, $filename);
    }

    public function renderPrintHtml(Collection $records, array $data): string
    {
        $this->fillDefaultData($data);

        if (! $this->isFilterColumnsDisabled() && isset($data['filter_columns'])) {
            $allTableColumns = $this->resolveColumns($this->getTable());
            $columns = array_intersect_key($allTableColumns, array_flip($data['filter_columns']));
        } else {
            $columns = $this->resolveColumns($this->getTable());
        }

        foreach ($this->getAdditionalColumns() as $column) {
            $columns[$column->getName()] = $column->getLabel();
        }

        $formattedRows = $this->getFormattedRows($records, array_keys($columns));

        $userAdditionalColumns = $data['additional_columns'] ?? [];
        if (! empty($userAdditionalColumns)) {
            foreach ($userAdditionalColumns as $key => $value) {
                $columns[$key] = $key;
            }
            $formattedRows = array_map(function (array $row) use ($userAdditionalColumns): array {
                return array_merge($row, $userAdditionalColumns);
            }, $formattedRows);
        }

        return view('filament-action-export::print', [
            'columns' => $columns,
            'records' => $formattedRows,
            'title' => $this->getTable()->getHeading() ?? 'Export',
        ])->render();
    }

    /** @return array<\Filament\Schemas\Components\Component> */
    public function buildFormSchema(?Collection $previewRecords = null): array
    {
        // Auto-set file name prefix from table heading
        if ($this->isFileNamePrefixEnabled() && ! $this->hasCustomFileNamePrefix()) {
            $heading = $this->getTable()->getHeading();
            if ($heading) {
                $this->fileNamePrefix($heading);
            }
        }

        $schema = [];

        // File name input
        if ($this->isFileNameEnabled()) {
            $schema[] = TextInput::make('file_name')
                ->label(__('filament-action-export::filament-action-export.fields.file_name'))
                ->default($this->getDefaultFileName())
                ->rule('regex:/[a-zA-Z0-9\s_\\.\-\(\):]/')
                ->required();
        }

        // Format selector
        if (! $this->isFormatsDisabled()) {
            $formatOptions = collect($this->getEnabledFormats())
                ->mapWithKeys(fn (ExportFormat $format) => [$format->value => $format->label()])
                ->all();

            $schema[] = Select::make('format')
                ->label(__('filament-action-export::filament-action-export.fields.format'))
                ->options($formatOptions)
                ->default($this->getDefaultFormat()->value)
                ->required()
                ->live();
        }

        // Page orientation (visible only for PDF)
        $schema[] = Select::make('page_orientation')
            ->label(__('filament-action-export::filament-action-export.fields.page_orientation'))
            ->options([
                'portrait' => __('filament-action-export::filament-action-export.fields.orientation_portrait'),
                'landscape' => __('filament-action-export::filament-action-export.fields.orientation_landscape'),
            ])
            ->default($this->getDefaultPageOrientation())
            ->visible(fn (Get $get): bool => $this->isFormatsDisabled()
                ? $this->getDefaultFormat() === ExportFormat::Pdf
                : $get('format') === ExportFormat::Pdf->value);

        // Column filter (CheckboxList)
        if (! $this->isFilterColumnsDisabled()) {
            $allColumns = $this->resolveColumns($this->getTable());

            $schema[] = CheckboxList::make('filter_columns')
                ->label(__('filament-action-export::filament-action-export.fields.filter_columns'))
                ->options($allColumns)
                ->default(array_keys($allColumns))
                ->columns(4)
                ->live();
        }

        // Additional columns (user-definable at export time)
        if (! $this->isAdditionalColumnsDisabled()) {
            // Predefined additional columns with defaults
            foreach ($this->getAdditionalColumns() as $column) {
                $schema[] = TextInput::make("additional_{$column->getName()}")
                    ->label($column->getLabel())
                    ->default($column->getDefaultValue());
            }

            // User-definable additional columns via KeyValue
            $schema[] = KeyValue::make('additional_columns')
                ->label(__('filament-action-export::filament-action-export.fields.additional_columns'))
                ->keyLabel(__('filament-action-export::filament-action-export.fields.additional_column_title'))
                ->valueLabel(__('filament-action-export::filament-action-export.fields.additional_column_default_value'))
                ->addActionLabel(__('filament-action-export::filament-action-export.fields.additional_column_add'));
        }

        // Preview section
        if ($this->isPreviewEnabled() && $previewRecords !== null && $previewRecords->isNotEmpty()) {
            $previewRows = $this->getFormattedRows($previewRecords->take(10));
            $previewColumns = $this->resolveColumns($this->getTable());

            foreach ($this->getAdditionalColumns() as $column) {
                $previewColumns[$column->getName()] = $column->getLabel();
            }

            /** @var view-string $previewView */
            $previewView = 'filament-action-export::components.preview-section';

            $schema[] = ViewField::make('preview')
                ->label(__('filament-action-export::filament-action-export.actions.preview'))
                ->view($previewView)
                ->viewData([
                    'rows' => $previewRows,
                    'columns' => $previewColumns,
                ])
                ->dehydrated(false);
        }

        return $schema;
    }
}
