<?php

use Contoweb\Pdflib\Writers\PdflibPdfWriter;

return [
    /*
    |--------------------------------------------------------------------------
    | PDFLib license
    |--------------------------------------------------------------------------
    |
    | Make sure you have a valid PDFLib license for your PHP version.
    | If you update your PHP version, you have to make a license update!
    |
    | If no license is provided, the generated PDFs are watermarked.
    |
    */
    'license' => '',

    /*
    |--------------------------------------------------------------------------
    | Measurement
    |--------------------------------------------------------------------------
    |
    | In which unit the package should position your elements.
    | You can choose between "mm" or "pt"
    |
    */
    'measurement' => [
        'unit' => 'mm',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fonts
    |--------------------------------------------------------------------------
    |
    | Define fonts location.
    | Use OTF fonts for best result.
    |
    */
    'fonts' => [
        'disk' => 'local',
        'path' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Templates
    |--------------------------------------------------------------------------
    |
    | Define the location of your PDF templates.
    |
    */
    'templates' => [
        'disk' => 'local',
        'path' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Exports
    |--------------------------------------------------------------------------
    |
    | Define the location of your generated PDFs.
    |
    */
    'exports' => [
        'disk' => 'local',
        'path' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF writer
    |--------------------------------------------------------------------------
    |
    | Define the writer class. It must implement the PdfWriter interface.
    |
    */
    'writer' => PdflibPdfWriter::class,

    /*
    |--------------------------------------------------------------------------
    | PDF creator
    |--------------------------------------------------------------------------
    |
    | Define the creator of the PDF.
    |
    */
    'creator' => env('PDFLIB_CREATOR', 'Laravel'),
];
