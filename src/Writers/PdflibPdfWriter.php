<?php

namespace Contoweb\Pdflib\Writers;

use Contoweb\Pdflib\WriterComponents\Table;
use Contoweb\Pdflib\Exceptions\ColorException;
use Contoweb\Pdflib\Exceptions\CoordinateException;
use Contoweb\Pdflib\Exceptions\DocumentException;
use Contoweb\Pdflib\Exceptions\FontException;
use Contoweb\Pdflib\Exceptions\ImageException;
use Contoweb\Pdflib\Exceptions\TextException;
use Contoweb\Pdflib\Files\FileManager;
use Contoweb\Pdflib\Helpers\MeasureCalculator;
use Exception;
use PDFlib;

class PdflibPdfWriter extends PDFlib implements PdfWriter
{
    /**
     * X position in pt.
     * @var float
     */
    public $xPos = 0;

    /**
     * Y position in pt.
     * @var float
     */
    public $yPos = 0;

    /**
     * Y offset for preview.
     * @var float
     */
    protected $xOffset;

    /**
     * X offset for preview.
     * @var float
     */
    protected $yOffset;

    /**
     * Use offsets.
     * @var float
     */
    protected $useOffset = false;

    /**
     * Loaded colors.
     * @var array
     */
    protected $colors = [];

    /**
     * Loaded fonts.
     * @var array
     */
    protected $fonts = [];

    /**
     * Already loaded images.
     * @var array
     */
    protected $imageCache = [];

    /**
     * Indicates if a new page is already created and the page should be closed before the next one starts.
     * @var bool
     */
    protected $siteOpen = false;

    /**
     * Path to the template.
     * @var string
     */
    protected $template;

    /**
     * Line offset in pt.
     * @var float
     */
    protected $lineOffset = 0;

    /**
     * Current font size.
     * @var
     */
    private $fontSize;

    /**
     * A table object.
     * @var Table
     */
    protected $table;

    /**
     * PdflibPdfWriter constructor.
     *
     * @param $license
     * @param $creator
     * @param $searchPath
     */
    public function __construct($license, $creator, $searchPath)
    {
        parent::__construct();

        $this->set_info('Creator', $creator);

        if ($license) {
            $this->set_option('license=' . $license);
        }

        $this->set_option('errorpolicy=return');
        $this->set_option('stringformat=utf8');

        $this->set_option('searchpath={' . $searchPath . '}');
        // $this->writer->set_option("spotcolorlookup=" . $spotcolorlookup);
    }

    /**
     * {@inheritdoc}
     */
    public function beginDocument($path, $optlist = null)
    {
        if ($this->begin_document($path, '') == 0) {
            throw new DocumentException('Error: ' . $this->get_errmsg());
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function finishDocument()
    {
        if ($this->siteOpen) {
            $this->end_page_ext('');
            $this->siteOpen = false;
        }

        $this->end_document('');

        $this->imageCache = [];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function newPage($width = 50, $height = 50, $optlist = null)
    {
        if ($this->siteOpen) {
            $this->end_page_ext('');
        }

        $this->siteOpen = true;

        $this->begin_page_ext(
            MeasureCalculator::calculateToPt($width),
            MeasureCalculator::calculateToPt($height),
            $optlist ?: '');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageSize($side = 'width')
    {
        return $this->get_option('page' . $side, '');
    }

    /**
     * {@inheritdoc}
     */
    public function loadTemplate($name, $path = null, $optlist = null)
    {
        $this->template = $this->open_pdi_document(
            $path ?: FileManager::templatePath($name),
            $optlist ?: ''
        );

        if ($this->template == 0) {
            throw new DocumentException('Error: ' . $this->get_errmsg());
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function fromTemplatePage($pageNumber)
    {
        $page = $this->open_pdi_page($this->template, $pageNumber, 'cloneboxes');
        $this->fit_pdi_page($page, 0, 0, 'adjustpage cloneboxes');
        $this->close_pdi_page($page);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function closeTemplate()
    {
        $this->close_pdi_document($this->template);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function loadColor($name, array $color)
    {
        array_unshift($color, 'fill');

        // Divide all color definitions to convert it for PDFLib.
        $color = array_map(function ($definition) use ($color) {
            if (is_numeric($definition)) {
                if ($color[1] === 'cmyk') {
                    return $definition / 100;
                }

                if ($color[1] === 'rgb') {
                    return $definition / 255;
                }
            }

            return $definition;
        }, $color);

        // This allows to define rgb colors with only three parameters.
        if (! array_key_exists(5, $color)) {
            $color[5] = 0;
        }

        $this->colors[$name] = $color;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function useColor($name)
    {
        if (array_key_exists($name, $this->colors)) {
            try {
                call_user_func_array([$this, 'setcolor'], $this->colors[$name]);
            } catch (Exception $e) {
                throw new ColorException($e);
            }
        } else {
            throw new ColorException('Color "' . $name . '" not defined.');
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function loadFont($name, $encoding = null, $optlist = null)
    {
        $this->fonts[$name] = $this->load_font($name, $encoding ?: 'unicode', $optlist ?: 'embedding');

        if ($this->fonts[$name] == 0) {
            throw new FontException('Error: ' . $this->get_errmsg());
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function useFont($name, $size, $color = null)
    {
        $this->fontSize = $size;

        if (array_key_exists($name, $this->fonts)) {
            $this->setfont($this->fonts[$name], $size);
        } else {
            throw new FontException('Font "' . $name . '" not loaded.');
        }

        if ($color) {
            $this->useColor($color);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFonts()
    {
        return $this->fonts;
    }

    /**
     * {@inheritdoc}
     */
    public function writeText($text)
    {
        $this->set_text_pos($this->xPos, $this->yPos);
        $this->show($text);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function writeTextLine($text, $optlist = null)
    {
        $this->fit_textline($text, $this->xPos, $this->yPos, $optlist);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function nextLine($spacing = 1.0)
    {
        $this->setYPosition($this->yPos - ($this->fontSize * $spacing), 'pt', true);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTextWidth($text, $font, $fontSize, $unit = null)
    {
        $textWidth = MeasureCalculator::calculateToUnit(
            $this->stringwidth($text, $this->load_font($font, 'unicode', 'embedding'), $fontSize),
            $unit ?: config('pdf.measurement.unit', 'pt'),
            'pt'
        );

        return $textWidth;
    }

    /**
     * {@inheritdoc}
     */
    public function drawImage($imagePath, $width, $height, $loadOptions = null, $fitOptions = null)
    {
        $image = $this->preloadImage($imagePath, $loadOptions);

        if (strpos($imagePath, '.pdf') || strpos($imagePath, '.svg')) {
            // vector images
            $fitObjectMethod = 'fit_graphics';
        } else {
            // pixel images
            $fitObjectMethod = 'fit_image';
        }

        $this->{$fitObjectMethod}(
            $image,
            MeasureCalculator::calculateToPt($this->xPos, 'pt'),
            MeasureCalculator::calculateToPt($this->yPos, 'pt'),
            $fitOptions ?: 'boxsize {' . MeasureCalculator::calculateToPt($width) . ' ' . MeasureCalculator::calculateToPt($height) . '} position left fitmethod=meet'
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function circleImage($imagePath, $size, $loadOptions = null)
    {
        $this->save();

        $width  = $size;
        $height = $size;
        $radius = $size / 2;

        // Set curves of the circle
        $this->moveto($this->xPos + $radius, $this->yPos);
        $this->lineto($this->xPos + $width - $radius, $this->yPos);
        $this->arc($this->xPos + $width - $radius, $this->yPos + $radius, $radius, 270, 360);
        $this->lineto($this->xPos + $width, $this->yPos + $height - $radius);
        $this->arc($this->xPos + $width - $radius, $this->yPos + $height - $radius, $radius, 0, 90);
        $this->lineto($this->xPos + $radius, $this->yPos + $height);
        $this->arc($this->xPos + $radius, $this->yPos + $height - $radius, $radius, 90, 180);
        $this->lineto($this->xPos, $this->yPos + $radius);
        $this->arc($this->xPos + $radius, $this->yPos + $radius, $radius, 180, 270);

        // Set the rounded corners from the code above
        $this->clip();

        // Load image
        $image = $this->preloadImage($imagePath, $loadOptions);

        // Fit the image into the circle
        $this->fit_image($image,
            MeasureCalculator::calculateToPt($this->xPos, 'pt'),
            MeasureCalculator::calculateToPt($this->yPos, 'pt'),
            'boxsize {' . MeasureCalculator::calculateToPt($width) . ' ' . MeasureCalculator::calculateToPt($height) . '} position center fitmethod=meet');

        // Close image and restore original clipping (no clipping)
        $this->close_image($image);

        // Restore the state without rounded corners
        $this->restore();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function drawRectangle($width, $height)
    {
        $this->rect($this->xPos, $this->yPos, $width, $height);
        $this->fill();
    }

    /**
     * {@inheritdoc}
     */
    public function drawLine($xFrom, $xTo, $yFrom, $yTo, $lineWidth = 0.3, $unit = null)
    {
        $this->setlinewidth($lineWidth);
        $this->moveto(MeasureCalculator::calculateToPt($xFrom, $unit), MeasureCalculator::calculateToPt($yFrom, $unit));
        $this->lineto(MeasureCalculator::calculateToPt($xTo, $unit), MeasureCalculator::calculateToPt($yTo, $unit));
        $this->stroke();
    }

    /**
     * {@inheritdoc}
     */
    public function setPosition($x, $y, $unit = null)
    {
        $this->setXPosition($x, $unit);
        $this->setYPosition($y, $unit);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setXPosition($measure, $unit = null, $ignoreOffset = false)
    {
        $measure = MeasureCalculator::calculateToPt($measure, $unit);

        if ($this->useOffset && $ignoreOffset === false) {
            $measure += $this->xOffset;
        }

        $this->xPos = $measure;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getXPosition($unit = null)
    {
        return MeasureCalculator::calculateToUnit(
            $this->xPos,
            $unit ?: config('pdf.measurement.unit', 'pt'),
            'pt'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setYPosition($measure, $unit = null, $ignoreOffset = false)
    {
        $measure = MeasureCalculator::calculateToPt($measure, $unit);

        if ($this->useOffset && $ignoreOffset === false) {
            $measure += $this->yOffset;
        }

        $this->yPos = $measure;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getYPosition($unit = null)
    {
        return MeasureCalculator::calculateToUnit(
            $this->yPos,
            $unit ?: config('pdf.measurement.unit', 'pt'),
            'pt'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getElementPosition($infobox, $corner)
    {
        if ($this->info_matchbox($infobox, 1, 'exists') == 1) {
            return $this->info_matchbox($infobox, 1, $corner);
        } else {
            throw new CoordinateException('Error: ' . $this->get_errmsg());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getElementSize($element, $dimension = 'width')
    {
        return $this->info_table($element, $dimension);
    }

    /**
     * {@inheritdoc}
     */
    public function setXOffset($measure, $unit = null)
    {
        $measure       = MeasureCalculator::calculateToPt($measure, $unit);
        $this->xOffset = $measure;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setYOffset($measure, $unit = null)
    {
        $measure       = MeasureCalculator::calculateToPt($measure, $unit);
        $this->yOffset = $measure;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function enableOffset()
    {
        $this->useOffset = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function disableOffset()
    {
        $this->useOffset = false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function inOriginal()
    {
        return ! $this->useOffset;
    }

    /**
     * {@inheritdoc}
     */
    public function inPreview()
    {
        return $this->useOffset;
    }

    /**
     * Loads existing or new image.
     *
     * @param $imagePath
     * @param $loadOptions
     * @return int
     * @throws ImageException
     */
    protected function preloadImage($imagePath, $loadOptions)
    {
        // We're using the PDFLib image index so the same image is only embedded one time in the PDF.
        if (array_key_exists($imagePath, $this->imageCache)) {
            $image = $this->imageCache[$imagePath];
        } else {
            if (strpos($imagePath, '.pdf') || strpos($imagePath, '.svg')) {
                // vector images
                $image = $this->load_graphics('auto', $imagePath, $loadOptions ?: '');
            } else {
                // pixel images
                $image = $this->load_image('auto', $imagePath, $loadOptions ?: '');
            }
            $this->imageCache[$imagePath] = $image;
        }

        if ($image <= 0) {
            throw new ImageException($this->get_errmsg());
        }

        return $image;
    }

    /**
     * {@inheritdoc}
     */
    public function addTextflow($textflow, $title, $optlist = null)
    {
        $textflow = $this->add_textflow($textflow, $title, $optlist);

        if ($textflow == 0) {
            throw new TextException('Error: ' . $this->get_errmsg());
        }

        return $textflow;
    }

    /**
     * {@inheritdoc}
     */
    public function newTable($items)
    {
        $table = new Table($this);

        $table->setItems($items);

        return $table;
    }
}
