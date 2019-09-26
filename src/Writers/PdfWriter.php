<?php

namespace Contoweb\Pdflib\Writers;

use Contoweb\Pdflib\Exceptions\ColorException;
use Contoweb\Pdflib\Exceptions\FontException;
use Contoweb\Pdflib\Exceptions\MeasureException;

interface PdfWriter
{
    /**
     * Load a PDF template.
     *
     * @param string $name
     * @param string|null $path
     * @param null $optlist
     * @return boolean
     */
    public function loadTemplate($name, $path = null, $optlist = null);

    /**
     * Close the template file.
     *
     * @return boolean
     */
    public function closeTemplate();

    /**
     * Load fonts to use it with the writer.
     *
     * @param string $name
     * @param array $color
     * @return $this
     */
    public function loadColor($name, array $color);

    /**
     * Load fonts to use it with the writer.
     *
     * @param string $name
     * @param string|null $encoding
     * @param string|null $optlist
     * @return $this
     * @throws FontException
     */
    public function loadFont($name, $encoding = null, $optlist = null);

    /**
     * Use a font for writing.
     *
     * @param string $name
     * @param float $size in pt
     * @param string]null $color
     * @return $this
     * @throws ColorException
     * @throws FontException
     */
    public function useFont($name, $size, $color = null);

    /**
     * Start a new document page.
     *
     * @param integer $width
     * @param integer $height
     * @param null $optlist
     * @return $this
     * @throws MeasureException
     */
    public function newPage($width = 0, $height = 0, $optlist = null);

    /**
     * Copy a template page into the current document
     *
     * @param $pageNumber
     * @return $this
     */
    public function fromTemplatePage($pageNumber);

    /**
     * Write fluent text.
     *
     * @param string $text
     * @param null $xPos
     * @param null $yPos
     * @return $this
     */
    public function writeText($text, $xPos = null, $yPos = null);

    /**
     * Write a line of text.
     *
     * @param $text
     * @param null $optlist
     * @param null $xpos
     * @param null $ypos
     * @return $this
     */
    public function writeTextLine($text, $optlist = null, $xpos = null, $ypos = null);

    /**
     * Go to the next line.
     *
     * @return $this
     */
    public function nextLine();

    /**
     * Draw an image.
     *
     * @param string $imagePath
     * @param float $size
     * @param string|null $loadOptions
     * @param string|null $fitOptions
     * @return $this
     */
    public function drawImage($imagePath, $size, $loadOptions = null, $fitOptions = null);

    /**
     * Draw a round image.
     *
     * @param string $imagePath
     * @param float $size
     * @param string|null $loadOptions
     * @return $this
     */
    public function circleImage($imagePath, $size, $loadOptions = null);

    /**
     * Set the writer's position.
     *
     * @param $x
     * @param $y
     * @param null $unit
     * @return $this
     * @throws MeasureException
     */
    public function setPosition($x, $y, $unit = null);

    /**
     * Set X position of the writer.
     *
     * @param $measure
     * @param string $unit
     * @param $ignoreOffset
     * @return $this
     */
    public function setXPosition($measure, $unit = null, $ignoreOffset = false);

    /**
     * Set Y position of the writer.
     *
     * @param float $measure
     * @param string $unit
     * @param boolean $ignoreOffset
     * @return $this
     */
    public function setYPosition($measure, $unit = null, $ignoreOffset = false);

    /**
     * Set X offset for preview.
     *
     * @param $measure
     * @param string $unit
     * @return $this
     * @throws MeasureException
     */
    public function setXOffset($measure, $unit = null);

    /**
     *
     * Set Y offset for preview.
     *
     * @param $measure
     * @param string $unit
     * @return $this
     * @throws MeasureException
     */
    public function setYOffset($measure, $unit = null);

    /**
     * Use the defined offset.
     *
     * @return $this
     */
    public function enableOffset();

    /**
     * Don't use the defined offset.
     *
     * @return $this
     */
    public function disableOffset();

    /**
     * Checks if it writes the original PDF.
     *
     * @return mixed
     */
    public function inOriginal();

    /**
     * Checks if it writes the preview PDF.
     *
     * @return mixed
     */
    public function inPreview();

    /**
     * Set the line offset.
     *
     * @param $measure
     * @param string $unit
     * @return $this
     * @throws MeasureException
     */
    public function setLineOffset($measure, $unit = null);

    /**
     * End PDFlib page and document
     * @return boolean
     */
    public function finishDocument();
}