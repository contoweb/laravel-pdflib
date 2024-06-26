<?php

namespace Contoweb\Pdflib\Tests;

use Contoweb\Pdflib\Exceptions\ColorException;
use Contoweb\Pdflib\Exceptions\DocumentException;
use Contoweb\Pdflib\Exceptions\FontException;
use Contoweb\Pdflib\Exceptions\ImageException;
use Contoweb\Pdflib\Exceptions\MeasureException;
use Contoweb\Pdflib\Helpers\MeasureCalculator;
use Contoweb\Pdflib\Tests\Files\PathHelper;
use Contoweb\Pdflib\Writers\PdfWriter;
use PHPUnit\Framework\Attributes\Test;

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

        $this->writer   = $this->app->make(PdfWriter::class);
        $this->fullPath = PathHelper::absolutePath('test.pdf', 'local');
    }

    #[Test]
    public function can_load_a_license()
    {
        $this->app['config']->set('pdf.license', 'invalid-license');

        $this->expectExceptionMessage('Invalid license key');

        $this->app->make(PdfWriter::class);
    }

    #[Test]
    public function can_begin_a_new_document()
    {
        $this->assertTrue($this->writer->beginDocument($this->fullPath));

        $this->assertFileExists($this->fullPath);
    }

    #[Test]
    public function throws_an_expection_when_document_cant_be_created()
    {
        $invalidPath = PathHelper::absolutePath('test.pdf', 'local', 'invalid-path');

        $this->expectException(DocumentException::class);

        $this->writer->beginDocument($invalidPath);
    }

    #[Test]
    public function can_write_a_document()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();
        $this->writer->finishDocument();

        $this->assertFileExists($this->fullPath);
    }

    #[Test]
    public function can_create_multiple_pages()
    {
        $this->writer->beginDocument($this->fullPath);

        for ($i  = 0; $i < 10; $i++) {
            $this->writer->newPage();
        }

        $this->writer->finishDocument();

        $this->assertFileExists($this->fullPath);
    }

    #[Test]
    public function able_to_use_template()
    {
        $templatePath = PathHelper::absolutePath('template.pdf', 'local', 'templates', false);

        $this->assertTrue($this->writer->loadTemplate($templatePath));

        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage()->fromTemplatePage(1);
        $this->writer->finishDocument();

        /* Todo: Assert if document really uses template. */

        $this->assertFileExists($this->fullPath);
    }

    #[Test]
    public function throws_an_exception_when_loading_unknown_template()
    {
        $this->expectException(DocumentException::class);
        $this->writer->loadTemplate('unknown.pdf');
    }

    #[Test]
    public function throws_an_exception_when_load_unknown_fonts()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->expectException(FontException::class);
        $this->writer->loadFont('unknown');
    }

    #[Test]
    public function can_use_font_files()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->loadTestFont();

        $this->assertInstanceOf(PdfWriter::class, $this->writer->useFont('OpenSans-Regular', 12));

        /* Todo: Assert if document really uses the font. */

        $this->writer->finishDocument();

        $this->assertFileExists($this->fullPath);
    }

    #[Test]
    public function throws_an_exception_when_use_unloaded_fonts()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->expectException(FontException::class);
        $this->expectExceptionMessage('Font "not loaded" not loaded.');

        $this->writer->useFont('not loaded', 12);
    }

    #[Test]
    public function can_use_defined_colors()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->writer->loadColor(
            'blue', [
                'cmyk',
                100, 100, 0, 0,
            ]);

        $this->writer->loadColor(
            'orange', [
                'rgb',
                255, 165, 0,
            ]);

        $this->assertInstanceOf(PdfWriter::class, $this->writer->useColor('blue'));
        $this->assertInstanceOf(PdfWriter::class, $this->writer->useColor('orange'));

        /* Todo: Assert if document really uses the font. */

        $this->writer->finishDocument();

        $this->assertFileExists($this->fullPath);
    }

    #[Test]
    public function can_use_font_with_color()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->loadTestFont();

        $this->writer->loadColor('blue', [
            'cmyk',
            100, 100, 0, 0,
        ]);

        $this->assertInstanceOf(PdfWriter::class, $this->writer->useFont('OpenSans-Regular', 12, 'blue'));

        /* Todo: Assert if document really uses the font. */

        $this->writer->finishDocument();

        $this->assertFileExists($this->fullPath);
    }

    #[Test]
    public function throws_an_exception_when_use_undefined_color()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->expectException(ColorException::class);
        $this->writer->useColor('unknown');
    }

    #[Test]
    public function throws_an_exception_when_use_invalid_loaded_color()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->expectException(ColorException::class);
        $this->writer->loadColor('blue-invalid', ['invalid', 100, 50, 70]);

        $this->writer->useColor('blue-invalid');
    }

    #[Test]
    public function can_position_the_cursor_in_units()
    {
        $units = ['mm', 'pt'];

        foreach ($units as $fromUnit) {
            foreach ($units as $toUnit) {
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

    #[Test]
    public function can_go_to_next_line()
    {
        $startPosition = 100;
        $fontSize      = 12;
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->loadTestFont();

        $this->writer->loadFont('OpenSans-Regular');
        $this->writer->useFont('OpenSans-Regular', $fontSize);

        $this->writer->setYPosition($startPosition);

        $this->writer->nextLine();

        $this->assertEquals(
            round($startPosition - MeasureCalculator::calculateToMm($fontSize, 'pt'), 3),
            round($this->writer->getYPosition(), 3)
        );
    }

    #[Test]
    public function can_go_to_next_line_with_line_spacing()
    {
        $startPosition = 100;
        $fontSize      = 12;
        $spacing       = 2;

        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->loadTestFont();

        $this->writer->useFont('OpenSans-Regular', $fontSize);

        $this->writer->setYPosition($startPosition);

        $this->writer->nextLine($spacing);

        $this->assertEquals(
            round($startPosition - (MeasureCalculator::calculateToMm($fontSize, 'pt') * $spacing), 3),
            round($this->writer->getYPosition(), 3)
        );
    }

    #[Test]
    public function can_manually_enable_and_disable_offset()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->writer->setXOffset(20);
        $this->writer->setYOffset(20);

        $this->writer->enableOffset();
        $this->writer->setPosition(100, 200);

        $this->assertEquals(120, round($this->writer->getXPosition(), 3));
        $this->assertEquals(220, round($this->writer->getYPosition(), 3));

        $this->writer->disableOffset();
        $this->writer->setPosition(100, 200);

        $this->assertEquals(100, round($this->writer->getXPosition(), 3));
        $this->assertEquals(200, round($this->writer->getYPosition(), 3));
    }

    #[Test]
    public function throws_exception_when_invalid_unit_is_used()
    {
        $this->expectException(MeasureException::class);
        $this->writer->setPosition(10, 10, 'invalid');
    }

    #[Test]
    public function can_write_text()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->loadTestFont();

        $this->writer->useFont('OpenSans-Regular', 12);

        $this->assertInstanceOf(
            PdfWriter::class,
            $this->writer->writeText('Test')
        );

        /* Todo: Assert that the text is really placed in the document */
    }

    #[Test]
    public function can_write_text_line()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->loadTestFont();

        $this->writer->useFont('OpenSans-Regular', 12);

        $this->assertInstanceOf(
            PdfWriter::class,
            $this->writer->writeTextLine('Test')
        );

        /* Todo: Assert that the text is really placed in the document */
    }

    #[Test]
    public function can_place_an_image()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->assertInstanceOf(
            PdfWriter::class,
            $this->writer->drawImage(
                PathHelper::absolutePath(
                    'example.jpeg',
                    'local',
                    'images',
                    false
                ),
                100,
                100
            )
        );

        /* Todo: Assert that the image is really placed in the document */
    }

    #[Test]
    public function can_place_a_rounded_image()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->assertInstanceOf(
            PdfWriter::class,
            $this->writer->circleImage(
                PathHelper::absolutePath(
                    'example.jpeg',
                    'local',
                    'images',
                    false
                ),
                100
            )
        );

        /* Todo: Assert that the image is really placed in the document */
    }

    #[Test]
    public function throws_an_exception_when_loading_invalid_image()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->expectException(ImageException::class);
        $this->writer->drawImage(PathHelper::absolutePath('invalid.jpeg', 'local', 'images', false), 100, 100);
    }

    #[Test]
    public function reuses_already_loaded_image()
    {
        // We create two files:
        // - We load an image one time into the first document
        // - We load the same image 10 times into the second document
        // --> File size of the second one should not increase 2x if image is reused.

        $file1 = PathHelper::absolutePath('file1.pdf', 'local');
        $file2 = PathHelper::absolutePath('file2.pdf', 'local');

        $this->writer->beginDocument($file1);
        $this->writer->newPage();

        $this->writer->drawImage(
            PathHelper::absolutePath('example.jpeg', 'local', 'images', false),
            100,
            100
        );

        $this->writer->finishDocument();

        $this->writer->beginDocument($file2);

        for ($i = 0; $i < 10; $i++) {
            $this->writer->newPage();

            $this->writer->drawImage(
                PathHelper::absolutePath('example.jpeg', 'local', 'images', false),
                100,
                100
            );
        }

        $this->writer->finishDocument();

        $size1 = filesize($file1);
        $size2 = filesize($file2);

        $this->assertLessThan(2, $size2 / $size1);
    }

    #[Test]
    public function applies_default_line_spacing()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->loadTestFont();
        $this->writer->useFont('OpenSans-Regular', 10);

        $this->assertEquals(0, $this->writer->getYPosition('pt'));

        // The default value for nextLine is 1, the next line is 10pt below the current position
        $this->writer->nextLine();

        $this->assertEquals(-10, $this->writer->getYPosition('pt'));

        $this->writer->finishDocument();
    }

    #[Test]
    public function can_set_line_spacing()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->loadTestFont();
        $this->writer->useFont('OpenSans-Regular', 10);

        $this->assertEquals(0, $this->writer->getYPosition('pt'));

        // Set the line spacing to 2, the next line is 20pt below the current position
        $this->writer->setLineSpacing(2);
        $this->writer->nextLine();

        $this->assertEquals(-20, $this->writer->getYPosition('pt'));

        $this->writer->finishDocument();
    }

    #[Test]
    public function can_overwrite_line_spacing_with_next_line()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->loadTestFont();
        $this->writer->useFont('OpenSans-Regular', 10);

        $this->assertEquals(0, $this->writer->getYPosition('pt'));

        // Set the line spacing to 2
        $this->writer->setLineSpacing(2);

        // Overwrite the line spacing with 3, the next line is 30pt below the current position
        $this->writer->nextLine(3);

        $this->assertEquals(-30, $this->writer->getYPosition('pt'));

        $this->writer->finishDocument();
    }

    #[Test]
    public function get_correct_text_width()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->loadTestFont();

        // 'Test' with font OpenSans-Regular and font size 10pt has a width of 19.44pt
        $this->assertEquals(19.44, $this->writer->getTextWidth('Test', 'OpenSans-Regular', 10, 'pt'));

        $this->writer->finishDocument();
    }

    #[Test]
    public function get_correct_text_width_without_parameters()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->loadTestFont();
        $this->writer->useFont('OpenSans-Regular', 10);

        // 'Test' with font OpenSans-Regular and font size 10pt has a width of 19.44pt
        $this->assertEquals(19.44, $this->writer->getTextWidth('Test', null, null, 'pt'));

        $this->writer->finishDocument();
    }

    #[Test]
    public function get_correct_font()
    {
        $this->writer->beginDocument($this->fullPath);
        $this->writer->newPage();

        $this->loadTestFont();

        $this->writer->useFont('OpenSans-Regular', 10);

        $font = $this->writer->getFonts()['OpenSans-Regular'];

        $this->assertEquals($font, $this->writer->getCurrentFont());

        $this->writer->finishDocument();
    }

    /**
     * Set font search path and load a test font.
     *
     * @throws FontException
     */
    private function loadTestFont()
    {
        $fontPath = PathHelper::absolutePath('', 'local', 'fonts', false);

        $this->writer->defineFontSearchPath($fontPath);

        $this->writer->loadFont('OpenSans-Regular');
    }
}
