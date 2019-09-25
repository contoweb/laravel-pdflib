<?php

namespace Contoweb\Pdflib;

use Contoweb\Pdflib\Concerns\FromTemplate;
use Contoweb\Pdflib\Concerns\WithPreview;
use Contoweb\Pdflib\Concerns\WithColors;
use Contoweb\Pdflib\Concerns\WithDraw;
use Contoweb\Pdflib\Concerns\WithFonts;
use Contoweb\Pdflib\Exceptions\MeasureException;
use Contoweb\Pdflib\Files\FileManager;
use Contoweb\Pdflib\Writers\PdfWriter;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;

class Pdf
{
    /**
     * @var PdfWriter $writer
     */
    private $writer;

    /**
     * @var WithDraw $document
     */
    private $document;

    /**
     * @var string $fileName
     */
    private $fileName;

    public function __construct(PdfWriter $writer)
    {
        $this->writer = $writer;
    }

    /**
     * @param WithDraw $document
     * @param string $fileName
     * @return Pdf
     * @throws Exception
     */
    public function store(WithDraw $document, $fileName)
    {
        $this->document = $document;
        $this->fileName = $fileName;

        if($document instanceof ShouldQueue) {
          // Return queue
        }

        $this->create();

        return $this;
    }

    /**
     * @param $document
     * @param $fileName
     * @return Pdf
     * @throws Exception
     */
    public function download($document, $fileName)
    {
        // Working on it...
    }

    /**
     * @param string|null $fileName
     * @return bool
     * @throws MeasureException
     */
    public function withPreview($fileName = null)
    {
        if($fileName && $fileName !== $this->fileName) {
            $this->fileName = $fileName;
        } else {
            // Extend file name before extension
            $extensionPos = strrpos($this->fileName, '.');
            $this->fileName = substr($this->fileName, 0, $extensionPos) . '_preview' . substr($this->fileName, $extensionPos);
        }

        if($this->document instanceof WithPreview) {
            // Make offset array key insensitive
            $offsetArray = array_change_key_case($this->document->offset());

            if(array_key_exists('x', $offsetArray)) {
                $this->writer->setXOffset($offsetArray['x'], config('pdf.measurement.unit', 'pt'));
            } else {
                throw new MeasureException('No X offset defined.');
            }

            if(array_key_exists('y', $offsetArray)) {
                $this->writer->setYOffset($offsetArray['y'], config('pdf.measurement.unit', 'pt'));
            } else {
                throw new MeasureException('No Y offset defined.');
            }

            $this->writer->enableOffset();
        }

        $this->create();

        return true;
    }

    /**
     * Creates the pdf document(s).
     *
     * @return string
     * @throws Exception
     */
    private function create()
    {
        $fullPath = FileManager::exportPath($this->fileName);

        if ($this->writer->begin_document($fullPath, "") == 0) {
            throw new Exception("Error: " . $this->writer->get_errmsg());
        }

        if($this->document instanceof FromTemplate) {
            $template = null;

            if($this->writer->inOriginal()) {
                $template = $this->document->template();
            }

            if($this->document instanceof WithPreview) {
                if($this->writer->inPreview()) {
                    $template = $this->document->previewTemplate();
                }
            }

            $this->writer->loadTemplate($template);
        }

        if($this->document instanceof WithFonts) {
            foreach($this->document->fonts() as $font) {
                $this->writer->loadFont(
                    $font['name'],
                    array_key_exists('encoding', $font) ? $font['encoding'] : null,
                    array_key_exists('optlist', $font) ? $font['optlist'] : null
                );
            }
        }

        if($this->document instanceof WithColors) {
            if ($this->document instanceof WithColors) {
                foreach ($this->document->colors() as $name => $color) {
                    $this->writer->loadColor($name, $color);
                }
            }
        }

        $this->document->draw($this->writer);

        if($this->document instanceof FromTemplate) {
            $this->writer->closeTemplate();
        }

        $this->writer->finishDocument();

        return $fullPath;
    }
}