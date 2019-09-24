<?php

namespace Contoweb\Pdflib\Concerns;

interface HasPreview
{
    /**
     * Set the the X and Y offset for the preview
     *
     * @param null $measureUnit
     * @return array
     */
    public function offset(): array;

    /**
     * Return the preview template.
     *
     * @return string
     */
    public function previewTemplate(): string;
}