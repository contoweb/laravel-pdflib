<?php

namespace Contoweb\Pdflib\Tests;

use Contoweb\Pdflib\Pdf;
use Contoweb\Pdflib\Facades\Pdf as PdfFacade;
use Contoweb\Pdflib\Tests\Data\Stubs\MinimalDocument;
use Contoweb\Pdflib\Tests\Files\PathHelper;

class PdfTest extends TestCase
{
    protected $pdf;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdf = $this->app->make(Pdf::class);
    }

    /**
     * @test
     */
    public function can_store_a_pdf_document_local_with_facade()
    {
        $document = new MinimalDocument();
        $fileName = 'test.pdf';
        $filePath = PathHelper::absolutePath($fileName, 'local');

        $this->assertFileNotExists($filePath);

        $pdf = PdfFacade::store($document, $fileName);

        $this->assertInstanceOf(Pdf::class, $pdf);
        $this->assertFileExists($filePath);
    }

    /**
     * @test
     */
    public function can_store_pdf_in_config_export_local_disk()
    {
        $document = new MinimalDocument();
        $fileName = 'test.pdf';
        $filePath = PathHelper::absolutePath($fileName, 'local', 'subdirectory');

        $this->assertFileNotExists($filePath);

        $this->app['config']->set('pdf.exports.disk', 'local');
        $this->app['config']->set('pdf.exports.path', 'subdirectory');

        $pdf = $this->pdf->store($document, $fileName);

        $this->assertInstanceOf(Pdf::class, $pdf);
        $this->assertFileExists($filePath);
    }

    /**
     * @test
     */
    public function can_store_pdf_in_config_export_other_disk()
    {
        $document = new MinimalDocument();
        $fileName = 'test.pdf';
        $filePath = PathHelper::absolutePath($fileName, 'other');

        $this->assertFileNotExists($filePath);

        $this->app['config']->set('pdf.exports.disk', 'other');
        $this->app['config']->set('pdf.exports.path');

        $pdf = $this->pdf->store($document, $fileName);

        $this->assertInstanceOf(Pdf::class, $pdf);
        $this->assertFileExists($filePath);
    }

    /**
     * @test
     */
    public function can_store_pdf_in_config_export_disk_with_path()
    {
        $document = new MinimalDocument();
        $fileName = 'test.pdf';
        $filePath = PathHelper::absolutePath($fileName, 'local', 'subdirectory');

        $this->assertFileNotExists($filePath);

        $this->app['config']->set('pdf.exports.disk', 'local');
        $this->app['config']->set('pdf.exports.path', 'subdirectory');

        $pdf = $this->pdf->store($document, $fileName);

        $this->assertInstanceOf(Pdf::class, $pdf);
        $this->assertFileExists($filePath);
    }
}