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
        return self::absolutePath(
            config('pdf.exports.disk', 'local'),
            config('pdf.exports.path', ''),
            $fileName
        );
    }

    /**
     * Return the template path.
     *
     * @param string $fileName
     * @return string
     */
    public static function templatePath($fileName)
    {
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
     * @param string $type
     * @return string
     */
    public static function fontPath($name, $type = null)
    {
        return self::absolutePath(
            config('pdf.fonts.disk', 'local'),
            config('pdf.fonts.path', ''),
            $name . '.' . ($type ?: 'ttf')
        );
    }

    /**
     * Return the fonts location.
     *
     * @return string
     */
    public static function fontsDirectory()
    {
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
        if($prefix === null){
            $prefix = '';
        }

        $path = Storage::disk($disk)->path($prefix);

        if ($prefix !== '') {
            $path .= DIRECTORY_SEPARATOR;
        }

        return $path . $fileName;
    }
}
