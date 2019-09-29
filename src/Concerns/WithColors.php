<?php

namespace Contoweb\Pdflib\Concerns;

interface WithColors
{
    /**
     * Set the the X and Y offset for the preview.
     *
     * @param null $measureUnit
     * @return array
     */
    public function colors(): array;
}
