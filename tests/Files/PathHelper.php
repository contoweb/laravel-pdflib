<?php

namespace Contoweb\Pdflib\Tests\Files;

class PathHelper
{
    /**
     * Generate an absolute path and removes old file with this path.
     *
     * @param  $fileName
     * @param  $diskName
     * @param  null  $path
     * @param  bool  $cleanup
     * @return string
     */
    public static function absolutePath($fileName, $diskName, $path = null, $cleanup = true)
    {
        $fullPath = config('filesystems.disks.' . $diskName . '.root') . DIRECTORY_SEPARATOR . ($path ? $path . DIRECTORY_SEPARATOR : '') . $fileName;

        if ($cleanup === true) {
            @unlink($fullPath);
        }

        return $fullPath;
    }
}
