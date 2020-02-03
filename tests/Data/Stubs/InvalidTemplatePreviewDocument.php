<?php

namespace Contoweb\Pdflib\Tests\Data\Stubs;

use Contoweb\Pdflib\Concerns\FromTemplate;
use Contoweb\Pdflib\Concerns\WithDraw;
use Contoweb\Pdflib\Concerns\WithPreview;
use Contoweb\Pdflib\Writers\PdfWriter;

class InvalidTemplatePreviewDocument implements FromTemplate, WithDraw, WithPreview
{
    /**
     * @var string Fake key name for X offset.
     */
    protected $xOffsetKey;

    /**
     * @var string Fake key name for Y offset.
     */
    protected $yOffsetKey;

    /**
     * InvalidTemplatePreviewDocument constructor.
     * @param string $xOffsetKey
     * @param string $yOffsetKey
     */
    public function __construct($xOffsetKey, $yOffsetKey)
    {
        $this->xOffsetKey = $xOffsetKey;
        $this->yOffsetKey = $yOffsetKey;
    }

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
            $this->xOffsetKey => 20,
            $this->yOffsetKey => 20,
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
