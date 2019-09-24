<?php

namespace Contoweb\Pdflib\Tests;

use Contoweb\Pdflib\Pdf;
use Contoweb\Pdflib\Facades\Pdf as PdfFacade;
use Contoweb\Pdflib\Tests\Data\Stubs\MinimalDocument;

class PdfTest extends TestCase
{
    /**
     * @var Pdf
     */
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = $this->app->make(Pdf::class);
    }

    /**
     * @test
     */
    public function can_store_a_pdf_document_local_with_facade()
    {
        $document = new MinimalDocument();

        $pdf = PdfFacade::store($document, 'test.pdf');

        $this->assertInstanceOf(Pdf::class, $pdf);
        $this->assertFileExists(__DIR__ . '/Data/Storage/Local/test.pdf');
    }
}