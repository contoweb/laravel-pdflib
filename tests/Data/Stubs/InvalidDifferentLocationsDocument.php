<?php

namespace Contoweb\Pdflib\Tests\Data\Stubs;

use Contoweb\Pdflib\Concerns\DifferentTemplateLocation;
use Contoweb\Pdflib\Concerns\FromTemplate;
use Contoweb\Pdflib\Concerns\WithDraw;
use Contoweb\Pdflib\Writers\PdfWriter;

class InvalidDifferentLocationsDocument implements
    WithDraw,
    FromTemplate,
    DifferentTemplateLocation
{
    /**
     * @inheritDoc
     */
    public function templateLocation(): array
    {
        return [
            'disk'            => 'other',
            'wrong-parameter' => 'invalid',
        ];
    }

    /**
     * @inheritDoc
     */
    public function template(): string
    {
        return 'template.pdf';
    }

    /**
     * @inheritDoc
     */
    public function fonts(): array
    {
        return ['OpenSans-Regular'];
    }

    public function draw(PdfWriter $writer)
    {
        $writer->newPage();
    }
}
