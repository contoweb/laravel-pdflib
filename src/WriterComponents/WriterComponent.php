<?php


namespace Contoweb\Pdflib\WriterComponents;


use Contoweb\Pdflib\Writers\PdfWriter;

interface WriterComponent
{
    /**
     * Component constructor.
     * @param  PdfWriter  $writer
     */
    public function __construct(PdfWriter $writer);
}