<?php

namespace Contoweb\Pdflib\Concerns;

interface DifferentFontsLocation
{
    /**
     * Provide a different location to load fonts.
     * Array has the same structure as in the config.
     *
     * @return array
     */
    public function fontsLocation(): array;
}
