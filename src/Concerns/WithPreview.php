<?php

namespace Contoweb\Pdflib\Concerns;

interface WithPreview
{
    /**
     * Set the the X and Y offset for the preview.
     *
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
