<?php

namespace Contoweb\Pdflib\Tests\Data\Stubs;

use Contoweb\Pdflib\Concerns\FromTemplate;
use Contoweb\Pdflib\Concerns\WithDraw;
use Contoweb\Pdflib\Concerns\WithPreview;
use Contoweb\Pdflib\Writers\PdfWriter;

class TemplatePreviewDocument implements FromTemplate, WithDraw, WithPreview
{
    public function template(): string
    {
        return 'template.pdf';
    }

    public function previewTemplate(): string
    {
        return 'previewTemplate.pdf';
    }

    public function offset(): array
    {
        return [
            'x' => 20,
            'y' => 20,
        ];
    }

    public function fonts(): array
    {
        return ['OpenSans-Regular'];
    }

    public function draw(PdfWriter $writer)
    {
        $writer->newPage();
    }
}
