<?php

namespace Contoweb\Pdflib\Tests\Files;

class PathHelper
{
    public static function absolutePath($fileName, $diskName, $path = null, $cleanup = true)
    {
        $fullPath = config('filesystems.disks.' . $diskName . '.root') . DIRECTORY_SEPARATOR . ($path ? $path . DIRECTORY_SEPARATOR : '') . $fileName;

        if($cleanup === true) {
            @unlink($fullPath);
        }

        return $fullPath;
    }
}