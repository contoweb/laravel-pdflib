<?php

namespace Contoweb\Pdflib\Concerns;

interface FromTemplate
{
    /**
     * Set the the X and Y offset for the preview
     *
     * @param null $measureUnit
     * @return array
     */
    public function template(): array;
}