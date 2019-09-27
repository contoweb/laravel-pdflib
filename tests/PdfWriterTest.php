<?php

namespace Contoweb\Pdflib\Tests;

use Contoweb\Pdflib\Exceptions\ColorException;
use Contoweb\Pdflib\Exceptions\DocumentException;
use Contoweb\Pdflib\Exceptions\FontException;
use Contoweb\Pdflib\Helpers\MeasureCalculator;
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
        $this->expectException(DocumentException::class);
        $this->writer->loadTemplate('unknown.pdf');
    }

    /**
     * @test
     */
    public function can_use_system_fonts_when_loaded()
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
    }

    /**
     * @test
     */
    public function can_use_font_files()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->writer->loadFont('OpenSans-Regular');

        $this->assertInstanceOf(PdfWriter::class, $this->writer->useFont('OpenSans-Regular', 12));

        /** Todo: Assert if document really uses the font. */

        $this->writer->finishDocument();

        $this->assertFileExists($this->fullPath);
    }

    /**
     * @test
     */
    public function can_use_defined_colors()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->writer->loadColor('blue', [
            'cmyk',
            100, 100, 0, 0
        ]);

        $this->assertInstanceOf(PdfWriter::class, $this->writer->useColor('blue'));

        /** Todo: Assert if document really uses the font. */

        $this->writer->finishDocument();

        $this->assertFileExists($this->fullPath);
    }

    /**
     * @test
     */
    public function can_use_font_with_color()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->writer->loadFont('Arial');

        $this->writer->loadColor('blue', [
            'cmyk',
            100, 100, 0, 0
        ]);

        $this->assertInstanceOf(PdfWriter::class, $this->writer->useFont('Arial', 12, 'blue'));

        /** Todo: Assert if document really uses the font. */

        $this->writer->finishDocument();

        $this->assertFileExists($this->fullPath);
    }

    /**
     * @test
     */
    public function throws_an_exception_when_use_undefined_color()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->expectException(ColorException::class);
        $this->writer->useColor('unknown');
    }

    /**
     * @test
     */
    public function can_go_to_next_line()
    {
        $startPosition = 100;
        $fontSize = 12;
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->writer->loadFont('Arial');
        $this->writer->useFont('Arial', $fontSize);

        $this->writer->setYPosition($startPosition);

        $this->writer->nextLine();

        $this->assertEquals(
            round($startPosition - MeasureCalculator::calculateToMm($fontSize, 'pt'), 3),
            round($this->writer->getYPosition(), 3)
        );
    }

    /**
     * @test
     */
    public function can_position_the_cursor_in_units()
    {
        $units = ['mm', 'pt'];

        foreach($units as $fromUnit) {
            foreach($units as $toUnit) {
                $this->writer->setXPosition(100, $fromUnit);

                self::assertEquals(
                    round(MeasureCalculator::calculateToUnit(100, $toUnit, $fromUnit), 3),
                    round($this->writer->getXPosition($toUnit), 3)
                );

                $this->writer->setYPosition(200, $fromUnit);

                self::assertEquals(
                    round(MeasureCalculator::calculateToUnit(200, $toUnit, $fromUnit), 3),
                    round($this->writer->getYPosition($toUnit), 3)
                );

                $this->writer->setPosition(50, 60, $fromUnit);

                self::assertEquals(
                    round(MeasureCalculator::calculateToUnit(50, $toUnit, $fromUnit), 3),
                    round($this->writer->getXPosition($toUnit), 3)
                );

                self::assertEquals(
                    round(MeasureCalculator::calculateToUnit(60, $toUnit, $fromUnit), 3),
                    round($this->writer->getYPosition($toUnit), 3)
                );
            }
        }
    }

    /**
     * @test
     */
    public function can_go_to_next_line_with_line_spacing()
    {
        $startPosition = 100;
        $fontSize = 12;
        $spacing = 2;

        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->writer->loadFont('Arial');
        $this->writer->useFont('Arial', $fontSize);

        $this->writer->setYPosition($startPosition);

        $this->writer->nextLine($spacing);

        $this->assertEquals(
            round($startPosition - (MeasureCalculator::calculateToMm($fontSize, 'pt') * $spacing), 3),
            round($this->writer->getYPosition(), 3)
        );
    }
}