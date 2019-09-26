<?php

namespace Contoweb\Pdflib\Writers;

use Contoweb\Pdflib\Exceptions\ColorException;
use Contoweb\Pdflib\Exceptions\FontException;
use Contoweb\Pdflib\Exceptions\ImageException;
use Contoweb\Pdflib\Files\FileManager;
use Contoweb\Pdflib\Helpers\MeasureCalculator;
use Exception;
use PDFlib;

class PdflibPdfWriter extends PDFlib implements PdfWriter
{
    /**
     * X position in pt
     * @var float
     */
    public $xPos = 0;

    /**
     * Y position in pt
     * @var float
     */
    public $yPos = 0;

    /**
     * Y offset for preview
     * @var float
     */
    protected $xOffset;

    /**
     * X offset for preview
     * @var float
     */
    protected $yOffset;

    /**
     * Use offsets
     * @var float
     */
    protected $useOffset = false;

    /**
     * Loaded colors
     * @var array
     */
    protected $colors;

    /**
     * Loaded fonts
     * @var array
     */
    protected $fonts;

    /**
     * Indicates if a new page is already created and the page should be closed before the next one starts
     * @var boolean
     */
    protected $siteOpen = false;

    /**
     * Path to the template
     * @var string
     */
    protected $template;

    /**
     * Line offset in pt
     * @var float
     */
    protected $lineOffset = 0;

    /**
     * Current font size.
     * @var
     */
    private $fontSize;

    /**
     * PdflibPdfWriter constructor.
     * @param $license
     * @param $creator
     * @param $searchPath
     */
    public function __construct($license, $creator, $searchPath)
    {
        parent::__construct();

        $this->set_info("Creator", $creator);

        if($license) {
            $this->set_option("license=" . $license);
        }

        $this->set_option("errorpolicy=return");
        $this->set_option("stringformat=utf8");

        $this->set_option("searchpath={" . $searchPath . "}");
        // $this->writer->set_option("spotcolorlookup=" . $spotcolorlookup);
    }

    /**
     * {@inheritdoc}
     */
    public function loadTemplate($name, $path = null, $optlist = null)
    {
        $this->template = $this->open_pdi_document(
            $path ?: FileManager::templatePath($name),
            $optlist ?: ""
        );

        if($this->template == 0) {
            throw new Exception("Error: " . $this->get_errmsg());
        }

        return true;
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

        // This allows to define rgb colors with only three parameters.
        if(! array_key_exists(5, $color)) {
            $color[5] = 0;
        }

        $this->colors[$name] = $color;

        return $this;
    }

    /**
     * @param $name
     * @throws ColorException
     */
    public function useColor($name)
    {
        if (array_key_exists($name, $this->colors)) {
            try {
                call_user_func_array(array($this, 'setcolor'), $this->colors[$name]);
            } catch (Exception $e) {
                throw new ColorException($e);
            }
        } else {
            throw new ColorException('Color "' . $name . '" not loaded.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadFont($name, $encoding = null, $optlist = null)
    {
        $this->fonts[$name] = $this->load_font($name, $encoding ?: "unicode", $optlist ?: "embedding");

        if($this->fonts[$name] == 0) {
            throw new FontException("Error: " . $this->get_errmsg());
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     */
    public function useFont($name, $size, $color = null)
    {
        $this->fontSize = $size;

        if(array_key_exists($name, $this->fonts)) {
            $this->setfont($this->fonts[$name], $size);
        } else {
            throw new FontException('Font "' . $name . '" not loaded.');
        }

        if($color) {
            $this->useColor($color);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function newPage($width = 50, $height = 50, $optlist = null)
    {
        if ($this->siteOpen) {
            $this->end_page_ext("");
            $this->siteOpen = false;
        } else {
            $this->siteOpen = true;
        }

        $this->begin_page_ext(
            MeasureCalculator::calculateToPt($width, config('pdf.measurement.unit')),
            MeasureCalculator::calculateToPt($height, config('pdf.measurement.unit')),
        $optlist ?: "");

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fromTemplatePage($pageNumber)
    {
        $page = $this->open_pdi_page($this->template, $pageNumber, "cloneboxes");
        $this->fit_pdi_page($page, 0, 0, "adjustpage cloneboxes");
        $this->close_pdi_page($page);

        return $this;
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
        $this->fit_textline($text,  $this->xPos, $this->yPos, $optlist);

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function nextLine()
    {
        $this->setYPosition($this->yPos - $this->fontSize, 'pt', true);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function drawImage($imagePath, $size, $loadOptions = null, $fitOptions = null)
    {
        // Load image
        $image = $this->load_image("auto", $imagePath, $loadOptions ?: "");

        if ($image == 0) {
            throw new ImageException($this->get_errmsg());
        }

        $this->fit_image($image, $this->xPos, $this->yPos, $fitOptions ?: "");

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function circleImage($imagePath, $size, $loadOptions = null)
    {
        $this->save();

        $width = $size;
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
        $image = $this->load_image("auto", $imagePath, "");

        if ($image == 0) {
            throw new ImageException($this->get_errmsg());
        }

        // Fit the image into the circle
        $this->fit_image($image, $this->xPos, $this->yPos, "boxsize {" . $width . " " . $height .
            "} position center fitmethod=meet");

        // Close image and restore original clipping (no clipping)
        $this->close_image($image);

        // Restore the state without rounded corners
        $this->restore();

        return $this;
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

        if($this->useOffset && $ignoreOffset === false) {
            $measure += $this->xOffset;
        }

        $this->xPos = $measure;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setYPosition($measure, $unit = null, $ignoreOffset = false)
    {
        $measure = MeasureCalculator::calculateToPt($measure, $unit);

        if($this->useOffset && $ignoreOffset === false) {
            $measure += $this->yOffset;
        }

        $this->yPos = $measure;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setXOffset($measure, $unit = null)
    {
        $measure = MeasureCalculator::calculateToPt($measure, $unit);
        $this->xOffset = $measure;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setYOffset($measure, $unit = null)
    {
        $measure = MeasureCalculator::calculateToPt($measure, $unit);
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
     * {@inheritdoc}
     */
    public function setLineOffset($measure, $unit = null)
    {
        $measure = MeasureCalculator::calculateToPt($measure, $unit);
        $this->lineOffset = $measure;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function finishDocument()
    {
        if($this->siteOpen) {
            $this->end_page_ext("");
            $this->siteOpen = false;
        }

        $this->end_document("");

        return true;
    }
}