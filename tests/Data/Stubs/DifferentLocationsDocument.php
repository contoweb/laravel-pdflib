<?php

namespace Contoweb\Pdflib\Tests\Data\Stubs;

use Contoweb\Pdflib\Concerns\DifferentExportLocation;
use Contoweb\Pdflib\Concerns\DifferentFontsLocation;
use Contoweb\Pdflib\Concerns\DifferentTemplateLocation;
use Contoweb\Pdflib\Concerns\FromTemplate;
use Contoweb\Pdflib\Concerns\WithColors;
use Contoweb\Pdflib\Concerns\WithDraw;
use Contoweb\Pdflib\Writers\PdfWriter;

class DifferentLocationsDocument implements
    WithDraw,
    FromTemplate,
    DifferentFontsLocation,
    DifferentTemplateLocation,
    DifferentExportLocation
{
    /**
     * @inheritDoc
     */
    public function exportLocation(): array
    {
        return [
            'disk' => 'other',
            'path' => null,
        ];
    }

    /**
     * @inheritDoc
     */
    public function fontsLocation(): array
    {
        return [
            'disk' => 'other',
            'path' => 'custom-font',
        ];
    }

    /**
     * @inheritDoc
     */
    public function templateLocation(): array
    {
        return [
            'disk' => 'other',
            'path' => 'custom-template',
        ];
    }

    /**
     * @inheritDoc
     */
    public function fonts(): array
    {
        return ['Font-In-Different-Location'];
    }

    /**
     * @inheritDoc
     */
    public function template(): string
    {
        return 'template-in-different-location.pdf';
    }

    public function draw(PdfWriter $writer)
    {
        $writer->newPage();
    }
}
