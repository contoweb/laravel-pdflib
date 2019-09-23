<?php

namespace Contoweb\Pdflib\Concerns;

use Contoweb\Pdflib\Exceptions\FontException;
use Contoweb\Pdflib\Exceptions\MeasureException;

interface Writer
{
    /**
     * Load a PDF template.
     *
     * @param string $name
     * @param string|null $path
     * @param null $optlist
     * @return void
     */
    public function loadTemplate($name, $path = null, $optlist = null);

    /**
     * Close the template file.
     *
     * @return void
     */
    public function closeTemplate();

    /**
     * Load fonts to use it with the writer.
     *
     * @param string $name
     * @param string|null $encoding
     * @param string|null $optlist
     * @return int
     */
    public function loadFont($name, $encoding = null, $optlist = null);

    /**
     * Use a font for writing.
     *
     * @param string $name
     * @param float $size in pt
     * @throws FontException
     */
    public function useFont($name, $size);

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
     */
    public function writeText($text, $xPos = null, $yPos = null);

    /**
     * Write a line of text.
     *
     * @param $text
     * @param null $optlist
     * @param null $xpos
     * @param null $ypos
     */
    public function writeTextLine($text, $optlist = null, $xpos = null, $ypos = null);

    /**
     * Go to the next line.
     */
    public function nextLine();

    /**
     * Draw a round image.
     *
     * @param $imagePath
     * @param $size
     */
    public function circleImage($imagePath, $size);

    /**
     * Set the writer's position.
     *
     * @param $x
     * @param $y
     * @param null $unit
     * @throws MeasureException
     */
    public function setPosition($x, $y, $unit = null);

    /**
     * Set Y position of the writer.
     *
     * @param $measure
     * @param string $unit
     * @param $ignoreOffset
     * @return float
     */
    public function setXPosition($measure, $unit = null, $ignoreOffset = false);

    /**
     * Set Y position of the writer.
     *
     * @param float $measure
     * @param string $unit
     * @param boolean $ignoreOffset
     * @return float
     */
    public function setYPosition($measure, $unit = null, $ignoreOffset = false);

    /**
     * Set X offset for preview.
     *
     * @param $measure
     * @param string $unit
     * @return float
     * @throws MeasureException
     */
    public function setXOffset($measure, $unit = null);

    /**
     *
     * Set Y offset for preview.
     *
     * @param $measure
     * @param string $unit
     * @return float
     * @throws MeasureException
     */
    public function setYOffset($measure, $unit = null);

    /**
     * Use the defined offset.
     *
     * @return mixed
     */
    public function enableOffset();

    /**
     * Don't use the defined offset.
     *
     * @return mixed
     */
    public function disableOffset();

    /**
     * Set line offset.
     *
     * @param $measure
     * @param string $unit
     * @return float
     * @throws MeasureException
     */
    public function setLineOffset($measure, $unit = null);

    /**
     * End PDFlib page and document
     */
    public function finishDocument();
}