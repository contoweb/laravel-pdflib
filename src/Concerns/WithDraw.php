<?php

namespace Contoweb\Pdflib\Concerns;

use Contoweb\Pdflib\Writers\PdfWriter;

interface WithDraw
{
    /**
     * Draw the document.
     *
     * @param PdfWriter $writer
     */
    public function draw(PdfWriter $writer);

    /**
     * Define the available fonts.
     *
     * @return array
     */
    public function fonts(): array;
}