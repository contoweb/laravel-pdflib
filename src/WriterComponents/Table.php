<?php

namespace Contoweb\Pdflib\WriterComponents;

use Contoweb\Pdflib\Exceptions\TableException;
use Contoweb\Pdflib\Helpers\MeasureCalculator;
use Contoweb\Pdflib\Writers\PdflibPdfWriter;
use Contoweb\Pdflib\Writers\PdfWriter;

class Table implements WriterComponent
{
    /**
     * Columns of a table.
     * @var array
     */
    protected $columns = [];

    /**
     * Headers of a table.
     * @var array
     */
    protected $headers = [];

    /**
     * Items of a table.
     * @var array
     */
    protected $items = [];

    /**
     * PDFlib's table variable.
     * @var array
     */
    private $pdflibTable;

    /**
     * @var PdfWriter
     */
    private $writer;

    /**
     * {@inheritdoc}
     */
    public function __construct(PdfWriter $writer)
    {
        $this->writer      = $writer;
        $this->pdflibTable = 0;
    }

    /**
     * Set items for table.
     *
     * @param  array  $items
     */
    public function setItems(array $items)
    {
        $this->items = $items;
    }

    /**
     * Add a header to table.
     *
     * @param  array  $names
     * @param  string|null  $font
     * @param  int|null  $fontSize
     * @param  string|null  $position
     * @return $this
     * @throws \Contoweb\Pdflib\Exceptions\MeasureException
     */
    public function withHeader($names, $font = null, $fontSize = null, $position = null)
    {
        foreach ($this->columns as $key=>$tableColumn) {
            array_push(
                $this->headers,
                [
                    'name'     => $names,
                    'width'    => MeasureCalculator::calculateToPt($tableColumn['width'], $tableColumn['unit']),
                    'unit'     => $tableColumn['unit'] ?: config('pdf.measurement.unit', 'pt'),
                    'font'     => $font ?: 'Arial',
                    'fontsize' => $fontSize ?: 10,
                    'position' => $position ?: 'left bottom',
                ]
            );
        }

        return $this;
    }

    /**
     * Add a column for the given table.
     *
     * @param  int  $columnWidth
     * @param  string|null  $unit
     * @param  string|null  $font
     * @param  int|null  $fontSize
     * @param  int|string  $position
     * @return $this
     * @throws \Contoweb\Pdflib\Exceptions\MeasureException
     */
    public function addColumn($columnWidth, $unit = null, $font = null, $fontSize = null, $position = null)
    {
        array_push(
            $this->columns,
            [
                'width'    => MeasureCalculator::calculateToPt($columnWidth, $unit),
                'unit'     => $unit ?: config('pdf.measurement.unit', 'pt'),
                'font'     => $font ?: 'Arial',
                'fontsize' => $fontSize ?: 10,
                'position' => $position ?: 'left bottom',
            ]
        );

        return $this;
    }

    /**
     * Add a cell to the given table.
     *
     * @param  array  $table
     * @param  int|null  $column
     * @param  int|null  $row
     * @param  string|null  $name
     * @param  string|null  $optlist
     * @return $this
     * @throws TableException
     */
    public function addCell($table, $column, $row, $name, $optlist = null)
    {
        $this->pdflibTable = $this->writer->add_table_cell($table, $column, $row, $name, $optlist);

        if ($this->pdflibTable == 0) {
            throw new TableException('Error adding cell: ' . $this->writer->get_errmsg());
        }

        return $this;
    }

    /**
     * Draw the given table.
     *
     * @param  string|null  $optlist
     * @return PdflibPdfWriter
     * @throws \Contoweb\Pdflib\Exceptions\MeasureException|TableException
     */
    public function place($optlist = null)
    {
        if ($optlist === null) {
            if (count($this->headers) > 0) {
                $headerCount = '1';
            } else {
                $headerCount = '0';
            }
            $optlist = 'header=' . $headerCount . ' footer=0 stroke={ {line=horother linewidth=0.3}}';
        }

        // Start the table with row 1
        $row = 1;
        $col = 1;

        if ($this->headers) {
            foreach ($this->headers as $index => $tableHeader) {
                $this->addCell(
                    $this->pdflibTable,
                    $col++,
                    $row,
                    isset(array_values($tableHeader['name'])[$index]) ? array_values($tableHeader['name'])[$index] : '',
                    'fittextline={font=' .
                    $this->writer->getFonts()[$tableHeader['font']] .
                    ' fontsize=' . $tableHeader['fontsize'] .
                    ' position={' . $tableHeader['position'] . '}
							 }' .
                    ' colwidth=' . $tableHeader['width']
                );
            }

            $row++;
            $col = 1;
        }

        for ($itemno = 1; $itemno <= count($this->items); $itemno++, $row) {
            foreach ($this->columns as $index => $column) {
                $this->addCell(
                    $this->pdflibTable,
                    $col++,
                    $row,
                    isset(array_values($this->items[$itemno - 1])[$index]) ? array_values($this->items[$itemno - 1])[$index] : '',
                    'fittextline={font=' .
                    $this->writer->getFonts()[$column['font']] .
                    ' fontsize=' . $column['fontsize'] .
                    ' position={' . $column['position'] . '}
							 }' .
                    ' colwidth=' . $column['width']
                );
            }
            $row++;
            $col = 1;
        }

        $this->fitTable(
            $this->pdflibTable,
            MeasureCalculator::calculateToPt($this->writer->getXPosition(), 'pt'),
            0,
            $this->writer->getPageSize('width'),
            MeasureCalculator::calculateToPt($this->writer->getYPosition(), 'pt'),
            $optlist
        );

        // reset set table-data for next table
        $this->items              = [];
        $this->columns            = [];
        $this->headers            = [];
        $this->pdflibTable        = 0;

        return $this->writer;
    }

    /**
     * Fit the given table.
     *
     * @param  array  $table
     * @param  int  $lowerLeftX
     * @param  int  $lowerLeftY
     * @param  int  $upperRightX
     * @param  int  $upperRightY
     * @param  string  $optlist
     * @return string
     * @throws TableException
     */
    private function fitTable($table, $lowerLeftX, $lowerLeftY, $upperRightX, $upperRightY, $optlist)
    {
        $result = $this->writer->fit_table($table, $lowerLeftX, $lowerLeftY, $upperRightX, $upperRightY, $optlist);

        if ($result == '_error') {
            throw new TableException("Couldn't place table : " . $this->writer->get_errmsg());
        }

        return $result;
    }
}
