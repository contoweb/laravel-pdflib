<?php

namespace Contoweb\Pdflib\Concerns;

interface DifferentTemplateLocation
{
    /**
     * Provide a different location to load templates.
     * Array has the same structure as in the config.
     *
     * @return array
     */
    public function templateLocation(): array;
}
