<?php

namespace Contoweb\Pdflib\Files;

use Contoweb\Pdflib\Concerns\DifferentExportLocation;
use Contoweb\Pdflib\Concerns\DifferentFontsLocation;
use Contoweb\Pdflib\Concerns\DifferentTemplateLocation;
use Contoweb\Pdflib\Concerns\WithDraw;
use Contoweb\Pdflib\Exceptions\DifferentLocationException;
use Illuminate\Support\Facades\Storage;

class FileManager
{
    /**
     * The document.
     *
     * @var WithDraw
     */
    protected $document;

    public function __construct(WithDraw $document)
    {
        $this->document = $document;
    }

    /**
     * Return the pdf export path.
     *
     * @param  string  $fileName
     * @return string
     * @throws DifferentLocationException
     */
    public function exportPath($fileName)
    {
        if($this->document instanceof DifferentExportLocation) {
            $location = $this->document->exportLocation();

            return $this->absolutPathForDifferentLocation($location, $fileName);
        }

        return self::absolutePath(
            config('pdf.exports.disk', 'local'),
            config('pdf.exports.path', ''),
            $fileName
        );
    }

    /**
     * Return the template path.
     *
     * @param  string  $fileName
     * @return string
     * @throws DifferentLocationException
     */
    public function templatePath($fileName)
    {
        if($this->document instanceof DifferentTemplateLocation) {
            $location = $this->document->templateLocation();

            return $this->absolutPathForDifferentLocation($location, $fileName);
        }

        return self::absolutePath(
            config('pdf.templates.disk', 'local'),
            config('pdf.templates.path', ''),
            $fileName
        );
    }

    /**
     * Return the path of a font.
     *
     * @param $name
     * @param  string  $type
     * @return string
     * @throws DifferentLocationException
     */
    public function fontPath($name, $type = null)
    {
        $fileName = $name . '.' . ($type ?: 'ttf');

        if($this->document instanceof DifferentFontsLocation) {
            $location = $this->document->fontsLocation();

            return $this->absolutPathForDifferentLocation($location, $fileName);
        }

        return self::absolutePath(
            config('pdf.fonts.disk', 'local'),
            config('pdf.fonts.path', ''),
            $fileName
        );
    }

    /**
     * Return the fonts location.
     *
     * @return string
     * @throws DifferentLocationException
     */
    public function fontsDirectory()
    {
        if($this->document instanceof DifferentFontsLocation) {
            $location = $this->document->fontsLocation();

            return $this->absolutPathForDifferentLocation($location, null);
        }

        return self::absolutePath(
            config('pdf.fonts.disk', 'local'),
            config('pdf.fonts.path', '')
        );
    }

    /**
     * Absolute path to the file.
     *
     * @param string $disk
     * @param string $prefix
     * @param string|null $fileName
     * @return string
     */
    protected static function absolutePath($disk, $prefix, $fileName = null)
    {
        $path = Storage::disk($disk)->path($prefix);

        if ($prefix !== '') {
            $path .= DIRECTORY_SEPARATOR;
        }

        return $path . $fileName;
    }

    /**
     * Get the absolut path for document's different location.
     *
     * @param $location
     * @param $fileName
     * @return string
     * @throws DifferentLocationException
     */
    protected function absolutPathForDifferentLocation($location, $fileName)
    {
        if (
            array_key_exists("disk", $location) === false ||
            array_key_exists("path", $location) === false
        ) {
            throw new DifferentLocationException('Invalid different location parameters provided.');
        }

        return self::absolutePath(
            $location['disk'],
            $location['path'],
            $fileName
        );
    }
}
