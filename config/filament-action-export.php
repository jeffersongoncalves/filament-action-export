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
    'use_snappy' => false,
    'icons' => [
        'action' => 'heroicon-o-arrow-down-tray',
        'preview' => 'heroicon-o-eye',
        'export' => 'heroicon-o-arrow-down-tray',
        'print' => 'heroicon-o-printer',
        'cancel' => 'heroicon-o-x-circle',
    ],
];
