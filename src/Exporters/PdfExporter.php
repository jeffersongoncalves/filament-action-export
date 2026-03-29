<?php

declare(strict_types=1);

namespace JeffersonGoncalves\FilamentExportAction\Exporters;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Closure;
use Illuminate\Support\Collection;
use JeffersonGoncalves\FilamentExportAction\Exporters\Contracts\Exporter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfExporter implements Exporter
{
    protected string $driver = 'dompdf';

    /** @var array<string, mixed> */
    protected array $pdfOptions = [];

    /** @var array<string, mixed> */
    protected array $extraViewData = [];

    protected ?Closure $writerCallback = null;

    public function driver(string $driver): static
    {
        $this->driver = $driver;

        return $this;
    }

    /** @param array<string, mixed> $options */
    public function pdfOptions(array $options): static
    {
        $this->pdfOptions = $options;

        return $this;
    }

    /** @param array<string, mixed> $data */
    public function extraViewData(array $data): static
    {
        $this->extraViewData = $data;

        return $this;
    }

    public function modifyWriter(Closure $callback): static
    {
        $this->writerCallback = $callback;

        return $this;
    }

    /** @param array<string, string> $columns */
    public function export(Collection $records, array $columns, string $filename): StreamedResponse
    {
        $html = view('filament-action-export::pdf', array_merge(
            ['records' => $records, 'columns' => $columns, 'title' => $filename],
            $this->extraViewData,
        ))->render();

        $paper = $this->pdfOptions['paper'] ?? config('filament-action-export.pdf_options.paper', 'a4');
        $orientation = $this->pdfOptions['orientation'] ?? config('filament-action-export.pdf_options.orientation', 'portrait');

        if ($this->driver === 'snappy' && class_exists(SnappyPdf::class)) {
            $pdf = SnappyPdf::loadHTML($html)
                ->setPaper($paper)
                ->setOrientation($orientation);
        } else {
            $pdf = Pdf::loadHTML($html)
                ->setPaper($paper, $orientation);
        }

        if ($this->writerCallback !== null) {
            ($this->writerCallback)($pdf);
        }

        $content = $pdf->output();

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, "{$filename}.pdf", [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
