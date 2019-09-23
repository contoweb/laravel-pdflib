<?php

namespace Contoweb\Pdflib\Concerns;

interface WithFonts
{
    /**
     * Set the the X and Y offset for the preview
     *
     * @param null $measureUnit
     * @return array
     */
    public function fonts(): array;
}