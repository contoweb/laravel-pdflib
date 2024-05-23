<?php

namespace Contoweb\Pdflib;

use Contoweb\Pdflib\Concerns\FromTemplate;
use Contoweb\Pdflib\Concerns\WithColors;
use Contoweb\Pdflib\Concerns\WithDraw;
use Contoweb\Pdflib\Concerns\WithPreview;
use Contoweb\Pdflib\Exceptions\MeasureException;
use Contoweb\Pdflib\Files\FileManager;
use Contoweb\Pdflib\Writers\PdfWriter;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;

class Pdf
{
    /**
     * @var PdfWriter
     */
    private $writer;

    /**
     * @var WithDraw
     */
    private $document;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var bool
     */
    private $previewMode = false;

    public function __construct(PdfWriter $writer)
    {
        $this->writer = $writer;
    }

    /**
     * @param  WithDraw  $document
     * @param  string  $fileName
     * @return Pdf
     *
     * @throws Exception
     */
    public function store(WithDraw $document, string $fileName): Pdf
    {
        $this->document = $document;
        $this->fileName = $fileName;

        if ($document instanceof ShouldQueue) {
            // Working on it...
        }

        $this->create();

        return $this;
    }

//    /**
//     * @param $document
//     * @param $fileName
//     * @return Pdf
//     * @throws Exception
//     */
//    public function download($document, $fileName)
//    {
//        // Working on it...
//    }

    /**
     * @param  string|null  $fileName
     * @return true
     *
     * @throws Exception
     */
    public function withPreview(?string $fileName = null): bool
    {
        $mainMode = $this->previewMode;

        $this->inPreviewMode();

        $this->previewFileName($fileName);

        $this->create();

        // Switch back to the main mode.
        $this->previewMode = $mainMode;

        return true;
    }

    /**
     * @return $this
     */
    public function inPreviewMode(): static
    {
        $this->previewMode = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function inOriginalMode(): static
    {
        $this->previewMode = false;

        return $this;
    }

    /**
     * Creates the pdf document(s).
     *
     * @return void
     *
     * @throws Exception
     */
    public function create(): void
    {
        $fileManager = new FileManager($this->document);

        $this->writer->defineFontSearchPath($fileManager->fontsDirectory());

        $this->writer->beginDocument($fileManager->exportPath($this->fileName));

        if ($this->document instanceof FromTemplate) {
            $template = $this->document->template();

            if ($this->document instanceof WithPreview) {
                if ($this->previewMode === false) {
                    $this->applyOffset();
                }

                if ($this->previewMode === true) {
                    $this->writer->disableOffset();

                    $template = $this->document->previewTemplate();
                }
            }

            $this->writer->loadTemplate($fileManager->templatePath($template));
        }

        if ($this->document instanceof WithColors) {
            foreach ($this->document->colors() as $name => $color) {
                $this->writer->loadColor($name, $color);
            }
        }

        if ($this->document instanceof WithDraw) {
            foreach ($this->document->fonts() as $name => $settings) {
                if (is_int($name)) {
                    $name     = $settings;
                    $settings = [];
                }

                $this->writer->loadFont(
                    $name,
                    array_key_exists('encoding', $settings) ? $settings['encoding'] : null,
                    array_key_exists('optlist', $settings) ? $settings['optlist'] : null
                );
            }

            $this->document->draw($this->writer);
        }

        if ($this->document instanceof FromTemplate) {
            $this->writer->closeTemplate();
        }

        $this->writer->finishDocument();
    }

    /**
     * @param  $fileName
     * @return void
     */
    private function previewFileName($fileName = null): void
    {
        if ($fileName) {
            $this->fileName = $fileName;
        } else {
            // Extend file name before extension
            $extensionPos   = strrpos($this->fileName, '.');
            $this->fileName = substr($this->fileName, 0, $extensionPos) . '_preview' . substr($this->fileName, $extensionPos);
        }
    }

    /**
     * Apply the defined document offset.
     *
     * @retrun void
     *
     * @throws MeasureException
     */
    private function applyOffset(): void
    {
        $offsetArray = array_change_key_case($this->document->offset());

        if (array_key_exists('x', $offsetArray)) {
            $this->writer->setXOffset($offsetArray['x'], config('pdf.measurement.unit', 'pt'));
        } else {
            throw new MeasureException('No X offset defined.');
        }

        if (array_key_exists('y', $offsetArray)) {
            $this->writer->setYOffset($offsetArray['y'], config('pdf.measurement.unit', 'pt'));
        } else {
            throw new MeasureException('No Y offset defined.');
        }

        $this->writer->enableOffset();
    }
}
