<?php

return [
    'pdf_driver' => env('FILAMENT_EXPORT_PDF_DRIVER', 'dompdf'),
    'default_format' => env('FILAMENT_EXPORT_DEFAULT_FORMAT', 'xlsx'),
    'formats' => ['csv', 'xlsx', 'pdf'],
    'csv_delimiter' => ',',
    'chunk_size' => 1000,
    'pdf_options' => [
        'paper' => 'a4',
        'orientation' => 'portrait',
    ],
    'preview_enabled' => true,
    'print_enabled' => true,
];
