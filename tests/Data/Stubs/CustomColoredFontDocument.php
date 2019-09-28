<?php

namespace Contoweb\Pdflib\Tests\Data\Stubs;

use Contoweb\Pdflib\Concerns\FromTemplate;
use Contoweb\Pdflib\Concerns\WithColors;
use Contoweb\Pdflib\Concerns\WithDraw;
use Contoweb\Pdflib\Concerns\WithPreview;
use Contoweb\Pdflib\Writers\PdfWriter;

class CustomColoredFontDocument implements WithDraw, WithColors
{
    public function colors(): array
    {
        return [
            'custom' => ['cmyk', 60, 40, 80, 0]
        ];
    }

    public function fonts(): array
    {
        return ['OpenSans-Regular'];
    }

    public function draw(PdfWriter $writer)
    {
        $writer->newPage();
        $writer->useFont('OpenSans-Regular', 12, 'custom')->writeTextLine('Text in Arial with custom color.');
    }
}