<?php

namespace Contoweb\Pdflib\Tests;

use Contoweb\Pdflib\Exceptions\DocumentException;
use Contoweb\Pdflib\Exceptions\FontException;
use Contoweb\Pdflib\Tests\Files\PathHelper;
use Contoweb\Pdflib\Writers\PdfWriter;

class PdfWriterTest extends TestCase
{
    /**
     * @var PdfWriter
     */
    protected $writer;

    /**
     * @var string
     */
    protected $fullPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = $this->app->make(PdfWriter::class);
        $this->fullPath = PathHelper::absolutePath('test.pdf', 'local');
    }

    /**
     * @test
     */
    public function can_begin_a_new_document()
    {
        $this->assertTrue($this->writer->beginDocument($this->fullPath));

        $this->assertFileExists($this->fullPath);
    }

    /**
     * @test
     */
    public function can_write_a_document()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();
        $this->writer->finishDocument();

        $this->assertFileExists($this->fullPath);
    }

    /**
     * @test
     */
    public function can_create_multiple_pages()
    {
        $this->writer->beginDocument($this->fullPath);

        for($i  = 0; $i < 10; $i++) {
            $this->writer->newPage();
        }

        $this->writer->finishDocument();

        $this->assertFileExists($this->fullPath);
    }

    /**
     * @test
     */
    public function able_to_use_template()
    {
        $this->app['config']->set('pdf.templates.disk', 'local');
        $this->app['config']->set('pdf.templates.path', '');

        $this->assertTrue($this->writer->loadTemplate('template.pdf'));

        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage()->fromTemplatePage(1);
        $this->writer->finishDocument();

        /** Todo: Assert if document really uses template. */

        $this->assertFileExists($this->fullPath);
    }

    /**
     * @test
     */
    public function throws_an_exception_when_loading_unknown_template()
    {
        $this->app['config']->set('pdf.templates.disk', 'local');
        $this->app['config']->set('pdf.templates.path', '');

        $this->expectException(DocumentException::class);
        $this->writer->loadTemplate('unknown.pdf');
    }

    /**
     * @test
     */
    public function can_use_fonts_when_loaded()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->writer->loadFont('Arial');

        $this->assertInstanceOf(PdfWriter::class, $this->writer->useFont('Arial', 12));

        /** Todo: Assert if document really uses Arial font. */

        $this->writer->finishDocument();

        $this->assertFileExists($this->fullPath);
    }

    /**
     * @test
     */
    public function throws_an_exception_when_load_unknown_fonts()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->expectException(FontException::class);
        $this->writer->loadFont('unknown');


        $this->writer->finishDocument();

        $this->assertFileExists($this->fullPath);
    }
}