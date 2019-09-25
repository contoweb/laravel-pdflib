<?php

namespace Contoweb\Pdflib\Tests\Data\Stubs;

use Contoweb\Pdflib\Concerns\WithDraw;
use Contoweb\Pdflib\Writers\PdfWriter;

class MinimalDocument implements WithDraw
{
    public function draw(PdfWriter $writer)
    {
        $writer->newPage();
    }
}