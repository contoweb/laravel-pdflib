<?php

namespace Contoweb\Pdflib\Files;

use Illuminate\Support\Facades\Storage;

class FileManager
{
    /**
     * Return the pdf export path.
     *
     * @param string $fileName
     * @return string
     */
    public static function exportPath($fileName)
    {
        $path = Storage::disk(config('pdf.exports.disk', 'local'))->path(config('pdf.exports.path', ''));

        return $path . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * Return the template path.
     *
     * @param string $fileName
     * @return string
     */
    public static function templatePath($fileName)
    {
        $path = Storage::disk(config('pdf.templates.disk', 'local'))->path(config('pdf.templates.path', ''));

        return $path . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * Return the fonts location.
     *
     * @return string
     */
    public static function fontsDirectory()
    {
        return $path = Storage::disk(config('pdf.fonts.disk', 'local'))->path(config('pdf.fonts.path', ''));
    }
}