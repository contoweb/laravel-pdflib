<?php

namespace Contoweb\Pdflib\Tests\Data\Stubs;

use Contoweb\Pdflib\Concerns\WithDraw;
use Contoweb\Pdflib\Concerns\Writer;

class MinimalDocument implements WithDraw
{
    public function draw(Writer $writer)
    {
        $writer->newPage();
    }
}