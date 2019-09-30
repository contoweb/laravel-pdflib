<?php

namespace Contoweb\Pdflib\Facades;

use Illuminate\Support\Facades\Facade;

class Pdf extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'pdf';
    }
}
