<?php

namespace Contoweb\Pdflib\Writers;

use Contoweb\Pdflib\Exceptions\ColorException;
use Contoweb\Pdflib\Exceptions\DocumentException;
use Contoweb\Pdflib\Exceptions\FontException;
use Contoweb\Pdflib\Exceptions\ImageException;
use Contoweb\Pdflib\Exceptions\MeasureException;
use Contoweb\Pdflib\WriterComponents\Table;

interface PdfWriter
{
    /**
     * Define the search path for fonts.
     *
     * @param $searchPath
     * @return $this
     */
    public function defineFontSearchPath($searchPath);

    /**
     * Begin a new PDFLib document.
     *
     * @param  string  $path
     * @param  string|null  $optlist
     * @return bool
     *
     * @throws DocumentException
     */
    public function beginDocument($path, $optlist = null);

    /**
     * End PDFlib page and document.
     *
     * @return bool
     */
    public function finishDocument();

    /**
     * Start a new document page.
     *
     * @param  int  $width
     * @param  int  $height
     * @param  null  $optlist
     * @return $this
     *
     * @throws MeasureException
     */
    public function newPage($width = 0, $height = 0, $optlist = null);

    /**
     * Get the document size of the choosen side.
     * 'width' or 'height' are available as parameter.
     *
     * @param  string  $side
     * @return int
     */
    public function getPageSize($side);

    /**
     * Load a PDF template.
     *
     * @param  string  $absolutPath
     * @param  null  $optlist
     * @return bool
     *
     * @throws DocumentException
     */
    public function loadTemplate($absolutPath, $optlist = null);

    /**
     * Copy a template page into the current document.
     *
     * @param $pageNumber
     * @return $this
     */
    public function fromTemplatePage($pageNumber);

    /**
     * Close the template file.
     *
     * @return bool
     */
    public function closeTemplate();

    /**
     * Load fonts to use it with the writer.
     *
     * @param  string  $name
     * @param  array  $color
     * @return $this
     */
    public function loadColor($name, array $color);

    /**
     * Use a defined color.
     *
     * @param  string  $name
     * @return $this
     *
     * @throws ColorException
     */
    public function useColor($name);

    /**
     * Open a new table.
     *
     * @param  array  $items
     * @return Table
     */
    public function newTable($items);

    /**
     * Add a textflow, to place it for e.g. in a table.
     *
     * @param  string  $textflow
     * @param  string  $title
     * @param  string|null  $optlist
     * @return $this
     */
    public function addTextflow($textflow, $title, $optlist = null);

    /**
     * Load fonts to use it with the writer.
     *
     * @param  string  $name
     * @param  string|null  $encoding
     * @param  string|null  $optlist
     * @return $this
     *
     * @throws FontException
     */
    public function loadFont($name, $encoding = null, $optlist = null);

    /**
     * Use a font for writing.
     *
     * @param  string  $name
     * @param  float  $size  in pt
     * @param string]null $color
     * @return $this
     *
     * @throws ColorException
     * @throws FontException
     */
    public function useFont($name, $size, $color = null);

    /**
     * Get defined writer fonts.
     *
     * @return array
     */
    public function getFonts();

    /**
     * Write fluent text.
     *
     * @param  string  $text
     * @return $this
     */
    public function writeText($text);

    /**
     * Write a line of text.
     *
     * @param $text
     * @param  string|null  $optlist
     * @return $this
     */
    public function writeTextLine($text, $optlist = null);

    /**
     * Go to the next line.
     *
     * @param  float  $spacing
     * @return $this
     */
    public function nextLine($spacing = 1.0);

    /**
     * Get the text width.
     *
     * @param  string  $text
     * @param  string  $font
     * @param  int|float  $fontSize
     * @param  null  $unit
     * @return $this
     */
    public function getTextWidth($text, $font, $fontSize, $unit = null);

    /**
     * Draw an image.
     *
     * @param  string  $imagePath
     * @param  float  $width
     * @param  float  $height
     * @param  string|null  $loadOptions
     * @param  string|null  $fitOptions
     * @return $this
     *
     * @throws ImageException
     */
    public function drawImage($imagePath, $width, $height, $loadOptions = null, $fitOptions = null);

    /**
     * Draw a round image.
     *
     * @param  string  $imagePath
     * @param  float  $size
     * @param  string|null  $loadOptions
     * @return $this
     *
     * @throws ImageException
     */
    public function circleImage($imagePath, $size, $loadOptions = null);

    /**
     * Draw a rectangle shape.
     *
     * @param  int  $width
     * @param  int  $height
     * @return $this
     */
    public function drawRectangle($width, $height);

    /**
     * Draw a rectangled shaped.
     *
     * @param  int  $xFrom
     * @param  int  $xTo
     * @param  int  $yFrom
     * @param  int  $yTo
     * @param  float  $lineWidth
     * @param  string  $unit
     * @return $this
     */
    public function drawLine($xFrom, $xTo, $yFrom, $yTo, $lineWidth = 0.3, $unit = null);

    /**
     * Set the writer's position.
     *
     * @param $x
     * @param $y
     * @param  null  $unit
     * @return $this
     *
     * @throws MeasureException
     */
    public function setPosition($x, $y, $unit = null);

    /**
     * Set X position of the writer.
     *
     * @param $measure
     * @param  string  $unit
     * @param $ignoreOffset
     * @return $this
     *
     * @throws MeasureException
     */
    public function setXPosition($measure, $unit = null, $ignoreOffset = false);

    /**
     * Get the cursor's X position.
     *
     * @param  string|null  $unit
     * @return float
     *
     * @throws MeasureException
     */
    public function getXPosition($unit = null);

    /**
     * Set Y position of the writer.
     *
     * @param  float  $measure
     * @param  string  $unit
     * @param  bool  $ignoreOffset
     * @return $this
     *
     * @throws MeasureException
     */
    public function setYPosition($measure, $unit = null, $ignoreOffset = false);

    /**
     * Get the cursor's Y position.
     *
     * @param  string|null  $unit
     * @return float
     *
     * @throws MeasureException
     */
    public function getYPosition($unit = null);

    /**
     * Get the position of an existing element.
     *
     * @param  string  $infobox
     * @param  string  $corner
     * @return $this
     */
    public function getElementPosition($infobox, $corner);

    /**
     * Get the size of an existing element.
     *
     * @param  string  $infobox
     * @param  string|null  $corner
     * @return $this
     */
    public function getElementSize($infobox, $corner);

    /**
     * Set X offset for preview.
     *
     * @param $measure
     * @param  string  $unit
     * @return $this
     *
     * @throws MeasureException
     */
    public function setXOffset($measure, $unit = null);

    /**
     * Set Y offset for preview.
     *
     * @param $measure
     * @param  string  $unit
     * @return $this
     *
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
}
