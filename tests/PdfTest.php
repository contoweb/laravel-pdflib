<?php

namespace Contoweb\Pdflib\Tests;

use Contoweb\Pdflib\Pdf;
use Contoweb\Pdflib\Tests\Files\PathHelper;
use Contoweb\Pdflib\Facades\Pdf as PdfFacade;
use Contoweb\Pdflib\Exceptions\MeasureException;
use Contoweb\Pdflib\Tests\Data\Stubs\MinimalDocument;
use Contoweb\Pdflib\Tests\Data\Stubs\TemplatePreviewDocument;
use Contoweb\Pdflib\Tests\Data\Stubs\CustomColoredFontDocument;
use Contoweb\Pdflib\Tests\Data\Stubs\InvalidTemplatePreviewDocument;

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

        $this->app['config']->set('pdf.exports.path', 'subdirectory');

        $pdf = $this->pdf->store($document, $fileName);

        $this->assertInstanceOf(Pdf::class, $pdf);
        $this->assertFileExists($filePath);
    }

    /**
     * @test
     */
    public function creates_preview_pdf_when_enabled()
    {
        $document        = new TemplatePreviewDocument();
        $fileName        = 'test.pdf';
        $fileNamePreview = 'test_preview.pdf';

        $filePathPreview = PathHelper::absolutePath($fileNamePreview, 'local');

        $pdf = $this->pdf->store($document, $fileName)->withPreview();

        $this->assertTrue($pdf);
        $this->assertFileExists($filePathPreview);
    }

    /**
     * @test
     */
    public function can_create_preview_pdf_with_own_name()
    {
        $document = new TemplatePreviewDocument();

        $fileName        = 'test.pdf';
        $fileNamePreview = 'other-name.pdf';

        $filePathPreview = PathHelper::absolutePath($fileNamePreview, 'local');

        $pdf = $this->pdf->store($document, $fileName)->withPreview($fileNamePreview);

        $this->assertTrue($pdf);
        $this->assertFileExists($filePathPreview);
    }

    /**
     * @test
     */
    public function throws_exception_when_preview_x_offset_is_invalid()
    {
        $fileName        = 'test.pdf';
        $fileNamePreview = 'other-name.pdf';

        $document = new InvalidTemplatePreviewDocument('invalid', 'y');

        $this->expectException(MeasureException::class);
        $this->expectErrorMessage('No X offset defined');

        $this->pdf->store($document, $fileName)->withPreview($fileNamePreview);
    }

    /**
     * @test
     */
    public function throws_exception_when_preview_y_offset_is_invalid()
    {
        $fileName        = 'test.pdf';
        $fileNamePreview = 'other-name.pdf';

        $document = new InvalidTemplatePreviewDocument('x', 'invalid');

        $this->expectException(MeasureException::class);
        $this->expectErrorMessage('No Y offset defined');

        $this->pdf->store($document, $fileName)->withPreview($fileNamePreview);
    }

    /**
     * @test
     */
    public function can_use_custom_fonts_and_color()
    {
        $document = new CustomColoredFontDocument();

        $fileName = 'test.pdf';
        $filePath = PathHelper::absolutePath($fileName, 'local');

        $pdf = $this->pdf->store($document, $fileName);

        $this->assertInstanceOf(Pdf::class, $pdf);
        $this->assertFileExists($filePath);
    }
}
