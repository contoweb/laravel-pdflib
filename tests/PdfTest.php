<?php

namespace Contoweb\Pdflib\Tests;

use Contoweb\Pdflib\Exceptions\DifferentLocationException;
use Contoweb\Pdflib\Exceptions\MeasureException;
use Contoweb\Pdflib\Facades\Pdf as PdfFacade;
use Contoweb\Pdflib\Pdf;
use Contoweb\Pdflib\Tests\Data\Stubs\CustomColoredFontDocument;
use Contoweb\Pdflib\Tests\Data\Stubs\DifferentLocationsDocument;
use Contoweb\Pdflib\Tests\Data\Stubs\InvalidDifferentLocationsDocument;
use Contoweb\Pdflib\Tests\Data\Stubs\InvalidTemplatePreviewDocument;
use Contoweb\Pdflib\Tests\Data\Stubs\MinimalDocument;
use Contoweb\Pdflib\Tests\Data\Stubs\TemplatePreviewDocument;
use Contoweb\Pdflib\Tests\Files\PathHelper;
use PHPUnit\Framework\Attributes\Test;

class PdfTest extends TestCase
{
    protected $pdf;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdf = $this->app->make(Pdf::class);
    }

    #[Test]
    public function can_store_a_pdf_document_local_with_facade()
    {
        $document = new MinimalDocument();
        $fileName = 'test.pdf';
        $filePath = PathHelper::absolutePath($fileName, 'local');

        $this->assertFileDoesNotExist($filePath);

        $pdf = PdfFacade::store($document, $fileName);

        $this->assertInstanceOf(Pdf::class, $pdf);
        $this->assertFileExists($filePath);
    }

    #[Test]
    public function can_store_pdf_in_config_export_local_disk()
    {
        $document = new MinimalDocument();
        $fileName = 'test.pdf';
        $filePath = PathHelper::absolutePath($fileName, 'local', 'subdirectory');

        $this->assertFileDoesNotExist($filePath);

        $this->app['config']->set('pdf.exports.disk', 'local');
        $this->app['config']->set('pdf.exports.path', 'subdirectory');

        $pdf = $this->pdf->store($document, $fileName);

        $this->assertInstanceOf(Pdf::class, $pdf);
        $this->assertFileExists($filePath);
    }

    #[Test]
    public function can_store_pdf_in_config_export_other_disk()
    {
        $document = new MinimalDocument();
        $fileName = 'test.pdf';
        $filePath = PathHelper::absolutePath($fileName, 'other');

        $this->assertFileDoesNotExist($filePath);

        $this->app['config']->set('pdf.exports.disk', 'other');
        $this->app['config']->set('pdf.exports.path');

        $pdf = $this->pdf->store($document, $fileName);

        $this->assertInstanceOf(Pdf::class, $pdf);
        $this->assertFileExists($filePath);
    }

    #[Test]
    public function can_store_pdf_in_config_export_disk_with_path()
    {
        $document = new MinimalDocument();
        $fileName = 'test.pdf';
        $filePath = PathHelper::absolutePath($fileName, 'local', 'subdirectory');

        $this->assertFileDoesNotExist($filePath);

        $this->app['config']->set('pdf.exports.path', 'subdirectory');

        $pdf = $this->pdf->store($document, $fileName);

        $this->assertInstanceOf(Pdf::class, $pdf);
        $this->assertFileExists($filePath);
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function throws_exception_when_preview_x_offset_is_invalid()
    {
        $fileName        = 'test.pdf';
        $fileNamePreview = 'other-name.pdf';

        $document = new InvalidTemplatePreviewDocument('invalid', 'y');

        $this->expectException(MeasureException::class);
        $this->expectExceptionMessage('No X offset defined');

        $this->pdf->store($document, $fileName)->withPreview($fileNamePreview);
    }

    #[Test]
    public function throws_exception_when_preview_y_offset_is_invalid()
    {
        $fileName        = 'test.pdf';
        $fileNamePreview = 'other-name.pdf';

        $document = new InvalidTemplatePreviewDocument('x', 'invalid');

        $this->expectException(MeasureException::class);
        $this->expectExceptionMessage('No Y offset defined');

        $this->pdf->store($document, $fileName)->withPreview($fileNamePreview);
    }

    #[Test]
    public function can_use_custom_fonts_and_color()
    {
        $document = new CustomColoredFontDocument();

        $fileName = 'test.pdf';
        $filePath = PathHelper::absolutePath($fileName, 'local');

        $pdf = $this->pdf->store($document, $fileName);

        $this->assertInstanceOf(Pdf::class, $pdf);
        $this->assertFileExists($filePath);
    }

    #[Test]
    public function can_define_different_locations()
    {
        $document = new DifferentLocationsDocument();

        $fileName = 'different-location-export-test.pdf';
        $filePath = PathHelper::absolutePath($fileName, 'other');

        $pdf = $this->pdf->store($document, $fileName);

        $this->assertInstanceOf(Pdf::class, $pdf);
        $this->assertFileExists($filePath);
    }

    #[Test]
    public function throws_exception_if_different_location_is_invalid()
    {
        // Todo: We only test different template location. Maybe testing different font location as well.
        $document = new InvalidDifferentLocationsDocument();

        $fileName = 'different-location-export-test.pdf';

        $this->expectException(DifferentLocationException::class);

        $this->pdf->store($document, $fileName);
    }
}
